<?php

namespace VehicleLogBundle\Repository;

/**
 * VehicleLogRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VehicleLogRepository extends \Doctrine\ORM\EntityRepository
{
    public function findLatestByVehicle($vehicle) {
        return $this->createQueryBuilder('l')
                ->andWhere('l.vehicle = ?1 AND l.deleted_at IS NULL')->setParameter(1, $vehicle)
                ->orderBy('l.vehicleLogBeginTime', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
    }

    public function findByMonth($employee_id, $begin, $end, $sort = 'vehicleLogBeginTime', $direction = 'asc') {
        return $this->createQueryBuilder('vl')
                ->andWhere("vl.employee='".$employee_id."' AND vl.vehicleLogBeginTime >= '".$begin."' AND vl.vehicleLogBeginTime < '".$end."' AND vl.deleted_at IS NULL")
                ->orderBy("vl.".$sort, $direction)
                ->getQuery()
                ->getResult();
    }

    public function findByMonthNoEmployee($begin, $end, $sort = 'vehicleLogBeginTime', $direction = 'asc') {
        return $this->createQueryBuilder('vl')
                ->andWhere("vl.vehicleLogBeginTime >= '".$begin."' AND vl.vehicleLogBeginTime < '".$end."' AND vl.deleted_at IS NULL")
                ->orderBy("vl.".$sort, $direction)
                ->getQuery()
                ->getResult();
    }

    public function findByNotFinalizedSince($finalizetime, $sort = 'vehicleLogBeginTime', $direction = 'asc') {parent::findAll();
        return $this->createQueryBuilder('vl')
                ->andWhere('vl.vehicleLogBeginTime <= ?1 AND vl.vehicleLogEndTime IS NULL AND vl.deleted_at IS NULL')->setParameter(1, $finalizetime)
                ->orderBy("vl.".$sort, $direction)
                ->getQuery()
                ->getResult();
    }

    public function checkshoppingvalueBeginAtHome($employee_id, $datatime, $likedate, $sort = 'vehicleLogBeginTime', $direction = 'asc') {
      return  $this->createQueryBuilder('vl')
              ->andWhere("vl.employee='".$employee_id."' AND ( vl.vehicleLogBeginTime like '".$likedate."%' OR vl.vehicleLogBeginTime like '".$likedate."%' ) AND vl.vehicleLogBeginTime <= '".$datatime."' AND vl.reason = 1  AND vl.deleted_at IS NULL AND (vl.expensereason is null OR vl.expensereason = 1) ")
              ->orderBy("vl.".$sort, $direction)
              ->getQuery()
              ->getResult();
  }
  public function checkshoppingvalueEndzuHome($employee_id, $datatime, $likedate, $sort = 'vehicleLogBeginTime', $direction = 'asc') {
      return  $this->createQueryBuilder('vl')
              ->andWhere("vl.employee='".$employee_id."' AND ( vl.vehicleLogBeginTime like '".$likedate."%' OR vl.vehicleLogBeginTime like '".$likedate."%' ) AND vl.vehicleLogBeginTime >= '".$datatime."' AND vl.reason = 1 AND vl.deleted_at IS NULL AND (vl.expensereason is null OR vl.expensereason = 1) ")
              ->orderBy("vl.".$sort, $direction)
              ->getQuery()
              ->getResult();
  }

    /**
     * Finds all entities in the repository.
     *
     * @return array The entities.
     */
    /*public function findAll()
    {
        return $this->createQueryBuilder('vl')
                ->andWhere('vl.deleted_at IS NOT NULL')
                ->getQuery()
                ->getResult();
    }*/
}
