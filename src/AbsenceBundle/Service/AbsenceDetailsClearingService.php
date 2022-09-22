<?php
namespace AbsenceBundle\Service;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use EmployeeBundle\Entity\AbsenceClearing;
use EmployeeBundle\Entity\Employee;
use AbsenceBundle\Entity\Absence;
use AbsenceBundle\Entity\AbsenceDetailClearing;
use EmployeeBundle\Entity\WorkingHours;


class AbsenceDetailsClearingService
{
    private $doctrine;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function createOrUpdateAbsenceDetailsFromArray($array, $employee, $absence)
    {
          if (/*empty($array) ||*/ empty($employee)) {
              return false;
          }
         // arbeitsTage
         $em = $this->doctrine->getManager();
         $workingtimes = $this->doctrine->getRepository('EmployeeBundle:WorkingHours')->findBy(array('employeeId' => $employee->getId()));
         if(empty($workingtimes))
         {
           $i=1;
           $begin = new  \DateTime('07:00');
           $end = new  \DateTime("16:00");
           while($i< 8)
           {
             $workingtimesRow = new WorkingHours() ;
             $workingtimesRow->setEmployeeId($employee->getId());
             $workingtimesRow->setDayOfWeek($i);
             $workingtimesRow->setworkBegin($begin);
             $workingtimesRow->setWorkEnd($end);
             $workingtimesRow->setOvertime(8);
             $workingtimesRow->setAutoBreak(1);
             $workingtimesRow->setSchool(0);
             $workingtimesRow->setAllowOvertime(0);
             if($i > 5)
             {
               $workingtimesRow->setDeletedAt(new \Datetime());
             }
             $em->persist($workingtimesRow);
             $em->flush();
             $i++;
           }
           $workingtimes = $this->doctrine->getRepository('EmployeeBundle:WorkingHours')->findBy(array('employeeId' => $employee->getId()));
         }
        // arbeitsTage
        if(empty($array))
        {
          $begin = $absence->getFromDate();
          $end = $absence->getToDate();
          while($begin < $end)
          {
            if( empty($workingtimes[$begin->format("N")-1]->getDeletedAt()) and !$em->getRepository('AbsenceBundle:PublicHoliday')->isPublicHoliday($begin->format("Y-m-d"))  )
            $array[$begin->format("Y-m-d")] = "1";
            $begin->modify("+1 day");
          }
        }
        if (empty($array) /* || empty($employee)*/) {
            return false;
        }
        $repo = $this->doctrine->getRepository('AbsenceBundle:AbsenceDetailClearing');
        if ($repo->findBy(array('absence' => $absence))) {
            $absenceDetailClearings = $repo->findBy(array('absence' => $absence));
            foreach ($absenceDetailClearings as $absenceDetailClearing) {
                $em->remove($absenceDetailClearing);
                $em->flush();
            }
        }
          if($absence->getStatus()->getId() != 2)
            {
                foreach ($array as $key => $value) {
                    $absenceDetailClearing = new AbsenceDetailClearing();
                    $absenceDetailClearing->setEmployee($employee);
                    $absenceDetailClearing->setReason($absence->getReason());
                    $absenceDetailClearing->setAbsence($absence);
                    $absenceDetailClearing->setDate(Carbon::parse($key));
                    $absenceDetailClearing->setDayDetail($absenceDetailClearing->transformCheckboxToDayDetail($value));
                    $absenceDetailClearing->setUseAsHolidays($absence->getReason()->getUseAsHolidays());
                    $em->persist($absenceDetailClearing);
                    $em->flush();
                }
            }
    }

    public function removeByAbsence($absence)
    {
        $em = $this->doctrine->getManager();
        $repo = $this->doctrine->getRepository('AbsenceBundle:AbsenceDetailClearing');
        if ($repo->findBy(array('absence' => $absence))) {
            $absenceDetailClearings = $repo->findBy(array('absence' => $absence));
            foreach ($absenceDetailClearings as $absenceDetailClearing) {
                $em->remove($absenceDetailClearing);
                $em->flush();
            }
        }
    }
}
