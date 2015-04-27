<?php

namespace Snowcap\ElasticaBundle\Tests\Listener\Mock;

use Doctrine\ORM\EntityManager;
use Elastica\Type;
use Snowcap\ElasticaBundle\Indexer\IndexerInterface;

/**
 * Mock indexers for unit tests
 *
 * @package Snowcap\ElasticaBundle\Tests\Listener\Mock
 */
class FooIndexer implements IndexerInterface {
    /**
     * Return an array of classes managed by this indexer
     * Must at least contain the class name of the main entity you wish to index, and may
     * also contain additional classes whose update or deletion should trigger a reindex of
     * the main entity mentionned above
     *
     * @return array
     */
    public function getManagedClasses()
    {
        return array('Snowcap\ElasticaBundle\Tests\Listener\Mock\FooEntity');
    }

    /**
     * Check if the passed entity can be managed by this indexer
     *
     * @param object $entity
     * @return bool
     */
    public function supports($entity)
    {
        // TODO: Implement supports() method.
    }

    /**
     * Return a mapping array
     * See http://ruflin.github.com/Elastica/ and
     * http://www.elasticsearch.org/guide/reference/mapping/ for more information
     *
     * @return mixed
     */
    public function getMappings()
    {
        // TODO: Implement getMappings() method.
    }

    /**
     * Return of the ACTION_* constants depending on the provided entity
     * Used to determine whether the given entity should be indexed or unindexed
     *
     * @param object $entity
     * @param Type $type
     * @return string
     */
    public function getIndexAction($entity, Type $type)
    {
        // TODO: Implement getIndexAction() method.
    }

    /**
     * Return an array of all the entities that need to be reindexed
     * during a rebuild operation
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @param Type $type
     * @return array
     */
    public function getEntitiesToIndex(EntityManager $em, Type $type)
    {
        // TODO: Implement getEntitiesToIndex() method.
    }

    /**
     * Get the entities to index provided a given entity
     * In simple cases, this method should simply return an array with the provided entity
     * In some cases, however (depending on the classes returned by getManagedClasses
     * you might want, given an entity of class Foo, index in fact an associated entity of class Bar
     *
     * @param object $entity
     * @return array
     */
    public function getIndexableEntities($entity)
    {
        // TODO: Implement getIndexableEntities() method.
    }

    /**
     * Determine the elasticsearch document identifier
     *
     * @param $entity
     * @return mixed
     */
    public function getDocumentIdentifier($entity)
    {
        // TODO: Implement getDocumentIdentifier() method.
    }

    /**
     * Return an array of data that can be used to build a Elastica_Document instance
     *
     * @param object $entity
     * @param Type $type
     * @return array
     */
    public function map($entity, Type $type)
    {
        // TODO: Implement map() method.
    }

    /**
     * Add (or update) an elasticsearch document for the provided entity
     *
     * @param object $entity
     * @param Type $type
     */
    public function addIndex($entity, Type $type)
    {
        // TODO: Implement addIndex() method.
    }

    /**
     * Remove (if existing) the elasticsearch document for the provided entity
     *
     * @param object $entity
     * @param Type $type
     */
    public function removeIndex($entity, Type $type)
    {
        // TODO: Implement removeIndex() method.
    }

    /**
     * Remove (if existing) the elasticsearch document for the provided id
     *
     * @param integer $id
     * @param Type $type
     */
    public function removeIndexById($id, Type $type)
    {
        // TODO: Implement removeIndexById() method.
    }

    /**
     * Store the entity manager
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        // TODO: Implement setEntityManager() method.
    }
} 