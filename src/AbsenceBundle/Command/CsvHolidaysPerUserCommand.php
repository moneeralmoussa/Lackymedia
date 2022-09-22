<?php

namespace AbsenceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Carbon\Carbon;

class CsvHolidaysPerUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('csv:holidays-per-user')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

      $year = 2018;
      $lastyear = $year - 1;
      $nextyear = $year + 1;
      $doctrine = $this->getContainer()->get('doctrine');
      $em = $doctrine->getEntityManager();
      $absenceService = $this->getContainer()->get('app.absence_service', $doctrine);;

      $employees = $em->getRepository('EmployeeBundle:Employee')->findAll();

      $fp = $this->getContainer()->getParameter('kernel.root_dir').'/../web/files/';
      $file = $fp.strtotime("now").'.csv';
      $handle = fopen($file, 'w+');

      $statistik = array();
      foreach ($employees as $employee) {

        $temp = $absenceService->getHolidayStatistik($employee, $year);
        $temp['employee'] = $employee->getFullname();
        array_push($statistik, $temp);
      }

      fputcsv($handle, array('Name', 'Resturlaub '.$lastyear, 'Urlaubstage '.$year, 'Zusätzliche Urlaubstage '.$year, 'Abzügliche Urlaubstage '.$year, 'Gesamte Urlaubstage '.$year, 'Genommene Urlaubstage '.$year, 'Übrige Urlaubstage '.$year.' / Resturlaub '.$nextyear),';');

      $employees = $em->getRepository('EmployeeBundle:Employee')->findAll();

      foreach ($statistik as $row) {
        fputcsv(
          $handle,
          array($row['employee'], $row['remainingold'], $row['holidaynew'], $row['additional'], $row['substract'], $row['holiday'], ($row['holiday'] - $row['remaining']), $row['remaining']),';');
      }

      fclose($handle);

      $output->writeln('Die Datei '.$file.' wurde erstellt.');
    }

}
