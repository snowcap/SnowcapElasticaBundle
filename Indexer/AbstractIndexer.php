<?php

namespace Snowcap\ElasticaBundle\Indexer;

use Doctrine\ORM\EntityManager;

abstract class AbstractIndexer implements IndexerInterface
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param $entity
     * @return bool
     */
    public function supports($entity)
    {
        return in_array(get_class($entity), $this->getManagedClasses());
    }

    /**
     * @param object $entity
     * @return mixed
     */
    public function getIndexableEntity($entity)
    {
        return $entity;
    }

    /**
     * @param object $entity
     * @return mixed
     */
    public function getDocumentIdentifier($entity)
    {
        return $entity->getId();
    }

    /**
     * @param object $entity
     * @param \Elastica_Type $type
     */
    public function addIndex($entity, \Elastica_Type $type)
    {
        $document = new \Elastica_Document($this->getDocumentIdentifier($entity), $this->map($entity, $type));
        $type->addDocument($document);
    }

    /**
     * @param object $entity
     * @param \Elastica_Type $type
     */
    public function removeIndex($entity, \Elastica_Type $type)
    {
        try {
            $type->deleteById($this->getDocumentIdentifier($entity));
        }
        catch(\InvalidArgumentException $e){}
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

}