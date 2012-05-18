<?php

namespace Snowcap\ElasticaBundle\Indexer;

use Doctrine\ORM\EntityManager;

interface IndexerInterface {

    const ACTION_REMOVE = 'remove';
    const ACTION_ADD = 'add';
    const ACTION_NONE = 'none';

    /**
     * Return an array of classes managed by this indexer
     * Must at least contain the class name of the main entity you wish to index, and may
     * also contain additional classes whose update or deletion should trigger a reindex of
     * the main entity mentionned above
     *
     * @abstract
     * @return array
     */
    public function getManagedClasses();

    /**
     * Check if the passed entity can be managed by this indexer
     *
     * @abstract
     * @param object $entity
     * @return bool
     */
    public function supports($entity);

    /**
     * Return a mapping array
     * See http://ruflin.github.com/Elastica/ and
     * http://www.elasticsearch.org/guide/reference/mapping/ for more information
     *
     * @abstract
     * @return mixed
     */
    public function getMappings();

    /**
     * Return of the ACTION_* constants depending on the provided entity
     * Used to determine whether the given entity should be indexed or unindexed
     *
     * @abstract
     * @param object $entity
     * @param \Elastica_Type $type
     * @return string
     */
    public function getIndexAction($entity, \Elastica_Type $type);

    /**
     * Return an array of all the entities that need to be reindexed
     * during a rebuild operation
     *
     * @abstract
     * @param \Doctrine\ORM\EntityManager $em
     * @param \Elastica_Type $type
     * @return array
     */
    public function getEntitiesToIndex(EntityManager $em, \Elastica_Type $type);

    /**
     * Get the entity to index provided a given entity
     * In simple cases, this method should simply return the provided entity
     * In some cases, however (depending on the classes returned by getManagedClasses
     * you might want, given an entity of class Foo, index in fact an associated entity of class Bar
     *
     * @abstract
     * @param object $entity
     * @return object
     */
    public function getIndexableEntity($entity);

    /**
     * Determine the elasticsearch document identifier
     *
     * @abstract
     * @param $entity
     * @return mixed
     */
    public function getDocumentIdentifier($entity);

    /**
     * Return an array of data that can be used to build a Elastica_Document instance
     *
     * @abstract
     * @param object $entity
     * @param \Elastica_Type $type
     * @return array
     */
    public function map($entity, \Elastica_Type $type);

    /**
     * Add (or update) an elasticsearch document for the provided entity
     *
     * @abstract
     * @param object $entity
     * @param \Elastica_Type $type
     */
    public function addIndex($entity, \Elastica_Type $type);

    /**
     * Remove (if existing) the elasticsearch document for the provided entity
     *
     * @abstract
     * @param object $entity
     * @param \Elastica_Type $type
     */
    public function removeIndex($entity, \Elastica_Type $type);

    /**
     * Remove (if existing) the elasticsearch document for the provided id
     *
     * @abstract
     * @param integer $id
     * @param \Elastica_Type $type
     */
    public function removeIndexById($id, \Elastica_Type $type);

    /**
     * Store the entity manager
     *
     * @abstract
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function setEntityManager(EntityManager $em);
}