<?php

namespace AbsenceBundle\Service;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use EmployeeBundle\Entity\AbsenceClearing;
use EmployeeBundle\Entity\Employee;
use AbsenceBundle\Entity\Absence;

class AbsenceClearingService
{
    private $doctrine;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function updateClearing(Employee $employee, Absence $absence)
    {
        $holidayNew = is_null($employee->getContract()) ? 0 : $employee->getContract()->getHolidays();
        $remainingOld = $employee->getRemainingDaysOfVacation();
        $year = Carbon::instance($absence->getFromDate())->year;

        $substract = 0;
        $additional = 0;

        foreach ($employee->getAbsenceClearings() as $absenceClearing) {
            if ($absenceClearing->getYear() == $year) {
                $substract = $absenceClearing->getSubstractDaysOfVacation();
                $additional = $absenceClearing->getAdditionalDaysOfVacation();
                $remainingOld = (empty($absenceClearing->getRemainingDaysOfVacation())?$remainingOld:$absenceClearing->getRemainingDaysOfVacation());
            }
        }

        $holiday = $holidayNew + $remainingOld - $substract + $additional;
        $used = count($this->doctrine->getRepository('AbsenceBundle:Absence')->getAllHolidaysInPeriode($employee, Carbon::create($year)->startOfYear()->format('Y-m-d'), Carbon::create($year)->endOfYear()->format('Y-m-d'), Carbon::create($year)->endOfYear()->endOfDay()));
        // $remaining = $holiday - $this->doctrine->getRepository('AbsenceBundle:Absence')->getHolidaysByYear($employee,1,$year);
        $remaining = $holiday - $used;
        $nextyear = $year + 1;
        $updated = false;
        foreach ($employee->getAbsenceClearings() as $absenceClearing) {
            if ($absenceClearing->getYear() == $nextyear) {
                $absenceClearing->setRemainingDaysOfVacation($remaining);
                $em = $this->doctrine->getManager();
                $em->persist($absenceClearing);
                $em->flush();
                $updated = true;
            }
        }
        if ($updated == false) {
            $ac = new AbsenceClearing();
            $ac->setYear($nextyear);
            $ac->setEmployee($employee);
            $ac->setRemainingDaysOfVacation($remaining);
            $em = $this->doctrine->getManager();
            $em->persist($ac);
            $em->flush();
        }
    }

    public function updateClearingByYear(Employee $employee, $year)
    {
        $holidayNew = is_null($employee->getContract()) ? 0 : $employee->getContract()->getHolidays();
        $remainingOld = $employee->getRemainingDaysOfVacation();
        $year = strval($year);

        $substract = 0;
        $additional = 0;

        foreach ($employee->getAbsenceClearings() as $absenceClearing) {
            if ($absenceClearing->getYear() == $year) {
                $substract = $absenceClearing->getSubstractDaysOfVacation();
                $additional = $absenceClearing->getAdditionalDaysOfVacation();
                $remainingOld = (empty($absenceClearing->getRemainingDaysOfVacation())?$remainingOld:$absenceClearing->getRemainingDaysOfVacation());
            }
        }
        $holiday = $holidayNew + $remainingOld - $substract + $additional;
        $used = count($this->doctrine->getRepository('AbsenceBundle:Absence')->getAllHolidaysInPeriode($employee, Carbon::create($year)->startOfYear()->format('Y-m-d'), Carbon::create($year)->endOfYear()->format('Y-m-d'), Carbon::create($year)->endOfYear()->endOfDay()));
        // $remaining = $holiday - $this->doctrine->getRepository('AbsenceBundle:Absence')->getHolidaysByYear($employee,1,$year);
        $remaining = $holiday - $used;
        $nextyear = $year + 1;

        $updated = false;
        foreach ($employee->getAbsenceClearings() as $absenceClearing) {
            if ($absenceClearing->getYear() == $nextyear) {
                $absenceClearing->setRemainingDaysOfVacation($remaining);
                $em = $this->doctrine->getManager();
                $em->persist($absenceClearing);
                $em->flush();
                $updated = true;
            }
        }
        if ($updated == false) {
            $ac = new AbsenceClearing();
            $ac->setYear($nextyear);
            $ac->setEmployee($employee);
            $ac->setRemainingDaysOfVacation($remaining);
            $em = $this->doctrine->getManager();
            $em->persist($ac);
            $em->flush();
        }
    }
}
