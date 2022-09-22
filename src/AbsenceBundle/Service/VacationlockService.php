<?php

namespace AbsenceBundle\Service;

use AbsenceBundle\Entity\Vacationlock;
use Carbon\Carbon;

class VacationlockService
{
    private $doctrine;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function findAllVacationlocks()
    {
        $vacationlock = $this->doctrine->getRepository('AbsenceBundle:Vacationlock')->findAll();

        $response = array();
        foreach ($vacationlock as $v) {
            $response[$v->getYear()] = $v->getDays();
        }
        return $response;
    }
}
