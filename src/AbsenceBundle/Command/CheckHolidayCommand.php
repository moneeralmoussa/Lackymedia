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

class CheckHolidayCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('absence:check-holiday')
            ->setDescription('...')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $absences = $em->getRepository('AbsenceBundle:Absence')->findAll();

        foreach ($absences as $absence) {
            $holidays = $em->getRepository('AbsenceBundle:PublicHoliday')->findDaysBetween(
                Carbon::instance($absence->getFromDate())->startOfDay(),
                Carbon::instance($absence->getToDate())->endOfDay()
                );

            array_walk($holidays, function (&$key) {
                $key = $key['start']->format('Y-m-d');
            });

            $weekendDays = 0;
            $days = Carbon::instance($absence->getFromDate())->startOfDay()->diffInDaysFiltered(function (Carbon $date) use ($holidays, &$weekendDays) {
                if ($date->isWeekend()) {
                    $weekendDays++;
                }

                return !$date->isWeekend() && !in_array($date->format('Y-m-d'), $holidays);
            }, Carbon::instance($absence->getToDate())->endOfDay());

            $period = CarbonPeriod::create($absence->getFromDate(), $absence->getToDate());

            if (floatval($period->count()) == $absence->getDay()) {
                continue;
            }

            $diff = [
                $absence->getId(),
                $absence->getEmployee()->getFullname(),
                $absence->getFromDate()->format('d.m.Y'),
                $absence->getToDate()->format('d.m.Y'),
                $period->count(),
                $weekendDays,
                count($holidays),
                floatval($absence->getDay()),
                $days,
                floatval($absence->getDay()) != $days ? 'X' : '',
            ];

            $arrDiff[] = $diff;

            $output->writeln(implode(" | ", $diff));
        }

        $headline = [
            'Abwesenheits ID',
            'Name',
            'Urlaub von',
            'Urluab bis',
            'Gesamte Tage',
            'Wochenenden',
            'Feiertage',
            'eingetragene Urlaubstage',
            'Berechnete Urlaubstage',
            'kontrollieren'
        ];
        array_unshift($arrDiff, $headline);
        $fp = $this->getContainer()->getParameter('kernel.root_dir').'/../web/files/';
        $file = $fp.'urlaubstage_differenz_'.Carbon::now('Europe/Amsterdam')->format('Y-m-d H:i').'.csv';
        $handle = fopen($file, 'w+');
        foreach ($arrDiff as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}
