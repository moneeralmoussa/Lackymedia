<?php

namespace AbsenceBundle\Service;

use Carbon\Carbon;
use EmployeeBundle\Entity\Employee;
use AbsenceBundle\Entity\Absence;
use EmployeeBundle\Entity\AbsenceClearing;
use AbsenceBundle\Entity\Holidayschedule;

class AbsenceService
{
    private $doctrine;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getHolidays($year, Employee $employee)
    {
        // Array initialisieren
        $holidays = [
            'remaining' => 0,
            'contract' => 0,
            'additional' => 0,
            'substract' => 0,
            'taken' => 0,
            'sum' => 0,
        ];

        if ($year < 2018) {
            return $holidays;
        }


        // Die Initialen Werte vor beginn der Nutzung des Kalendermoduls
        if ($employee->getRemainingDaysOfVacation() && $year == 2018) {
            $holidays['remaining'] = floatval($employee->getRemainingDaysOfVacation());
        }

        // Falls kein Eintrittsdatum ausgewählt manuell auf 2018 setzten [2018 wure der Kalender eingeführt]
        $entry_date = $employee->getEntryDate() ? Carbon::instance($employee->getEntryDate())->year : Carbon::create(2018, 1, 1)->year;

        // Falls welche für Letztes Jahr existieren, dann die nehmen.
        if ($this->getHolidays($year-1, $employee) && $this->getHolidays($year-1, $employee)['sum'] && $entry_date <= (new Carbon())->year) {
            $holidays['remaining'] = $this->getHolidays($year-1, $employee)['sum'];
        }

        // Vertraglich geregelte Urlaubstage
        if ($employee->getContract()) {
          //code Holidayschedule
            $holidayschedule = $this->doctrine->getRepository('AbsenceBundle:Holidayschedule')->getHolidayscheduleByEmployeeAndYear($employee,$year);
            if(!empty($holidayschedule))
            {
                $holidays['contract'] = $holidayschedule->getHoliday();
            }
            else{
                $holidays['contract'] = $employee->getContract()->getHolidays();
            }
          //end Holidayschedule
        }
        // Werte für dieses Jahr
        $absenceClearing = $this->doctrine->getRepository('EmployeeBundle:AbsenceClearing')->getAbsenceClearing($year, $employee);

        if ($absenceClearing) {
            $holidays['additional'] = floatval($absenceClearing['additional']);
            $holidays['substract'] = floatval($absenceClearing['substract']);
        }

        // Alle genommenen Tage ermitteln
        $taken = $this->doctrine->getRepository('AbsenceBundle:AbsenceDetailClearing')->getSumHolidaysAbsenceDetailClearingsByYear($year, $employee);

        if ($taken) {
            $holidays['taken'] = $taken;
        }

        if ($year < $entry_date) {
            $holidays = [
                'remaining' => 0,
                'contract' => 0,
                'additional' => 0,
                'substract' => 0,
                'taken' => 0,
                'sum' => 0,

            ];

        }

        // Summe berechnen
        $holidays['sum'] = $holidays['remaining'] + $holidays['contract'] + $holidays['additional'] - $holidays['substract'] - $holidays['taken'];

        return $holidays;
    }

    public function getRemainingMtl2($employeeIds, Carbon $start, Carbon $end, $year)
    {
        $employees = $this->doctrine->getRepository('EmployeeBundle:Employee')->findById($employeeIds);
        $resources = [];

        foreach ($employees as $employee) {
            $holidays = $this->getHolidays($year, $employee);

            $days = $holidays['remaining'] + $holidays['contract'] + $holidays['additional'] - $holidays['substract'];

            $taken = $this->doctrine->getRepository('AbsenceBundle:AbsenceDetailClearing')->findSumHolidaysAbsenceDetailClearingsByStartAndEnd($employee, $start->format('Y-m-d'), $end->format('Y-m-d'));

            $remaining = $days - $taken;

            $resources[] = [
              "id"  => $employee->getId(),
              "remainingmtl"  => is_null($remaining) ? 0 : strval(sprintf("%.1f", $remaining)),
            ];
        }

        if (count($resources) == 1) {
            $resources = $resources[0];
        }

        return $resources;
    }

    public function getDaysByReason($employee, $year)
    {
        $statistic = $this->doctrine->getRepository('AbsenceBundle:Absence')->getDaysByReason2($employee, $year);

        //Array vorbereiten
        $arrStatistic = [];
        foreach ($statistic as $s) {
            $arrStatistic[$s['reason']->getName()] = [
                'days' => floatval($s['days']),
                'color' => $s['reason']->getColor(),
            ];
        }

        return $arrStatistic;
    }

    public function getUsedAbsenceMonthly($date, $employee)
    {
        $start = new Carbon($date);
        $end = new Carbon($date);
        $month = $start->month;
        $year = $start->copy()->year;
        $start = $start->startOfMonth()->format('Y-m-d');
        $end = $end->endOfMonth()->format('Y-m-d');
        $test = $this->doctrine->getRepository('AbsenceBundle:Absence')->getAbsencesBetweenByEmployeeID($employee, $start, $end);

        $new = array();
        foreach ($test as $t) {
            array_push($new, $this->doctrine->getRepository('AbsenceBundle:Absence')->getSplittedInMonthsHolidaysByEmployeeDate($employee, ($t['fromDate'])->format('Y-m-d'), ($t['toDate'])->format('Y-m-d')));
        }
        $used = 0;
        foreach ($new as $n) {
            foreach ($n as $x) {
                if ($x['month'] <= $month) {
                    $used += floatval($x['days']);
                }
            }
        }
        ////////////////////////////////
        // $holidayNew = is_null($employee->getContract()) ? 0 : $employee->getContract()->getHolidays();
        // $remainingOld = $employee->getRemainingDaysOfVacation();
        //
        // $substract = 0;
        // $additional = 0;
        //
        // foreach($employee->getAbsenceClearings() as $absenceClearing) {
        //     if ($absenceClearing->getYear() == $year) {
        //         $substract = $absenceClearing->getSubstractDaysOfVacation();
        //         $additional = $absenceClearing->getAdditionalDaysOfVacation();
        //         $remainingOld = (empty($absenceClearing->getRemainingDaysOfVacation())?$remainingOld:$absenceClearing->getRemainingDaysOfVacation());
        //     }
        // }
        //
        // $holiday = $holidayNew + $remainingOld - $substract + $additional;
        // // $holiday = $employee->getHoliday();
        // $remaining = $holiday - $used;
        return $used;
    }

    public function getWorkingDays($start, $end)
    {
        $start = Carbon::parse($start)->StartofDay();
        $end = Carbon::parse($end)->EndOfDay();
        $days = $start->diffInDaysFiltered(function (Carbon $date) {
            return !$date->isWeekend();
        }, $end);

        return $days;
    }

    public function getHolidayStatistik(Employee $employee, Carbon $year)
    {
        $thisyear = $year->startOfYear();
        $nextyear = $year->copy()->addYear()->startOfYear();
        $remainingOld = floatval($employee->getRemainingDaysOfVacation());
        $holidayNew = is_null($employee->getContract()) ? 0 : $employee->getContract()->getHolidays();
        $substract = 0;
        $additional = 0;
        foreach ($employee->getAbsenceClearings() as $absenceClearing) {
            if ($absenceClearing->getYear() == $year->year) {
                $substract = floatval($absenceClearing->getSubstractDaysOfVacation());
                $additional = floatval($absenceClearing->getAdditionalDaysOfVacation());
                $remainingOld = (empty($absenceClearing->getRemainingDaysOfVacation())?floatval($remainingOld):floatval($absenceClearing->getRemainingDaysOfVacation()));
            }
        }
        $holiday = $holidayNew + $remainingOld - $substract + $additional;
        $used = $this->doctrine->getRepository('AbsenceBundle:Absence')->getAllHolidaysInPeriode($employee, $thisyear->format('Y-m-d'), $thisyear->endOfYear()->format('Y-m-d'), Carbon::parse($thisyear->format('Y-m-d'))->endOfYear());
        $remaining = $holiday - count($used);
        $response = array(
          "remainingold"  => $remainingOld,
          "remaining"     => $remaining,
          "holidaynew"    => $holidayNew,
          "holiday"       => $holiday,
          "substract"     => $substract,
          "additional"    => $additional,
          "year"          => $thisyear->format('Y'),
          "nextyear"      => $nextyear->format('Y'),
      );

        return $response;
    }


    public function getRemainingMtl($employeeIds, Carbon $start, Carbon $end, $year)
    {
        $employees = $this->doctrine->getRepository('EmployeeBundle:Employee')->findById($employeeIds);

        foreach ($employees as $employee) {
            $holiday = $employee->getHolidayByYear($year);
            $used = count($this->doctrine->getRepository('AbsenceBundle:Absence')->getAllHolidaysInPeriode($employee, $start->format('Y-m-d'), $end->format('Y-m-d'), $end->copy()->endOfYear()->endOfDay()));
            $remaining = $holiday - $used;
            $resources[] = [
              "id"  => $employee->getId(),
              "remainingmtl"  => is_null($remaining) ? 0 : strval(sprintf("%.1f", $remaining)),
            ];
        }

        if (count($resources) <= 1) {
            $resources = $resources[0];
        }

        return $resources;
    }

    public function getAbsenceJson($employee, $background = false)
    {
        $repo = $this->doctrine->getRepository('AbsenceBundle:Absence');
        $repoAbsenceDetailClearing = $this->doctrine->getRepository('AbsenceBundle:AbsenceDetailClearing');


        if (is_null($employee)) {
            $absence = $repo->findAll(array('status' => '1'));
        } else {
            $absence = $repo->findBy(array('employee' => $employee, 'status' => array(1,3)));
        }

        $arrayCollection = array();

        foreach ($absence as $item) {
            if ($background) {
                $arrayCollection[] =
                array(
                    'title' => $item->getReason()->getName(),
                    'start' => $item->getFromDate()->format('Y-m-d'),
                    'end' => $item->getToDate()->modify('+1 day')->format('Y-m-d'),
                    'backgroundColor' => $item->getReason()->getColor(),
                    //'rendering' => 'background',
                    'background' => true,
                    'icon' => 'check-circle-o',
                    'halfDays' => [],
                );
            } else {
                $arrayCollection[] =
                array(
                     'id' => $item->getId(),
                     'name' => $item->getEmployee()->getFullname(),
                     'color' => $item->getReason()->getColor(),
                     'reason' => $item->getReason()->getName(),
                     'day' =>$item->getDay(),
                     'dayDetail' => $repoAbsenceDetailClearing->findAllWithAbsenceAbsenceDetailClearings($item->getEmployee(), $item->getFromDate(), $item->getToDate(), $item),
                     'halfDays' => $this->doctrine->getRepository('AbsenceBundle:AbsenceDetailClearing')->getHalfDays($item),
                     'approvedBy' => $item->getApprovedBy()->getFullname(),
                     'status' => $item->getStatus()->getName(),
                     'note' => $item->getNote(),
                     'startDate' => $item->getFromDate(),
                     'endDate' => $item->getToDate()
                 );
            }
        }
        return $arrayCollection;
    }
}
