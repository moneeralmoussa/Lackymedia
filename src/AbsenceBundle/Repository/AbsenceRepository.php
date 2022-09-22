<?php

namespace AbsenceBundle\Repository;

use EmployeeBundle\Entity\Employee;
use AbsenceBundle\Entity\Absence;
use Doctrine\ORM\Query;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * AbsenceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AbsenceRepository extends \Doctrine\ORM\EntityRepository
{
    public function getDaysByReason2(Employee $employee, $year)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('r as reason, SUM(adc.dayDetail) as days')
            ->from('AbsenceBundle:AbsenceDetailClearing', 'adc')
            ->join('adc.absence', 'a')
            ->join('AbsenceBundle:Reason', 'r', 'WITH', 'r.id = adc.reason')
            ->where('YEAR(adc.date) = :year')
            ->andWhere('a.employee = :employee')
            ->groupBy('r.id')
            ->setParameters(array('employee' => $employee, 'year' => $year));
        return $qb->getQuery()->getResult();
    }

    public function getDaysByReason(Employee $employee, $year)
    {

    //Statistik Jählich machen
        $startyear = Carbon::create($year)->startOfYear()->format('Y-m-d');
        $endyear = Carbon::create($year)->endOfMonth()->endOfDay()->format('Y-m-d');
        // $startyear = '2019-01-01';
        // $endyear = '2019-12-31';

        global $kernel;
        if ($kernel instanceof \AppCache) {
            $kernel = $kernel->getKernel();
        }

        // $absence = $this->findBy(array('employee' => $employee, 'status' =>array("1","3")));
        $absence = $this->getHolidaysByEmployeeDate($employee, $startyear, $endyear);
        $absenceMerged = array();
        foreach ($absence as $row) {
            if (!isset($absenceMerged[$row->getReason()->getName()])) {
                $absenceMerged[$row->getReason()->getName()]['days'] = 0;
            }

            if ($row->getReason()->getName() == 'Urlaub' && $row->getStatus()->getId() == 3) {
                if (!isset($absenceMerged["unbestätigter Urlaub"])) {
                    $absenceMerged["unbestätigter Urlaub"]['days'] = 0;
                }

                $absenceMerged['unbestätigter Urlaub'] = array(
          'days'  =>  $absenceMerged['unbestätigter Urlaub']['days'] += $row->getDay(),
          'color' =>  $row->getReason()->getColor(),
        );
            } else {
                $color = $row->getReason()->getColor();
                if ($row->getReason()->getName() == 'Urlaub' && $row->getStatus()->getId() == 1) {
                    $color = '#2ecc71';
                }

                $absenceMerged[$row->getReason()->getName()] = array(
          'days'  =>  $absenceMerged[$row->getReason()->getName()]['days'] += $row->getDay(),
          'color' =>  $color,
        );
            }
        }

        $absenceService = $kernel->getContainer()->get('app.absence_service', $kernel->getContainer()->get('doctrine'));

        $workingdays = array('Arbeitstage' => [
      'days' => $absenceService->getWorkingDays($startyear, $endyear),
      'color' => '#34495e'
      ]);

        $absenceMerged = array_merge($workingdays, $absenceMerged);

        return $absenceMerged;
    }

    public function getDayCount(Employee $employee)
    {
        $absences = array_sum($this->getDaysByReason($employee));

        if (!isset($absence)) {
            $absence = 0;
        }

        return $absences;
    }


    public function findAbsencesByYear($employee, $status, $year)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
            ->from('AbsenceBundle:Absence', 'a')
            ->join('a.reason', 'r')
            ->where('a.employee = :employee')
            ->andWhere('r.use_as_holidays = 1')
            ->andWhere('a.status = :status')
            ->andWhere('YEAR(a.fromDate) = :year')
            ->setParameters(array('employee' => $employee, 'status' => $status, 'year' => $year));
        return $qb->getQuery()->getResult();
    }


    public function findAllAbsencesByYear($employee, $status, $year)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
                ->from('AbsenceBundle:Absence', 'a')
                ->join('a.reason', 'r')
                ->where('a.employee = :employee')
                ->andWhere('a.status = :status')
                ->andWhere('YEAR(a.fromDate) = :year')
                ->setParameters(array('employee' => $employee, 'status' => $status, 'year' => $year));
        return $qb->getQuery()->getResult();
    }
    public function findAllAbsencesByYearCalendarJson($year)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
                ->from('AbsenceBundle:Absence', 'a')
                ->Where('YEAR(a.fromDate) = :year')
                ->orWhere('YEAR(a.toDate) = :year')
                ->setParameters(array('year' => $year));
        return $qb->getQuery()->getResult();
    }

    public function getHolidays($employee, $status)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('SUM(a.day) as days')
        ->from('AbsenceBundle:Absence', 'a')
        ->join('a.reason', 'r')
        ->where('a.employee = :employee')
        ->andWhere('r.use_as_holidays = 1')
        ->andWhere('a.status = :status')
        ->setParameters(array('employee' => $employee, 'status' => $status));
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getHolidaysByYear($employee, $status, $year)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('SUM(a.day) as days')
          ->from('AbsenceBundle:Absence', 'a')
          ->join('a.reason', 'r')
          ->where('a.employee = :employee')
          ->andWhere('YEAR(a.toDate) = :year')
          ->andWhere('r.use_as_holidays = 1')
          ->andWhere('a.status = :status')
          ->setParameters(array('employee' => $employee, 'status' => $status, 'year' => $year));
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAbsencesBetween($start, $end)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')
        ->from('AbsenceBundle:Absence', 'f')
        ->where('f.fromDate >= :start')
        ->andWhere('f.toDate <= :end')
        ->setParameters(array('start' => $start, 'end' => $end));

        return $qb->getQuery()->getArrayResult();
    }

    public function getAbsencesBetweenByEmployeeID($employee, $start, $end)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')
      ->from('AbsenceBundle:Absence', 'f')
      ->join('f.reason', 'r')
      ->where('f.fromDate BETWEEN :start AND :end OR f.toDate BETWEEN :start AND :end ')
      ->andWhere('f.employee = :employee')
      ->andWhere('f.status = 1')
      ->andWhere('r.use_as_holidays = 1')
      ->setParameters(array('start' => $start, 'end' => $end, 'employee' => $employee));

        return $qb->getQuery()->getArrayResult();
    }

    public function getAbsencesBetweenByDepartment($start, $end)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')
      ->from('AbsenceBundle:Absence', 'f')
      ->join('f.employee', 'e', 'e.name')
      ->join('e.department', 'd')
      ->where('f.fromDate >= :start')
      ->andWhere('f.toDate <= :end')
      ->indexBy('e.name', 'e.name')
      ->setParameters(array('start' => $start, 'end' => $end));

        return $qb->getQuery()->getArrayResult();
    }

    public function getAbsencesBetweenByEmployee($start, $end)
    {
        $start = new Carbon($start);
        $end = new Carbon($end);
        $employee = $this->getEntityManager()->getRepository('EmployeeBundle:Employee');

        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')
      ->from('AbsenceBundle:Absence', 'f')
      ->where('f.fromDate >= :start')
      ->andWhere('f.toDate <= :end')
      ->setParameters(array('start' => $start, 'end' => $end));

        $r = $qb->getQuery()->getArrayResult();
        var_dump($r);
        die;
        $result = [];
        foreach ($r as $row) {
            $name = $employee->findById($row['employeeID']);
            $result[$name[0]->getFullname()][] = $row;
        }
        return $result;

        // $em = $this->getDoctrine()->getManager();
  // $employees = $em->getRepository('EmployeeBundle:Employee')->findAll();
  // $e = array();
  // foreach ($employees as $employee) {
    // if(!isset($absenceMerged[$employee->getReason()->getName()]))
    // $e[$employee->getReason()->getName()] = 0;

    // $e[$employee->getReason()->getName()] = array(
    //   'absences' => array();
    // );
  // }

  // return $e;
    }

    public function getSumDays($start, $end)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('SUM(f.day) as days')
      ->from('AbsenceBundle:Absence', 'f')
      ->where('f.fromDate >= :start')
      ->andWhere('f.fromDate <= :end')
      ->setParameters(array('start' => $start, 'end' => $end));
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getLeftSumDays($start, $end)
    {
        $leftDays = $this->getSumContractHolidays() - $this->getSumDaysBySpecificReason($start, $end, 'Urlaub');
        return $leftDays;
    }

    public function getSumContractHolidays()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('SUM(f.holidays) as holidays')
          ->from('EmployeeBundle:Contract', 'f');
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getSumDaysByReason($start, $end)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('SUM(f.day) as days', 'r.name')
        ->from('AbsenceBundle:Absence', 'f')
        ->where('f.fromDate >= :start')
        ->andWhere('f.fromDate <= :end')
        ->join('f.reason', 'r')
        ->groupBy('f.reason')
        ->setParameters(array('start' => $start, 'end' => $end));
        return $qb->getQuery()->getArrayResult();
    }
    public function getSumDaysBySpecificReason($start, $end, $reason)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('SUM(f.day) as days')
          ->from('AbsenceBundle:Absence', 'f')
          ->where('f.fromDate >= :start')
          ->andWhere('f.fromDate <= :end')
          ->join('f.reason', 'r')
          ->andWhere('r.name = :reason')
          ->groupBy('f.reason')
          ->setParameters(array('start' => $start, 'end' => $end, 'reason' => $reason));
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllDatesFromPeriodeForEmployee($employee, $start, $end)
    {
        $absences = $this->getEntityManager()->getRepository('AbsenceBundle:Absence')->getAbsencesBetweenByEmployeeID($employee, $start, $end);

        $dates = array();
        foreach ($absences as $absence) {
            $period = CarbonPeriod::create(($absence['fromDate'])->format('Y-m-d'), ($absence['toDate'])->format('Y-m-d'));
            // dump($period);
            foreach ($period as $date) {
                array_push($dates, $date);
            }
        }
        return $dates;
    }

    public function getAllHolidaysInPeriode($employee, $start, $end, $periode_end)
    {
        $absences = $this->getEntityManager()->getRepository('AbsenceBundle:Absence')->getAllDatesFromPeriodeForEmployee($employee, $start, $end);

        $dates = array();
        foreach ($absences as $absence) {
            $public_holiday = $this->getEntityManager()->getRepository('AbsenceBundle:PublicHoliday')->isPublicHoliday($absence);
            if (
            (!$absence->isWeekend()) &&
            ($absence <= $periode_end) &&
            ($absence >= $start) &&
            is_null($public_holiday)
          ) {
                array_push($dates, $absence);
            }
        }

        return $dates;
    }

    public function getSumDaysByDepartment()
    {
        $start = Carbon::create(Carbon::now()->year, 1, 1, 0);
        $union= 'SELECT subq.date, r0.name as reason, d0.name as department, count(a0.id) as days FROM (';
        do {
            $union .= "SELECT '{$start->toDateString()}' AS date UNION ";
            $start->addDay();
        } while ($start->year == Carbon::now()->year);

        $union = substr($union, 0, -7);
        $union .= ") subq LEFT JOIN absence a0 ON (subq.date between a0.from_date AND a0.to_date) LEFT JOIN employee e0 ON (a0.employee_id = e0.id) LEFT JOIN reason r0 ON (r0.id = a0.reason_id) LEFT JOIN department d0 ON(d0.id = e0.department_id) GROUP BY subq.date, a0.reason_id,e0.department_id HAVING days > 0;";
        $stmt = $this->_em->getConnection()->prepare($union);
        $stmt->execute();
        $res = $stmt->fetchAll();
        return $res;
    }

    public function getAbsenceByReason(Reason $reason)
    {
        return $this->findBy(
            array(
              'employee'  => $employee,
              'reason'    => $reason
            ),
            array(
              'createdat'=>'desc'
            )
          );
    }

    public function getAbsencesByEmployeeDate($employee, $start, $end)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')
            ->from('AbsenceBundle:Absence', 'f')
            ->where('f.employee = :employee')
            ->andWhere('f.status = 1 OR f.reason IN (7,8,9,10)')
            ->andWhere('f.fromDate BETWEEN :start AND :end OR f.toDate BETWEEN :start AND :end OR :start BETWEEN f.fromDate AND f.toDate OR :end BETWEEN f.fromDate AND f.toDate')
            ->setParameters(array('employee' => $employee, 'start' => $start, 'end' => $end));

        return $qb->getQuery()->getResult();
    }

    public function getSchooldaysByEmployeeDate($employee, $start, $end)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')
            ->from('AbsenceBundle:Absence', 'f')
            ->where('f.employee = :employee')
            ->andWhere('f.status = 1')
            ->andWhere('f.reason IN (11,12)')
            ->andWhere('f.fromDate BETWEEN :start AND :end OR f.toDate BETWEEN :start AND :end')
            ->setParameters(array('employee' => $employee, 'start' => $start, 'end' => $end));

        return $qb->getQuery()->getResult();
    }

    public function getHolidaysByEmployeeDate($employee, $start, $end)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')
            ->from('AbsenceBundle:Absence', 'f')
            ->where('f.employee = :employee')
            ->andWhere('f.status = 1')
            ->andWhere('f.reason IN (2,3,4)')
            ->andWhere('f.fromDate BETWEEN :start AND :end OR f.toDate BETWEEN :start AND :end')
            ->setParameters(array('employee' => $employee, 'start' => $start, 'end' => $end));

        return $qb->getQuery()->getResult();
    }

    public function getIllnessesByEmployeeDate($employee, $start, $end)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')
            ->from('AbsenceBundle:Absence', 'f')
            ->where('f.employee = :employee')
            ->andWhere('f.status = 1')
            ->andWhere('f.reason IN (8,9)')
            ->andWhere('f.fromDate BETWEEN :start AND :end OR f.toDate BETWEEN :start AND :end')
            ->setParameters(array('employee' => $employee, 'start' => $start, 'end' => $end));

        return $qb->getQuery()->getResult();
    }

    public function getSumHolidaysByEmployeeDate($employee, $start, $end)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('SUM(f.day) as days')
            ->from('AbsenceBundle:Absence', 'f')
            ->where('f.employee = :employee')
            ->andWhere('f.fromDate >= :start')
            ->andWhere('f.fromDate <= :end')
            ->join('f.reason', 'r')
            ->andWhere('r.use_as_holidays != 0')
            ->setParameters(array('employee' => $employee, 'start' => $start, 'end' => $end));
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getSplittedInMonthsHolidaysByEmployeeDate($employee, $start, $end)
    {
        $sql = "
    select YEAR(selected_date) as year, MONTH(selected_date) as month, count(distinct selected_Date) as days
    from
    absence a,
    (select adddate('1970-01-01',t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) selected_date from
     (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
     (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
     (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
     (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3,
     (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t4) v
    where selected_date between '{$start}' and '{$end}'
    and selected_date not in (SELECT start from public_holiday)
    and DAYOFWEEK(selected_date) not IN(1, 7)
    and employee_id = {$employee->getId()}
    and status_id = 1
    group by YEAR(selected_date), MONTH(selected_date)
    ";

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAbsencesByStatus($employee, $status)
    {
        return $this->findBy(
            array(
          'employee'  =>  $employee,
          'status'    =>  $status
        ),
            array(
          'createdat'=>'desc'
        )
      );
    }

    public function getAllAbsencesByStatus($status)
    {
        return $this->findBy(
            array(
          'status'    =>  $status
        ),
            array(
          'createdat'=>'desc'
        )
      );
    }

    public function getNewAbsences($start)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')
            ->from('AbsenceBundle:Absence', 'f')
            ->andWhere('f.status = 3')
            ->andWhere('f.createdat >= :start')
            ->setParameters(array('start' => $start));

        return $qb->getQuery()->getResult();
    }

    public function getIllWithoutCertification($start)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')
            ->from('AbsenceBundle:Absence', 'f')
            ->andWhere('f.reason = 7')
            ->andWhere('f.status = 3')
            ->andWhere('f.fromDate <= :start')
            ->setParameters(array('start' => $start));

        return $qb->getQuery()->getResult();
    }
}