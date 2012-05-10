<?php

namespace Snowcap\ElasticaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Snowcap\ElasticaBundle\DependencyInjection\Compiler\IndexerCompilerPass;

class SnowcapElasticaBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new IndexerCompilerPass());
    }

}
