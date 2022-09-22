<?php

namespace VehicleBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * VehicleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VehicleRepository extends EntityRepository
{
    public function findByTrimbleId($trimbleId) {
        return $this->createQueryBuilder('l')
                ->andWhere('l.trimbleId = ?1')->setParameter(1, $trimbleId)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
    }

    public function findAllOrderByVehicletype($direction = 'asc') {
        return $this->createQueryBuilder('v')
                ->leftJoin('v.vehicletype', 'vt')
                ->orderBy('vt.name', $direction)
                ->getQuery()
                ->getResult();
    }

    public function findAllByVehicletypetype($vehicletypetype = [6], $direction = 'asc') {
        return $this->createQueryBuilder('v')
                ->leftJoin('v.vehicletype', 'vt')
                ->andWhere("vt.vehicletypetype IN ('".implode("', '",$vehicletypetype)."')")
                ->orderBy('v.name', $direction)
                ->getQuery()
                ->getResult();
    }

    public function getAllSoftDeleted()
	{
		$delete_date=(new \DateTime())->format('Y-m-d H:i:s');
		return $this->getEntityManager()
			->createQuery(
				"SELECT e FROM VehicleBundle:Vehicle e WHERE e.deleted_at IS NOT NULL AND e.deleted_at < '".$delete_date."'")
			->getResult();
	}
}