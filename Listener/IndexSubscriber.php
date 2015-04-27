<?php

namespace Snowcap\ElasticaBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Snowcap\ElasticaBundle\ServiceInterface;

/**
 * This subscriber class listens to Doctrine events and, depending on the registered indexers, automatically
 * triggers index/unindex operations
 *
 * @package Snowcap\ElasticaBundle\Listener
 */
class IndexSubscriber implements EventSubscriber
{
    /**
     * @var \Snowcap\ElasticaBundle\Service
     */
    private $elastica;

    /**
     * @var array
     */
    private $managedClasses = array();

    /**
     * @var array
     */
    private $scheduledIndexations = array();

    /**
     * @var array
     */
    private $scheduledUnindexations = array();

    /**
     * @param \Snowcap\ElasticaBundle\ServiceInterface $elastica
     */
    public function __construct(ServiceInterface $elastica)
    {
        $this->elastica = $elastica;
        foreach ($elastica->getIndexers() as $indexer) {
            $this->managedClasses = array_merge($this->managedClasses, $indexer->getManagedClasses());
        }
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array('postPersist', 'postUpdate', 'preRemove', 'postFlush');
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $ea
     */
    public function postPersist(LifecycleEventArgs $ea)
    {
        $this->scheduleForIndexation($ea->getEntity());
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $ea
     */
    public function postUpdate(LifecycleEventArgs $ea)
    {
        $this->scheduleForIndexation($ea->getEntity());
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $ea
     */
    public function preRemove(LifecycleEventArgs $ea)
    {
        $this->scheduleForUnindexation($ea->getEntity());
    }

    /**
     * We trigger index/unindex operations on postFlush events
     *
     * @param PostFlushEventArgs $ea
     */
    public function postFlush(PostFlushEventArgs $ea)
    {
        foreach($this->scheduledIndexations as $entity)
        {
            $this->elastica->index($entity);
        }
        $this->scheduledIndexations = array();

        foreach($this->scheduledUnindexations as $entity)
        {
            $this->elastica->indexRemove($entity);
        }
        $this->scheduledUnindexations = array();
    }

    /**
     * Schedule the provided entity for an index operation
     *
     * @param $entity
     */
    private function scheduleForIndexation($entity)
    {
        if ($this->isManaged($entity) && !in_array($entity, $this->scheduledIndexations)) {
            $this->scheduledIndexations[] = $entity;
        }
    }

    /**
     * Schedule the provided entity for an unindex operation
     *
     * @param $entity
     */
    private function scheduleForUnindexation($entity)
    {
        if ($this->isManaged($entity) && !in_array($entity, $this->scheduledUnindexations)) {
            $this->scheduledUnindexations[]= $entity;
        }
    }

    /**
     * Determines if the provided entity is managed by the Elastica subscriber
     *
     * @param object $entity
     * @return bool
     */
    private function isManaged($entity)
    {
        $entityClasses = array_merge(array(get_class($entity)), class_parents($entity));
        $intersection = array_intersect($entityClasses, $this->managedClasses);

        return count($intersection) > 0;
    }
}