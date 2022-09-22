<?php

namespace AbsenceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AbsenceUpdateclearingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('absence:updateclearing')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argument = $input->getArgument('argument');

        if ($input->getOption('force')) {
            $doctrine = $this->getContainer()->get('doctrine');
            $em = $doctrine->getEntityManager();

            $absenceclearingservice = $this->getContainer()->get('app.absence_clearing_service', $doctrine);;

            $employees = $em->getRepository('EmployeeBundle:Employee')->findAll();
            // $employee = $em->getRepository('EmployeeBundle:Employee')->find(8);
            foreach ($employees as $employee) {
              $absenceclearingservice->updateClearingByYear($employee,$argument);
            }

            $output->writeln('Alles Erledigt!');
        }

        $output->writeln('Bitte machen Sie ein Datenbank-Backup bevor Sie diesen Befehl ausführen. Anschliessend führen sie den Befehl mit der Option --force aus.');

    }

}
