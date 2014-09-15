<?php

namespace Snowcap\ElasticaBundle\Indexer;

use Doctrine\ORM\EntityManager;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Type;

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
        $supports = false;

        if(in_array(get_class($entity), $this->getManagedClasses())) {
            $supports = true;
        }

        if (!$supports) {
            // if the entity is a Proxy
            foreach ($this->getManagedClasses() as $class) {
                if ($entity instanceof $class) {
                    $supports = true;
                }
            }
        }

        return $supports;
    }

    /**
     * @param object $entity
     * @return array
     */
    public function getIndexableEntities($entity)
    {
        return array($entity);
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
     * @param Type $type
     */
    public function addIndex($entity, Type $type)
    {
        $document = new Document($this->getDocumentIdentifier($entity), $this->map($entity, $type));
        $type->addDocument($document);
    }

    /**
     * @param object $entity
     * @param Type $type
     */
    public function removeIndex($entity, Type $type)
    {
        $this->removeIndexById($this->getDocumentIdentifier($entity), $type);
    }

    /**
     * @param integer $id
     * @param Type $type
     */
    public function removeIndexById($id, Type $type)
    {
        try {
            $type->deleteById($id);
        }
        catch(\InvalidArgumentException $e){}
        catch(NotFoundException $e){}
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

}
