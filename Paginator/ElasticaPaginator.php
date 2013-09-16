<?php

namespace Snowcap\ElasticaBundle\Paginator;

use Doctrine\ORM\AbstractQuery;
use Snowcap\CoreBundle\Paginator\AbstractPaginator;
use Snowcap\ElasticaBundle\Service;

class ElasticaPaginator extends AbstractPaginator
{
    /**
     * @var \Elastica_Query
     */
    private $elasticaQuery;

    /**
     * @var \Snowcap\ElasticaBundle\Service
     */
    private $elastica;

    /**
     * @var \Elastica_ResultSet
     */
    private $resultSet = null;

    /**
     * @var string
     */
    private $index;

    /**
     * @param \Elastica_Query $query
     * @param $index
     * @param Service $elastica
     */
    public function __construct(\Elastica_Query $query, $index, Service $elastica = null)
    {
        $this->elasticaQuery = $query;
        $this->index = $index;
        $this->elastica = $elastica;
    }

    /**
     * @param Service $elastica
     * @return ElasticaPaginator
     */
    public function setElasticaService(Service $elastica)
    {
        $this->elastica = $elastica;

        return $this;
    }

    /**
     * @param int $page
     * @return $this|\Snowcap\CoreBundle\Paginator\PaginatorInterface
     * @throws \InvalidArgumentException
     */
    public function setPage($page)
    {
        if($page < 1) {
            throw new \InvalidArgumentException('The page is invalid');
        }
        $this->page = $page;

        return $this;
    }

    /**
     * @param int $limitPerPage
     * @return ElasticaPaginator
     * @throws \InvalidArgumentException
     */
    public function setLimitPerPage($limitPerPage)
    {
        if ($limitPerPage <= 0) {
            throw new \InvalidArgumentException('The limit per page is invalid');
        }
        $this->limitPerPage = $limitPerPage;

        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        if (null === $this->resultSet) {
            $this->search();
        }

        return $this->resultSet->getTotalHits();
    }

    /**
     * @return \Traversable
     */
    public function getIterator()
    {
        if (null === $this->resultSet) {
            $this->search();
        }

        return $this->resultSet;
    }

    /**
     * @return \Elastica_ResultSet
     */
    public function getResultSet()
    {
        if (null === $this->resultSet) {
            $this->search();
        }

        return $this->resultSet;
    }

    /**
     * Launch the search
     *
     */
    private function search()
    {
        $this->elasticaQuery->setLimit($this->limitPerPage);
        $this->elasticaQuery->setFrom($this->getOffset());
        $this->resultSet = $this->elastica->search($this->elasticaQuery, $this->index);
    }
}
