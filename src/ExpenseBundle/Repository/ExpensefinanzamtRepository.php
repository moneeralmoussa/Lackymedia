<?php

namespace ExpenseBundle\Repository;

/**
 * ExpensefinanzamtRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ExpensefinanzamtRepository extends \Doctrine\ORM\EntityRepository
{
    public function findstatus($employee_id,$date)
    {
        $now=(new \DateTime())->format('Y-m-d');
        return $this->getEntityManager()
            ->createQuery(
                "SELECT e FROM ExpenseBundle:Expensefinanzamt e WHERE e.employeeId = '".$employee_id."' AND e.date = '".$date."'"
            )
            ->getOneOrNullResult();
    }
    public function findAllByEmployeeId($employee_id,$date)
    {
        
        $frYear=(new \DateTime())->modify('-13 month')->format('Y-m-d');
        return $this->getEntityManager()
            ->createQuery(
                "SELECT e FROM ExpenseBundle:Expensefinanzamt e WHERE e.employeeId = '".$employee_id."' AND e.date like '".$date."%' order by e.date" 
            )
            ->getResult();
    }

}