<?php

namespace ExpenseBundle\Repository;

/**
 * WorkdayRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class WorkdayRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByMonth($employee_id, $begin, $end, $sort = 'date', $direction = 'asc') {
        return $this->createQueryBuilder('wd')
                ->andWhere("wd.employee='".$employee_id."' AND wd.date >= '".$begin."' AND wd.date < '".$end."'")
                ->orderBy("wd.".$sort, $direction)
                ->getQuery()
                ->getResult();
    }
    public function findByDateTruck($truck_id, $begin, $end, $sort = 'date', $direction = 'asc') {
        return $this->createQueryBuilder('wd')
                ->andWhere("wd.truck='".$truck_id."' AND wd.date >= '".$begin."' AND wd.date < '".$end."'")
                ->orderBy("wd.".$sort, $direction)
                ->getQuery()
                ->getResult();
    }
    public function findEditedexpenses($begin, $end) {
        return $this->createQueryBuilder('wd')
                ->andWhere("wd.status='3' AND wd.date >= '".$begin."' AND wd.date < '".$end."'")
                ->getQuery()
                ->getResult();
    }
}
