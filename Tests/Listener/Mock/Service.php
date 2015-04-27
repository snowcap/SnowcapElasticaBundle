<?php

namespace Snowcap\ElasticaBundle\Tests\Listener\Mock;


use Snowcap\ElasticaBundle\Indexer;
use Snowcap\ElasticaBundle\Indexer\IndexerInterface;
use Snowcap\ElasticaBundle\ServiceInterface;

/**
 * Mock service class for unit tests
 *
 * @package Snowcap\ElasticaBundle\Tests\Listener\Mock
 */
class Service implements ServiceInterface {
    /**
     * @var array
     */
    private $indexers = array();

    public function __construct()
    {
        $this->indexers = array(new FooIndexer());
    }

    /**
     * Create indexes as defined in the config
     *
     */
    public function createIndexes()
    {
        // TODO: Implement createIndexes() method.
    }

    /**
     * Reindex all indexable content
     *
     */
    public function reindex()
    {
        // TODO: Implement reindex() method.
    }

    /**
     * Index the provided entity (or unindex it if its indexer asks us too)
     *
     * @param object $entity
     */
    public function index($entity)
    {
        // TODO: Implement index() method.
    }

    /**
     * Unindex the provided entity
     *
     * @param object $entity
     * @return mixed
     */
    public function indexRemove($entity)
    {
        // TODO: Implement indexRemove() method.
    }

    /**
     * Perform a simple search on the given index and types
     *
     * @param string $query
     * @param string|array $index
     * @param array $types
     * @return \Elastica\ResultSet
     */
    public function search($query, $index, $types = null)
    {
        // TODO: Implement search() method.
    }

    /**
     * Register an index
     *
     * @param string $alias
     * @param Indexer\IndexerInterface $indexer
     */
    public function registerIndexer($alias, IndexerInterface $indexer)
    {
        // TODO: Implement registerIndexer() method.
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
        // TODO: Implement setIndexes() method.
    }
} 