<?php

namespace AppBundle\Repository;

/**
 * LinkListRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LinkListRepository extends \Doctrine\ORM\EntityRepository
{

     public function getallOrderById() {
                return $this->createQueryBuilder('l')
                ->orderBy("l.id", "ASC")
                ->getQuery()
                ->getResult();

          //  ->getOneOrNullResult();
            }

            public function getallOrderByGroup() {
              return $this->createQueryBuilder('l')

              ->orderBy('l.groupname', 'ASC')
              ->orderBy("l.link", "ASC")
              ->getQuery()
              ->getResult();
          //  ->getOneOrNullResult();
          }

          public function getallGroup() {
            return $this->createQueryBuilder('l')
            ->select("l.groupname")
            ->groupBy("l.groupname")
            ->orderBy('l.groupname', 'ASC')
            ->orderBy("l.link", "ASC")
            ->getQuery()
            ->getResult();
        //  ->getOneOrNullResult();
        }

}
