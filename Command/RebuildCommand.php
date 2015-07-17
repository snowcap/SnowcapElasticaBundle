<?php

namespace Snowcap\ElasticaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RebuildCommand
 * @package Snowcap\ElasticaBundle\Command
 */
class RebuildCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('snowcap:elastica:rebuild')
            ->setDescription('Rebuild all elastica indexes')
            ->addArgument('types', InputArgument::IS_ARRAY + InputArgument::OPTIONAL, 'Specific types to rebuild')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timeStart = microtime(true);
        $elastica = $this->getContainer()->get('snowcap_elastica.service');

        // Rebuild only given types
        if ($input->hasArgument('types') && 0 < count($input->getArgument('types'))) {
            foreach ($input->getArgument('types') as $type) {
                $output->writeln(sprintf('Rebuilding "%s" type', $type));
                $elastica->rebuildType($type);
            }
        }

        // Rebuild all types
        else {
            $output->writeln('Rebuilding all elastica indexes');
            $elastica->createIndexes();
            $elastica->reindex();
        }

        $time = number_format(microtime(true) - $timeStart, 3);
        $output->writeln('Rebuilt indexes in ' . $time . ' seconds.');
    }
}