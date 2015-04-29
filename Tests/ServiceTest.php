<?php

namespace Snowcap\ElasticaBundle\Tests;

use Snowcap\ElasticaBundle\Service;
use Snowcap\ElasticaBundle\Tests\Mock\BarIndexer;
use Snowcap\ElasticaBundle\Tests\Mock\FooIndexer;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterIndexerWithValidManagedClass()
    {
        $mockClient = $this->getMock('Snowcap\ElasticaBundle\Client', [], [], '', false);
        $service = new Service($mockClient, 'plop');

        $service->registerIndexer('foo', new FooIndexer());
        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testRegisterIndexerWithInvalidManagedClass()
    {
        $mockClient = $this->getMock('Snowcap\ElasticaBundle\Client', [], [], '', false);
        $service = new Service($mockClient, 'plop');

        $service->registerIndexer('bar', new BarIndexer());
    }
}