<?php

namespace Snowcap\ElasticaBundle;

use Elastica\Index;
use Elastica\ResultSet;
use Elastica\Search;
use Elastica\Type\Mapping;
use Snowcap\ElasticaBundle\Indexer\IndexerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * This service class is the main entry point for Elastica operations
 *
 * @package Snowcap\ElasticaBundle
 */
class Service extends ContainerAware implements ServiceInterface
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var array
     */
    protected $indexes = array();

    /**
     * @var array
     */
    protected $types = array();

    /**
     * @var array
     */
    protected $indexers = array();

    /**
     * @param Client $client
     * @param string $namespace
     */
    public function __construct(Client $client, $namespace)
    {
        $this->client = $client;
        $this->namespace = $namespace;
    }

    /**
     * Create indexes as defined in the config
     *
     */
    public function createIndexes()
    {
        foreach ($this->indexes as $indexName => $indexParams) {
            $index = $this->client->getIndex($indexName);
            $response = $index->create($indexParams, true);
            $this->createTypes($index);
        }
    }

    /**
     * Create types associated with the given index
     *
     * @param Index $index
     */
    protected function createTypes(Index $index)
    {
        foreach ($this->indexers as $indexerAlias => $indexer) {
            $type = $index->getType($indexerAlias);

            $mapping = new Mapping();
            $mapping->setType($type);
            $mapping->setProperties($indexer->getMappings());
            $mapping->send();
        }
    }

    /**
     * Reindex all indexable content
     *
     */
    public function reindex()
    {
        foreach ($this->indexes as $indexName => $indexParams) {
            $index = $this->client->getIndex($indexName);
            foreach ($this->indexers as $indexerAlias => $indexer) {
                $type = $index->getType($indexerAlias);
                $entities = $indexer->getEntitiesToIndex($this->container->get('doctrine.orm.entity_manager'), $type);
                foreach($entities as $entity) {
                    $indexer->addIndex($entity, $type);
                }
                $this->container->get('doctrine.orm.entity_manager')->clear();
            }
        }
    }

    /**
     * Rebuild one type associated to all indexes
     *
     * @param string $typeName
     */
    public function rebuildType($typeName)
    {
        // Check indexer with the given type name
        if (!isset($typeName, $this->indexers)) {
            throw new \UnexpectedValueException(sprintf('The indexer for type "%s" does not exist.', $typeName));
        }

        /** @var IndexerInterface $indexer */
        $indexer = $this->indexers[$typeName];

        foreach ($this->indexes as $indexName => $indexParams) {
            $index = $this->client->getIndex($indexName);
            $type = $index->getType($typeName);

            // Create the type with the correct mapping
            $type->delete();
            $mapping = new Mapping();
            $mapping->setType($type);
            $mapping->setProperties($indexer->getMappings());
            $mapping->send();

            // Reindex data
            $entities = $indexer->getEntitiesToIndex($this->container->get('doctrine.orm.entity_manager'), $type);
            foreach($entities as $entity) {
                $indexer->addIndex($entity, $type);
            }
            $this->container->get('doctrine.orm.entity_manager')->clear();
        }
    }

    /**
     * Take the appropriate index action for the given entity
     *
     * @param object $entity
     */
    public function index($entity)
    {
        foreach ($this->indexes as $indexName => $indexParams) {
            $index = $this->client->getIndex($indexName);
            foreach ($this->indexers as $indexerAlias => $indexer) {
                if($indexer->supports($entity)) {
                    $type = $index->getType($indexerAlias);

                    $indexableEntities = $indexer->getIndexableEntities($entity);
                    foreach ($indexableEntities as $indexableEntity) {

                        if($this->container->get('doctrine.orm.entity_manager')->getUnitOfWork()->isScheduledForDelete($indexableEntity)) {
                            $action = IndexerInterface::ACTION_REMOVE;
                        }
                        else {
                            $action = $indexer->getIndexAction($indexableEntity, $type);
                        }

                        switch($action) {
                            case IndexerInterface::ACTION_ADD:
                                $indexer->addIndex($indexableEntity, $type);
                                break;
                            case IndexerInterface::ACTION_REMOVE:
                                $indexer->removeIndex($indexableEntity, $type);
                                break;
                        }
                    }

                }
            }
        }
    }

    public function indexRemove($entity)
    {
        foreach ($this->indexes as $indexName => $indexParams) {
            $index = $this->client->getIndex($indexName);
            foreach ($this->indexers as $indexerAlias => $indexer) {
                if($indexer->supports($entity)) {
                    $type = $index->getType($indexerAlias);
                    $indexableEntities = $indexer->getIndexableEntities($entity);
                    foreach ($indexableEntities as $indexableEntity) {
                        if($indexableEntity->getId() !== null) {
                            if (get_class($entity) === get_class($indexableEntity)) {
                                $indexer->removeIndexById($indexableEntity->getId(), $type);
                            } else {
                                // Special case: a managed entity has been removed, but
                                // it isn't the main indexable entity, so instead of
                                // removing anything, we need to update the indexable entity
                                // to let him know some of his related is gone
                                $indexer->addIndex($indexableEntity, $type);
                            }
                        }
                    }

                }
            }
        }
    }

    /**
     * Perform a simple search on the given index and types
     *
     * @param string $query
     * @param string|array $index
     * @param array $types
     * @param array|int|null $options
     * @return ResultSet
     */
    public function search($query, $index, $types = null, $options = null)
    {
        $search = new Search($this->client);

        if (!is_array($index)) {
            $index = array($index);
        }
        if($types === null) {
            $types = array_keys($this->indexers);
        }

        foreach ($index as $idx) {
            $search->addIndex($this->addNamespace($idx));
        }

        $search->addTypes($types);
        return $search->search($query, $options);
    }

    /**
     * Register an index
     *
     * @param string $alias
     * @param Indexer\IndexerInterface $indexer
     * @throws \UnexpectedValueException
     */
    public function registerIndexer($alias, IndexerInterface $indexer)
    {
        foreach($indexer->getManagedClasses() as $managedClass) {
            if(!class_exists($managedClass)) {
                $message = 'Invalid managed class "%s" provided in indexer "%s"';
                throw new \UnexpectedValueException(sprintf($message, $managedClass, get_class($indexer)));
            }
        }

        $this->indexers[$alias] = $indexer;
    }

    /**
     * Return all indexers
     *
     * @return array
     */
    public function getIndexers()
    {
        return $this->indexers;
    }

    /**
     * Set indexes
     *
     * @param array $indexes
     */
    public function setIndexes(array $indexes)
    {
        $namespacedIndexes = array();
        foreach($indexes as $indexName => $indexParams) {
            $namespacedIndexes[$this->addNamespace($indexName)] = $indexParams;
        }
        $this->indexes = $namespacedIndexes;
    }

    private function addNamespace($indexName) {
        return $this->namespace . '.' . $indexName;
    }

}
