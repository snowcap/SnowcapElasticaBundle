<?php

namespace Snowcap\ElasticaBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Snowcap\ElasticaBundle\Service;

class IndexSubscriber implements EventSubscriber {

    /**
     * @var \Snowcap\ElasticaBundle\Service
     */
    private $elastica;

    /**
     * @var array
     */
    private $managedClasses = array();

    /**
     * @param \Snowcap\ElasticaBundle\Service $elastica
     */
    public function __construct(Service $elastica) {
        $this->elastica = $elastica;
        foreach($elastica->getIndexers() as $indexer) {
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
        return array('postPersist', 'postUpdate', 'postRemove');
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $ea
     */
    public function postPersist(LifecycleEventArgs $ea)
    {
        $this->index($ea->getEntity());
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $ea
     */
    public function postUpdate(LifecycleEventArgs $ea)
    {
        $this->index($ea->getEntity());
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $ea
     */
    public function postRemove(LifecycleEventArgs $ea)
    {
        $this->index($ea->getEntity());
    }

    /**
     * @param $entity
     */
    private function index($entity)
    {
        if(in_array(get_class($entity), $this->managedClasses)) {
            $this->elastica->index($entity);
        }
    }

}