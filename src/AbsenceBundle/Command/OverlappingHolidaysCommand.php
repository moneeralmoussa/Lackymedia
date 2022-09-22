<?php

namespace AbsenceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AbsenceBundle\Entity\AbsenceDetailClearing;

class OverlappingHolidaysCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('absence:overlapping')
            ->setDescription('...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $absenceDetailClearing = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findAllOverlapping();
        dump($absenceDetailClearing);
    }
}
