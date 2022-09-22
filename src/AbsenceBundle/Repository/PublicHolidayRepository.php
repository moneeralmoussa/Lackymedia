<?php

namespace AbsenceBundle\Repository;

/**
 * PublicHolidayRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PublicHolidayRepository extends \Doctrine\ORM\EntityRepository
{
    public function findDaysBetween($start, $end, $state="DE-NW")
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('f.start')
            ->from('AbsenceBundle:PublicHoliday', 'f')
            ->where('f.type = 1')
            ->andWhere('f.state = :state')
            ->andWhere('f.start BETWEEN :start AND :end')
            ->setParameters(array('start' => $start, 'end' => $end, 'state' => $state));
        return $qb->getQuery()->getArrayResult();
    }

    public function isPublicHoliday($date, $state="DE-NW")
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')
            ->from('AbsenceBundle:PublicHoliday', 'f')
            ->where('f.type = 1')
            ->andWhere('f.state = :state')
            ->andWhere(':date BETWEEN f.start AND f.end')
            ->setMaxResults(1)
            ->setParameters(array('date' => $date, 'state' => $state));

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByYear($year)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('p')
            ->from('AbsenceBundle:PublicHoliday', 'p')
            //->where('p.type = 1')
            ->andWhere('YEAR(p.start) = :year')
            ->setParameters(array('year' => $year));

        return $qb->getQuery()->getResult();
    }

    public function getAllYears()
    {
        $query =  "
		SELECT year, count(year) as c
		FROM (SELECT SUBSTRING(start, 1, 4) as year, type
		FROM public_holiday
		GROUP BY year,type) AS x
		GROUP BY year
		HAVING c >= 2;";

        $connection = $this->_em->getConnection();
        $statement = $connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        return array_column($result, 'year');
    }
}