<?php
namespace Snowcap\ElasticaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class RebuildCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('snowcap:elastica:rebuild')
            ->setDescription('Rebuild all elastica indexes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timeStart = microtime(true);

        $output->writeln('Rebuilding all elastica indexes');
        $elastica = $this->getContainer()->get('snowcap_elastica.service');
        $elastica->createIndexes();
        $elastica->reindex();

        $time = number_format(microtime(true) - $timeStart, 3);
        $output->writeln('Rebuilt indexes in ' . $time . ' seconds.');
    }
}