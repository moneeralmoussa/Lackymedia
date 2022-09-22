<?php

namespace TrimbleSoapBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TrimbleSoapPollTracesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('trimble:soap:poll-traces')
            ->setDescription('Polls Traces from Trimble-Soap-Server')
            ->setHelp("Usage:\nInstruction 123")
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argument = $input->getArgument('argument');

        if ($input->getOption('option')) {
            // ...
        }

        // Geht so nicht: $traces = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findAll();
        
        $output->writeln('Command result.');
        $output->writeln(count($traces));
    }

}
