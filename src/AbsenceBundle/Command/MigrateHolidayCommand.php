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

class MigrateHolidayCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('absence:migrate')
            ->setDescription('...')
            ->addArgument('year', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $year = $input->getArgument('year');

        if ($input->getOption('force')) {
            ini_set('memory_limit', '-1');
            $time_pre = microtime(true);
            $em = $this->getContainer()->get('doctrine')->getManager();

            $em->getConnection()->getConfiguration()->setSQLLogger(null);

            $cmd = $em->getClassMetadata('AbsenceBundle:AbsenceDetailClearing');
            $connection = $em->getConnection();
            $dbPlatform = $connection->getDatabasePlatform();
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
            $connection->executeUpdate($q);
            $connection->query('SET FOREIGN_KEY_CHECKS=1');

            $employees = $em->getRepository('EmployeeBundle:Employee')->findAll();
            // $employee = $em->getRepository('EmployeeBundle:Employee')->findOneBy(array('id' => 23));
            $employees_count = count($employees);
            $employees_counter = 0;
            foreach ($employees as $employee) {
                $absences = $em->getRepository('AbsenceBundle:Absence')->findBy(array('employee' => $employee));
                $workingdays =  $em->getRepository('AbsenceBundle:Absence')->findBy(array('employee' => $employee, 'reason' => 5));
                $publicHolidays = $em->getRepository('AbsenceBundle:PublicHoliday')->findBy(array('type' => 1));
                // $absences = $em->getRepository('AbsenceBundle:Absence')->findAllAbsencesByYear($employee, 1, $year);
                // $publicHolidays = $em->getRepository('AbsenceBundle:PublicHoliday')->findByYear($year);

                $arrPublicHolidays = [];

                foreach ($publicHolidays as $ph) {
                    $arrPublicHolidays[] = $ph->getStart()->format('Y-m-d');
                }

                foreach ($workingdays as $wd) {
                    $arrPublicHolidays[] = $wd->getFromDate()->format('Y-m-d');
                }

                foreach ($absences as $absence) {
                    $period = CarbonPeriod::create($absence->getFromDate(), $absence->getToDate());

                    $days = Carbon::instance($absence->getFromDate())->startOfDay()->diffInDaysFiltered(function (Carbon $date) use ($arrPublicHolidays) {
                        return !$date->isWeekend() && !in_array($date->format('Y-m-d'), $arrPublicHolidays);
                    }, Carbon::instance($absence->getToDate())->endOfDay());

                    foreach ($period as $date) {
                        if (in_array($date->format('Y-m-d'), $arrPublicHolidays) || $date->isWeekend()) {
                            continue;
                        }

                        $absenceDetailClearing = new AbsenceDetailClearing();

                        $absenceDetailClearing->setEmployee($employee);

                        $absenceDetailClearing->setReason($absence->getReason());

                        $absenceDetailClearing->setUseAsHolidays($absence->getReason()->getUseAsHolidays());

                        $absenceDetailClearing->setDate($date);

                        $absenceDetailClearing->setDayDetail(1);

                        if ((abs(floatval($absence->getDay()) - $days) == 0.5) && $date->format('Y-m-d') == $period->getEndDate()->format('Y-m-d')) {
                            $absenceDetailClearing->setDayDetail(0.5);
                        }

                        $absenceDetailClearing->setAbsence($absence);

                        $em->persist($absenceDetailClearing);
                        $output->writeln($employees_counter.'/'.$employees_count.' ID: '.$employee->getId().' '.$date->format('Y-m-d').' vorbereitet. Memory: '.(memory_get_peak_usage(true)/1024/1024)." MiB");
                    }
                }
                $em->flush();
                $em->detach($absenceDetailClearing);
                $em->clear(AbsenceDetailClearing::class);
                $output->writeln("In die Datenbank übertragen!");
                $employees_counter++;
            }
            $time_post = microtime(true);
            $exec_time = $time_post - $time_pre;
            $output->writeln('Dauer: '.$exec_time);
        } else {
            $output->writeln('Achtung: Bei diesem Befehl wird die DB Tabelle absence_detail_clearing truncated und durch die eingetragenen Urlaube in der DB Tabelle absence neu generiert');
            $output->writeln('Wenn du dir sicher bist führe den Befehl mit dem Parameter --force aus');
        }
    }
}
