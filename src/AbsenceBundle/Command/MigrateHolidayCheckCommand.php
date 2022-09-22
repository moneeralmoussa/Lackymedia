<?php

namespace AbsenceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use AbsenceBundle\Entity\AbsenceDetailClearing;

class MigrateHolidayCheckCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('absence:migrate:check')
            ->setDescription('...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', '-1');
        $time_pre = microtime(true);
        $em = $this->getContainer()->get('doctrine')->getManager();
        $employees = $em->getRepository('EmployeeBundle:Employee')->findAll();


        $employees_count = count($employees);
        $employees_counter = 0;
        foreach ($employees as $employee) {
            $absences = $em->getRepository('AbsenceBundle:Absence')->findBy(array('employee' => $employee));
            $workingdays =  $em->getRepository('AbsenceBundle:Absence')->findBy(array('employee' => $employee, 'reason' => 5));
            $publicHolidays = $em->getRepository('AbsenceBundle:PublicHoliday')->findBy(array('type' => 1));

            $arrPublicHolidays = [];

            foreach ($publicHolidays as $ph) {
                $arrPublicHolidays[] = $ph->getStart()->format('Y-m-d');
            }

            foreach ($workingdays as $wd) {
                $arrPublicHolidays[] = $wd->getFromDate()->format('Y-m-d');
            }

            foreach ($absences as $absence) {
                $period = CarbonPeriod::create($absence->getFromDate(), $absence->getToDate());
                $absenceDetailClearing = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findBy(array('absence' => $absence));
                $days = Carbon::instance($absence->getFromDate())->startOfDay()->diffInDaysFiltered(function (Carbon $date) use ($arrPublicHolidays) {
                    return !$date->isWeekend() && !in_array($date->format('Y-m-d'), $arrPublicHolidays);
                }, Carbon::instance($absence->getToDate())->endOfDay());

                if (count($absenceDetailClearing) != $days) {
                    $output->writeln($employees_counter.'/'.$employees_count.' ID: '.$employee->getId().' '.count($absenceDetailClearing). ' '.$days.' '.$absence->getFromDate()->format('Y-m-d').' '.$absence->getToDate()->format('Y-m-d').' Memory: '.(memory_get_peak_usage(true)/1024/1024)." MiB");
                }
            }
            $employees_counter++;
        }
        $time_post = microtime(true);
        $exec_time = $time_post - $time_pre;
        $output->writeln('Dauer: '.$exec_time);
    }
}
