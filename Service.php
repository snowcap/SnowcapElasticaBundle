<?php

namespace Snowcap\ElasticaBundle;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;

use Snowcap\ElasticaBundle\Indexer\IndexerInterface;

class Service extends ContainerAware
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
     * @param \Elastica_Index $index
     */
    protected function createTypes(\Elastica_Index $index)
    {
        foreach ($this->indexers as $indexerAlias => $indexer) {
            $type = $index->getType($indexerAlias);
            $mapping = new \Elastica_Type_Mapping();
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
                            $indexer->removeIndexById($indexableEntity->getId(), $type);
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
     * @param string $index
     * @param array $types
     * @return \Elastica_ResultSet
     */
    public function search($query, $index, $types = null)
    {
        if($types === null) {
            $types = array_keys($this->indexers);
        }
        $search = new \Elastica_Search($this->client);

        // if $index is an array we perform the search on multiple indexes
        // otherwise, we just perform the search on the given index
        if (is_array($index)) {
            foreach ($index as $idx) {
                $search->addIndex($this->addNamespace($idx));
            }
        } else {
            $search->addIndex($this->addNamespace($index));
        }

        $search->addTypes($types);
        return $search->search($query);
    }

    /**
     * Register an index
     *
     * @param string $alias
     * @param Indexer\IndexerInterface $indexer
     */
    public function registerIndexer($alias, IndexerInterface $indexer)
    {
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
