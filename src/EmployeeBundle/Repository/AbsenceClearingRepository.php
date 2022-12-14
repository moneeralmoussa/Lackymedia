<?php

namespace EmployeeBundle\Repository;

use Carbon\Carbon;
use EmployeeBundle\Entity\Employee;

/**
 * AbsenceClearingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AbsenceClearingRepository extends \Doctrine\ORM\EntityRepository
{
    public function getAbsenceClearing($year, Employee $employee)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a.remainingDaysOfVacation as remaining, a.substractDaysOfVacation as substract, a.additionalDaysOfVacation as additional')
                ->from('EmployeeBundle:AbsenceClearing', 'a')
                ->where('a.employee = :employee')
                ->andWhere('a.year = :year')
                ->setParameters(array('employee' => $employee, 'year' => $year))
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
    public function getAbsenceClearingByEmployee($year, Employee $employee)
    {
      return $this->createQueryBuilder('l')
          ->where('l.employee = :employee')
          ->andWhere('l.year = :year')
          ->setParameters(array('employee' => $employee, 'year' => $year))
          ->setMaxResults(1)
          ->getQuery()
          ->getOneOrNullResult();
    }
}
