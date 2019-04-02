<?php

namespace Gogol\VpsManager\App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallManagerCommand extends Command
{
    protected function configure()
    {
        $this->setName('install')
             ->setDescription('Install VPS Manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello World');
    }
}