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

    const DEFAULT_LIMIT_PER_PAGE = 10;

    /**
     * @param \Elastica_Query $query
     * @param $index
     */
    public function __construct(\Elastica_Query $query, $index)
    {
        $this->elasticaQuery = $query;
        $this->index = $index;

        return $this;
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
     * @return ElasticaPaginator
     */
    public function setPage($page)
    {
        $page = $page > 0 ? $page : 1;
        $this->page = $page;

        return $this;
    }

    /**
     * @param int $limitPerPage
     * @return ElasticaPaginator
     */
    public function setLimitPerPage($limitPerPage)
    {
        $limitPerPage = $limitPerPage > 0 ? $limitPerPage : self::DEFAULT_LIMIT_PER_PAGE;
        $this->limitPerPage = $limitPerPage;

        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        if ($this->resultSet == null) {
            $this->getIterator();
        }

        return $this->resultSet->getTotalHits();
    }

    /**
     * @return \Traversable
     */
    public function getIterator()
    {
        $this->elasticaQuery->setFrom($this->getOffset());
        $this->elasticaQuery->setLimit($this->limitPerPage);

        $this->resultSet = $this->elastica->search($this->elasticaQuery, $this->index);

        return new \ArrayIterator($this->resultSet->getResults());
    }

    /**
     * @return \Elastica_ResultSet
     */
    public function getResultSet()
    {
        if ($this->resultSet == null) {
            $this->getIterator();
        }

        return $this->resultSet;
    }
}