<?php

namespace EmployeeBundle\Controller;

use Carbon\Carbon;
use EmployeeBundle\Entity\Employee;
use EmployeeBundle\Entity\Department;
use EmployeeBundle\Entity\EmployeeArchiv;
use EmployeeBundle\Entity\EmployeeImport;
use EmployeeBundle\Entity\Salary;
use EmployeeBundle\Entity\Employeeworkday;
use EmployeeBundle\Entity\AbsenceClearing;
use EmployeeBundle\Entity\Employeeposition;
use EmployeeBundle\Entity\WorkingHours;
use EmployeeBundle\Entity\Breaktime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EmployeeBundle\Entity\User;
use LocationBundle\Entity\Location;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * User controller.
 *
 */
class EmployeeworkdayController extends Controller
{
    public function mycoderandomAction(Request $request) //use
    {
        $em = $this->getDoctrine()->getManager();
        $employee  =   $em->getRepository('EmployeeBundle:Employeeworkday')->checksession($this->getUser()->getEmployee()->getId());
        $random = random_int(1,9999);
        while ($random < 1000) $random = random_int(1,9999);
            $connection = $em->getConnection();
            $statement = $connection->prepare("
            INSERT INTO ramdomauth (id,random, employee_id,webbrowser,ip ,create_at) VALUES (NULL,'".$random."', '".$this->getUser()->getEmployee()->getId()."','".$_SERVER['HTTP_USER_AGENT']."','".$this->container->get('request')->getClientIp()."', current_timestamp());
                ");
            $statement->execute();
            //,['date' => $employee[0]->getBeginEmployeepositionDate()]
                        if(empty($employee))
                        return new JsonResponse([
                            'code' 		=> $random,
                            'date'      => '',
                        ]);
           return new JsonResponse([
            'code' 		=> $random,
            'date' 	    	=> $employee->getBeginEmployeepositionDate(),
        ]);
    }
    public function servercodesessionbeginAction(Request $request,$id)  // use
    {
       $em = $this->getDoctrine()->getManager();
       if(substr($id,0, 3) == 'EM_')
       {  // GET TRIMBLE
            $employee  =   $em->getRepository('EmployeeBundle:Employee')->findByTrimbleId(substr($id,3));
             if(empty($employee )) die();
            $connection = $em->getConnection();
            $statement = $connection->prepare("
            INSERT INTO ramdomauth (id,random, employee_id,webbrowser,ip ,create_at) VALUES (NULL,'0000', '".$employee->getId()."','PRIVATE CODE','PRIVATE CODE', current_timestamp());
                ");
            $statement->execute();
            $id= 0000;
       }
        $connection = $em->getConnection();
        $statement = $connection->prepare("
        select * from ramdomauth where random = ". $id ." ORDER BY id DESC LIMIT 1 ;
             ");
        $statement->execute();
        $row = $statement->fetchAll();
        if(empty($row)) {die();}

        $rowdate=  new \DateTime($row[0]['create_at']);
        $s= $rowdate->diff(new \DateTime())->format('%s');
          if($s > 18) {return new JsonResponse(false);}
        $i= $rowdate->diff(new \DateTime())->format('%i');
          if($i != 0 ){return new JsonResponse(false);}
        $h= $rowdate->diff(new \DateTime())->format('%h');
          if($h != 0){return new JsonResponse(false);}

        $employee = $em->getRepository('EmployeeBundle:Employee')->find($row[0]['employee_id']);
        $employeeworkday = $em->getRepository('EmployeeBundle:Employeeworkday')->checksession($row[0]['employee_id']);
        $position = $em->getRepository('EmployeeBundle:Employeeposition')->find(1);
        if(empty($employeeworkday))
        {
        $employee_code = new Employeeworkday();
        $employee_code->setEmployeeId($row[0]['employee_id']);
        $employee_code->setBeginEmployeepositionId($position);
        $employee_code->setBeginEmployeepositionDate(new \DateTime());
        $employee_code->setBeginLoginTypeId('1');
        $employee_code->setBeginEditByEmployeeId($this->getUser()->getEmployee()->getId());
        $employee_code->setStatusBeginId('1');
        $employee_code->setBeginWebbrowser($row[0]['webbrowser']);
        $employee_code->setBeginIp($row[0]['ip']);
        $employee_code->setCreateAt(new \DateTime());
        $em->persist($employee_code);
        $em->flush();
        $status = 'sessionStart';
        }
        else if(empty($employeeworkday->getEndEmployeepositionDate()))
        {
            //check break end = null
            $timeNow = new \Datetime();
            $check =$employeeworkday->getBeginEmployeepositionDate();
            // check break->end !=null //
            $break = $em->getRepository('EmployeeBundle:Breaktime')->checkBreak($employeeworkday->getId());

            if(!empty($break))
            {
                $break->setEnd(new \Datetime());
                $em->persist($break);
                $em->flush();
            }
            if( round(($timeNow->getTimestamp() - $check->getTimestamp()) / 3600, 2) > 0.03 )
            {
                    $i= $employeeworkday->getBeginEmployeepositionDate()->diff(new \DateTime())->format('%i');
                    $h= $employeeworkday->getBeginEmployeepositionDate()->diff(new \DateTime())->format('%h');
                    $d= $employeeworkday->getBeginEmployeepositionDate()->diff(new \DateTime())->format('%d');
                    $sum= ($d * 24 * 60) + ($h * 60 ) + $i;

                    $employeeworkday->setEndEmployeepositionId($position);
                    $employeeworkday->setEndEmployeepositionDate(new \DateTime());
                    $employeeworkday->setEndLoginTypeId('1');
                    $employeeworkday->setEndEditByEmployeeId($this->getUser()->getEmployee()->getId());
                    $employeeworkday->setStatusEndId('1');
                    $employeeworkday->setSum($sum);
                    $employeeworkday->setEndWebbrowser($row[0]['webbrowser']);
                    $employeeworkday->setEndIp($row[0]['ip']);
                    $em->persist($employeeworkday);
                    $em->flush();
                    $status = 'sessionEnd';
            }
            else{
            $status = 'sessionStart';
                }
        }

        else  if(!empty($employeeworkday->getEndEmployeepositionDate()))
        {
            $timeNow = new \Datetime();
            $check =$employeeworkday->getEndEmployeepositionDate();
            if( round(($timeNow->getTimestamp() - $check->getTimestamp()) / 3600, 2) > 0.03 )
            {
                    $break = new Breaktime();
                    $break->setEmployeeworkdayId($employeeworkday);
                    $break->setBegin($employeeworkday->getEndEmployeepositionDate());
                    $break->setEnd(new \DateTime());
                    $break->setCreateAt(new \DateTime());
                    $em->persist($break);
                    $em->flush();

                $employeeworkday->setEndEmployeepositionId(null);
                $employeeworkday->setEndEmployeepositionDate(null);
                $employeeworkday->setEndLoginTypeId(null);
                $employeeworkday->setEndEditByEmployeeId($this->getUser()->getEmployee()->getId());
                $employeeworkday->setStatusEndId(null);

                $em->persist($employeeworkday);
                $em->flush();
                $status = 'sessionStart';
            }
            else{
                $status = 'sessionEnd';
            }

        }
        return new JsonResponse([
            'status'            => $status,
            'employee' 	    	=> $employee->getFName(),
        ]);
    }


    public function createBreaktimeAction()  //use
    {
        $em = $this->getDoctrine()->getManager();
        $employeeworkday = $em->getRepository('EmployeeBundle:Employeeworkday')->checksession($this->getUser()->getEmployee()->getId());
        if(empty($employeeworkday) or $employeeworkday->getEndEmployeepositionDate() != null or $employeeworkday->getEndEmployeepositionDate() != '' )
        return new JsonResponse([
            'status'            => 'False Data', ]);
        $break = $em->getRepository('EmployeeBundle:Breaktime')->checkBreak($employeeworkday->getId());
        if($break == null)
        {
        $break = new Breaktime();
        $break->setEmployeeworkdayId($employeeworkday);
        $break->setBegin(new \DateTime());
        $break->setCreateAt(new \DateTime());
        $em->persist($break);
        $em->flush();
        $status = 'begin';}
        else{
        $break->setEnd(new \DateTime());
        $em->persist($break);
        $em->flush();$status = 'end';}

        return $this->redirectToRoute('app');
    }

    public function  createAction(Request $request)  // use
    {
       $em = $this->getDoctrine()->getManager();
       $employee = $em->getRepository('EmployeeBundle:Employee')->find($this->getUser()->getEmployee()->getId());
       $employeeworkday = $em->getRepository('EmployeeBundle:Employeeworkday')->checksession($this->getUser()->getEmployee()->getId());
       //new
       $connection = $em->getConnection();
       $position = new Employeeposition;
       $position->setName('ohne Pin');
       $position->setStreet(' ');
       $position->setCreateAt(new \DateTime());
       $em->persist($position);
       $em->flush();
       if(empty($employeeworkday))
       {
           $employee_code = new Employeeworkday();
           $employee_code->setEmployeeId($this->getUser()->getEmployee()->getId());
           $employee_code->setBeginEmployeepositionId($position);
           $employee_code->setBeginEmployeepositionDate(new \DateTime());
           $employee_code->setBeginLoginTypeId('2');
           $employee_code->setBeginEditByEmployeeId($this->getUser()->getEmployee()->getId());
           $employee_code->setStatusBeginId('3');
           $employee_code->setBeginWebbrowser($_SERVER['HTTP_USER_AGENT']);
           $employee_code->setBeginIp($this->container->get('request')->getClientIp());
           $employee_code->setCreateAt(new \DateTime());
           $em->persist($employee_code);
           $em->flush();
           $status = 'sessionStart';
       }
       else if(empty($employeeworkday->getEndEmployeepositionDate()))
       {
           //check break end = null
           $break = $em->getRepository('EmployeeBundle:Breaktime')->checkBreak($employeeworkday->getId());
           if(!empty($break) && empty($break->getEnd())) $break->setEnd(new \DateTime());

           $i= $employeeworkday->getBeginEmployeepositionDate()->diff(new \DateTime())->format('%i');
           $h= $employeeworkday->getBeginEmployeepositionDate()->diff(new \DateTime())->format('%h');
           $d= $employeeworkday->getBeginEmployeepositionDate()->diff(new \DateTime())->format('%d');
           $sum= ($d * 24 * 60) + ($h * 60 ) + $i;

           $employeeworkday->setEndEmployeepositionId($position);
           if (new \DateTime() < new \DateTime()){
           $breaks = $em->getRepository('EmployeeBundle:Breaktime')->findByEmployeeWorkingday($employeeworkday->getId());
           foreach ($breaks as $break)
               {
                   if($break->getBegin() > new \DateTime() or ($break->getEnd() > new \DateTime()))
                   {
                       $break->setDeletedAt((new \DateTime()));
                       $em->persist($break);
                       $em->flush();
                   }
               }
                   $employeeworkday->setEndEmployeepositionDate(new \DateTime());
           }
           $employeeworkday->setEndLoginTypeId('2');
           $employeeworkday->setEndEditByEmployeeId($this->getUser()->getEmployee()->getId());
           $employeeworkday->setStatusEndId('3');
           $employeeworkday->setSum($sum);
           $employeeworkday->setEndWebbrowser($_SERVER['HTTP_USER_AGENT']);
           $employeeworkday->setEndIp($this->container->get('request')->getClientIp());
           $em->persist($employeeworkday);
           $em->flush();
           $status = 'sessionEnd';
       }
       else  if(!empty($employeeworkday->getEndEmployeepositionDate()))
       {
           $break = new Breaktime();
           $break->setEmployeeworkdayId($employeeworkday);
           $break->setBegin($employeeworkday->getEndEmployeepositionDate());
           $break->setEnd(new \DateTime());
           $break->setCreateAt(new \DateTime());
           $em->persist($break);
           $em->flush();
           $employeeworkday->setEndEmployeepositionId(null);
           $employeeworkday->setEndEmployeepositionDate(null);
           $employeeworkday->setEndLoginTypeId(null);
           $employeeworkday->setEndEditByEmployeeId($this->getUser()->getEmployee()->getId());
           $employeeworkday->setStatusEndId(null);
           $em->persist($employeeworkday);
           $em->flush();
           $status = 'editsession';
       }
       return $this->redirectToRoute('app');
    }

    public function indexAction($date) // use
    {

        $em = $this->getDoctrine()->getManager();
        $employees = $em->getRepository('EmployeeBundle:Employee')->getAllAvailable();
        foreach ( $employees  As $employee ) {
        $employeeworkday = $em->getRepository('EmployeeBundle:Employeeworkday')->checksessiondate($employee->getId(),$date);
        if(!empty($employeeworkday))
                {
                    $employee->workingdayId = $employeeworkday->getId();
                    $employee->beginEmployeepositionName = $employeeworkday->getBeginEmployeepositionId()->getName();
                    if(!empty($employeeworkday->getEndEmployeepositionId()))
                    $employee->endEmployeepositionName  = $employeeworkday->getEndEmployeepositionId()->getName();
                    else  $employee->endEmployeepositionName  = null;
                    $employee->beginEmployeepositionDate  = $employeeworkday->getBeginEmployeepositionDate();
                    $employee->endEmployeepositionDate = $employeeworkday->getEndEmployeepositionDate();
                    $employee->statusBeginId = $employeeworkday->getStatusBeginId();
                    $employee->beginLoginTypeId = $employeeworkday->getBeginLoginTypeId();
                    $employee->endLoginTypeId = $employeeworkday->getEndLoginTypeId();
                    $employee->statusEndId = $employeeworkday->getStatusEndId();
                    $employee->sum = $employeeworkday->getSum();
                }
                else {
                    $employee->workingdayId = null;
                    $employee->beginEmployeepositionName = null;
                    $employee->endEmployeepositionName  = null;
                    $employee->beginEmployeepositionDate  = null;
                    $employee->endEmployeepositionDate = null;
                    $employee->beginLoginTypeId = null;
                    $employee->endLoginTypeId = null;
                    $employee->statusBeginId = null;
                    $employee->statusEndId = null;
                    $employee->sum = null;
                }
        }
        $date= new \Datetime($date);
        return $this->render('EmployeeBundle:Employeeworkday:index.html.twig', array(
            'workdays' => $employees,
            'Date' => $date,

        ));
    }

    public function editBeginStatusAction($id , $status)  // use
    {
        $em = $this->getDoctrine()->getManager();
        $employeeworkday = $em->getRepository('EmployeeBundle:Employeeworkday')->find($id);

        if(!empty($employeeworkday))
        {
            $employeeworkday->setStatusBeginId($status);
            $employeeworkday->setBeginEditByEmployeeId($this->getUser()->getEmployee()->getId());
            $em->persist($employeeworkday);
            $em->flush();
            $status  = 'true';
        }
        else { $status = 'false';}
        return new JsonResponse([
            'status'            => $status,
        ]);
    }
    public function editEndStatusAction($id , $status)   // use
    {
        $em = $this->getDoctrine()->getManager();
        $employeeworkday = $em->getRepository('EmployeeBundle:Employeeworkday')->find($id);

        if(!empty($employeeworkday))
        {
            $employeeworkday->setStatusEndId($status);
            $employeeworkday->setEndEditByEmployeeId($this->getUser()->getEmployee()->getId());
            $em->persist($employeeworkday);
            $em->flush();
            $status  = 'true';
        }
        else { $status = 'false';}
        return new JsonResponse([
            'status'            => $status,
        ]);
    }
    public function findEmployeeworkday($id ,  $date)
    {
        $em = $this->getDoctrine()->getManager();
        $employeeworkingday = $em->getRepository('EmployeeBundle:Employeeworkday')->findbyMonth($id, $date);
        if(empty($employeeworkingday))
        {
         $employeeworkingday = new Employeeworkday();
         $workday = $em->getRepository('EmployeeBundle:Employeeworkday')->findbyMonthWithOutEnd($id, $date);
         $employeeworkingday->endstatus = (empty($workday))?0:1;
          if($employeeworkingday->endstatus == 1 ){
             $employeeworkingday->begin=($workday->getBeginEmployeepositionDate());
             $employeeworkingday->begintype=($workday->getBeginLoginTypeId());
             $employeeworkingday->beginstatus=($workday->getStatusBeginId());
          }
          $employeeworkingday->datum = $date;
          $krank = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,8);
          $urlaub = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,2);
          $Schule = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findSchool($id,$date);
          $Holiday = $em->getRepository('AbsenceBundle:PublicHoliday')->isPublicHoliday($date);
          if(!empty($krank))$employeeworkingday->krank = '8:0';else $employeeworkingday->krank = '';
          if(!empty($urlaub))if($urlaub->getDayDetail()== '0.5'){$employeeworkingday->urlaub = '4.0';} else{$employeeworkingday->urlaub = '8.0';} else $employeeworkingday->urlaub = '';
          if(!empty($Schule)) $employeeworkingday->schule = '8:0';else $employeeworkingday->schule = '';
          if(!empty($Holiday))$employeeworkingday->holiday = $Holiday->getTitle();else  $employeeworkingday->holiday = '';
           $employeeworkingday->sum_workingtime = 0;
           $employeeworkingday->sum_breaktime = 0 ;
          return $employeeworkingday;
        }
        else{
            $krank = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,8);
            $urlaub = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,2);
            if(!empty($krank))$employeeworkingday->krank = '8.0';else $employeeworkingday->krank = '';
            if(!empty($urlaub))if($urlaub->getDayDetail()== '0.5'){$employeeworkingday->urlaub = '4.0';} else{$employeeworkingday->urlaub = '8.0';} else $employeeworkingday->urlaub = '';
            $breaks = $em->getRepository('EmployeeBundle:Breaktime')->findByEmployeeWorkingday($employeeworkingday->getId());
            if (!empty($breaks))
                    {$workingtime = [];
                    $begin =  $employeeworkingday->getBeginEmployeepositionDate();
                    $end = $employeeworkingday->getEndEmployeepositionDate();
                    $i=0;$h=0;
                        for ($index=0 ; $index<= count($breaks);$index++)
                        {
                            if($index == 0)
                            {
                                $workingtime[$index] = [
                                    "begin" => $begin,
                                    "end" => $breaks[$index]->getBegin(),
                                ];
                            }
                            else if($index == count($breaks))
                            {
                                $workingtime[$index] = [
                                    "begin" => $breaks[$index-1]->getEnd(),
                                    "end" => $end,
                                ];
                            }
                            else
                            {
                                $workingtime[$index] = [
                                    "begin" => $breaks[$index-1]->getEnd(),
                                    "end" => $breaks[$index]->getBegin(),
                                ];
                            }
                        }
                    }
                   else if (empty($breaks)){
                        $begin =  $employeeworkingday->getBeginEmployeepositionDate();
                        $end = $employeeworkingday->getEndEmployeepositionDate();
                        $workingtime[0] = [
                            "begin" => $begin,
                            "end" => $end,
                        ];
                    }
                    $sum_workingtime =0;
                    foreach ($breaks as $break)
                    {
                        $sum_workingtime -= round(($break->getEnd()->getTimestamp() - $break->getBegin()->getTimestamp()) / 3600, 2);
                    }
                    $employeeworkingday->sum_breaktime  = $sum_workingtime ;
                    $sum_workingtime += round(($employeeworkingday->getEndEmployeepositionDate()->getTimestamp() - $employeeworkingday->getBeginEmployeepositionDate()->getTimestamp()) / 3600, 2);
                    $employeeworkingday->datum = $date;
                    $employeeworkingday->arrayworkingtimes = $workingtime;
                    $employeeworkingday->sum_workingtime = $sum_workingtime;
                }
            return $employeeworkingday;
    }
    public function employeeByMonthAction($id ,$date )   // use
    {
          $em = $this->getDoctrine()->getManager();
          $employee = $em->getRepository('EmployeeBundle:Employee')->find($id);
          $workingtimes = $this->getDoctrine()->getRepository('EmployeeBundle:WorkingHours')->findBy(array('employeeId' => $employee->getId()));
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
              $workingtimesRow->setAutoBreak(1);
              $workingtimesRow->setSchool(0);
              $workingtimesRow->setAllowOvertime(0);
              $workingtimesRow->setOvertime(8);
              if($i > 5)
              {
                $workingtimesRow->setDeletedAt(new \Datetime());
              }
              $em->persist($workingtimesRow);
              $em->flush();
              $i++;
            }
          }

            $WorkingHours = $this->getDoctrine()->getRepository('EmployeeBundle:WorkingHours')->findBy(array('employeeId' => $employee->getId()));
            date_default_timezone_set("Europe/Berlin");
            $Date=new \DateTime($date);
            $HoursOfWork=0.0;
            $employeeworkingdays=[];
            for($i=1 ;$i<=$Date->format('t');$i++)
            {
                $Day = $Date->format('Y-m') . '-' . $i;
                $DayDatetime=clone(new \DateTime($Day));

                $employeeworkingday=  $this->findEmployeeworkdayPrint($id,$Day,$WorkingHours);
                $HoursOfWork=($WorkingHours[$DayDatetime->format('N')-1]->getOvertime());
                $employeeworkingday->HoursOfWork = $HoursOfWork;
                $employeeworkingday->overtimePremium=0;
                if(!empty($employeeworkingday->kuz))
                {
                  $employeeworkingday->kuz =$WorkingHours[(new \Datetime($employeeworkingday->datum))->format('N')-1]->getOvertime() * $employeeworkingday->kuz;
                  $employeeworkingday->HoursOfWork =  $employeeworkingday->HoursOfWork -   $employeeworkingday->kuz  ;
                }
                if(!empty($employeeworkingday->ausgleichBereitschaft ))
                {
                  $employeeworkingday->ausgleichBereitschaft =$WorkingHours[(new \Datetime($employeeworkingday->datum))->format('N')-1]->getOvertime() * $employeeworkingday->ausgleichBereitschaft;
                  $employeeworkingday->HoursOfWork =  $employeeworkingday->HoursOfWork -   $employeeworkingday->ausgleichBereitschaft  ;
                }
                if(!empty($employeeworkingday->uberstundenausgleich))
                {
                  $employeeworkingday->uberstundenausgleich =$WorkingHours[(new \Datetime($employeeworkingday->datum))->format('N')-1]->getOvertime() * $employeeworkingday->uberstundenausgleich;
                  $employeeworkingday->HoursOfWork =  $employeeworkingday->HoursOfWork -   $employeeworkingday->uberstundenausgleich  ;
                }
                if(!empty($employeeworkingday->urlaub))
                {
                  $employeeworkingday->urlaub =$WorkingHours[(new \Datetime($employeeworkingday->datum))->format('N')-1]->getOvertime() * $employeeworkingday->urlaub;
                  $employeeworkingday->HoursOfWork =  $employeeworkingday->HoursOfWork -   $employeeworkingday->urlaub  ;
                }
                if($WorkingHours[$DayDatetime->format('N')-1]->getAllowOvertime() == 1 AND $DayDatetime < (new \Datetime()) )
                {
                  if( $employeeworkingday->sum_workingtime > $employeeworkingday->HoursOfWork  )
                  {
                    $employeeworkingday->overtimePremium= $employeeworkingday->sum_workingtime -  $employeeworkingday->HoursOfWork  ;
                  }
                  else
                  {
                      $employeeworkingday->overtimePremium= $employeeworkingday->sum_workingtime -  $employeeworkingday->HoursOfWork  ;
                  }
                }
                $employeeworkingdays[] =$employeeworkingday;
            }
            $employees = $em->getRepository('EmployeeBundle:Employee')->getAllAvailable();
            return $this->render('EmployeeBundle:Employeeworkday:indexEmployeeByMonth.html.twig', array(
                'workingtimes' => $employeeworkingdays,
                'employee' =>  $employee,
                'Date' =>  $Date,
                'employees' => $employees,
                'workingHours' => $WorkingHours,
            ));
    }

    public function findEmployeeworkdayPrint($id ,  $date, $WorkingHours)
{
    $em = $this->getDoctrine()->getManager();
    $sum_workingtime=0;
    $employeeworkingday = $em->getRepository('EmployeeBundle:Employeeworkday')->findbyMonth($id, $date);
    if(empty($employeeworkingday))
    {
     $employeeworkingday = new Employeeworkday();
     $workday = $em->getRepository('EmployeeBundle:Employeeworkday')->findbyMonthWithOutEnd($id, $date);
     $employeeworkingday->endstatus = (empty($workday))?0:1;
      if($employeeworkingday->endstatus == 1 )
      {
         $employeeworkingday->beginist=clone($workday->getBeginEmployeepositionDate());
         $begin=clone($workday->getBeginEmployeepositionDate());
         $beginist =  clone($workday->getBeginEmployeepositionDate());
         if ($begin->format('i')< 7)
         {
             $beginist->setTime( $beginist->format("H"), 0, 0 );
         }
         elseif($begin->format('i')< 22)
         {
             $beginist->setTime($beginist->format("H"), 15, 0 );
         }
         elseif($begin->format('i')< 37)
         {
             $beginist->setTime($beginist->format("H"), 30, 0 );
         }
         elseif($begin->format('i')< 53)
         {
             $beginist->setTime($beginist->format("H"), 45, 0 );
         }
         else{
             $beginist->setTime($beginist->format("H")+1, 0, 0 );
         }
         $employeeworkingday->begin=$beginist;
         $employeeworkingday->begintype=($workday->getBeginLoginTypeId());
         $employeeworkingday->beginstatus=($workday->getStatusBeginId());
      }
      $employeeworkingday->datum = $date;
      $krank = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,8);
      $urlaub = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,2);
      $Schule = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findSchool($id,$date);
      $Holiday = $em->getRepository('AbsenceBundle:PublicHoliday')->isPublicHoliday($date);
      $kuz = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,52);
      $ausgleichBereitschaft  = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,31);
      $uberstundenausgleich  = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,33);

      if(!empty($krank))$employeeworkingday->krank = '8:0';else $employeeworkingday->krank = '';
      if(!empty($urlaub))if($urlaub->getDayDetail()== '0.5'){$employeeworkingday->urlaub = '0.5';} else{$employeeworkingday->urlaub = '1.0';} else $employeeworkingday->urlaub = '';
      if(!empty($kuz))if($kuz->getDayDetail()== '0.5'){$employeeworkingday->kuz = '0.5';} else{$employeeworkingday->kuz = '1.0';} else $employeeworkingday->kuz = '';
      if(!empty($ausgleichBereitschaft))if($ausgleichBereitschaft->getDayDetail()== '0.5'){$employeeworkingday->ausgleichBereitschaft = '0.5';} else{$employeeworkingday->ausgleichBereitschaft = '1.0';} else $employeeworkingday->ausgleichBereitschaft = '';
      if(!empty($uberstundenausgleich))if($uberstundenausgleich->getDayDetail()== '0.5'){$employeeworkingday->uberstundenausgleich = '0.5';} else{$employeeworkingday->uberstundenausgleich = '1.0';} else $employeeworkingday->uberstundenausgleich = '';
      if($WorkingHours[(new \Datetime($date))->format('N')-1]->getSchool() =='1' ||!empty($Schule))
       {$employeeworkingday->schule = '8:0';}
       else {$employeeworkingday->schule = '';}
      if(!empty($Holiday))$employeeworkingday->holiday = $Holiday->getTitle();else  $employeeworkingday->holiday = '';
       $employeeworkingday->sum_workingtime = 0;
       $employeeworkingday->sum_breaktime = 0 ;
      return $employeeworkingday;
    }
    else{
        $begin =  $employeeworkingday->getBeginEmployeepositionDate();
        $beginist =  clone($employeeworkingday->getBeginEmployeepositionDate());
        if ($begin->format('i')< 7)
        {
            $begin->setTime( $begin->format("H"), 0, 0 );
        }
        elseif($begin->format('i')< 22)
        {
            $begin->setTime($begin->format("H"), 15, 0 );
        }
        elseif($begin->format('i')< 37)
        {
            $begin->setTime($begin->format("H"), 30, 0 );
        }
        elseif($begin->format('i')< 53)
        {
            $begin->setTime($begin->format("H"), 45, 0 );
        }
        else{
            $begin->setTime($begin->format("H")+1, 0, 0 );
        }
$employeeworkingday->setBeginEmployeepositionDate($begin);
$end = $employeeworkingday->getEndEmployeepositionDate();
$endist = clone($employeeworkingday->getEndEmployeepositionDate());
        if ($end->format('i')< 7)
        {
            $end->setTime( $end->format("H"), 0, 0 );
        }
        elseif($end->format('i')< 22)
        {
            $end->setTime($end->format("H"), 15, 0 );
        }
        elseif($end->format('i')< 37)
        {
            $end->setTime($end->format("H"), 30, 0 );
        }
        elseif($end->format('i')< 53)
        {
            $end->setTime($end->format("H"), 45, 0 );
        }
        else{
            $end->setTime($end->format("H")+1, 0, 0 );
        }
$employeeworkingday->setEndEmployeepositionDate($end);
        $krank = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,8);
        $urlaub = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,2);
        $kus = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,52);
        $ausgleichBereitschaft  = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,31);
        $uberstundenausgleich  = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByReason($id,$date,33);
        if(!empty($krank))$employeeworkingday->krank = '8.0';else $employeeworkingday->krank = '';
        if(!empty($urlaub))if($urlaub->getDayDetail()== '0.5'){$employeeworkingday->urlaub = '0.5';} else{$employeeworkingday->urlaub = '1.0';} else $employeeworkingday->urlaub = '';
        if(!empty($kuz))if($kuz->getDayDetail()== '0.5'){$employeeworkingday->kuz = '0.5';} else{$employeeworkingday->kuz = '1.0';} else $employeeworkingday->kuz = '';
        if(!empty($ausgleichBereitschaft))if($ausgleichBereitschaft->getDayDetail()== '0.5'){$employeeworkingday->ausgleichBereitschaft = '0.5';} else{$employeeworkingday->ausgleichBereitschaft = '1.0';} else $employeeworkingday->ausgleichBereitschaft = '';
        if(!empty($uberstundenausgleich))if($uberstundenausgleich->getDayDetail()== '0.5'){$employeeworkingday->uberstundenausgleich = '0.5';} else{$employeeworkingday->uberstundenausgleich = '1.0';} else $employeeworkingday->uberstundenausgleich = '';
        $breaks = $em->getRepository('EmployeeBundle:Breaktime')->findByEmployeeWorkingday($employeeworkingday->getId());
        if (!empty($breaks))
        {   $workingtime = [];
            $begin =  $employeeworkingday->getBeginEmployeepositionDate();
            $end = $employeeworkingday->getEndEmployeepositionDate();
            $i=0;$h=0;
                for ($index=0 ; $index<= count($breaks);$index++)
                {
                    if($index == 0)
                    {
                        $workingtime[$index] = [
                            "begin" => $begin,
                            "end" => $breaks[$index]->getBegin(),
                        ];
                    }
                    else if($index == count($breaks))
                    {
                        $workingtime[$index] = [
                            "begin" => $breaks[$index-1]->getEnd(),
                            "end" => $end,
                        ];
                    }
                    else
                    {
                        $workingtime[$index] = [
                            "begin" => $breaks[$index-1]->getEnd(),
                            "end" => $breaks[$index]->getBegin(),
                        ];
                    }
                }

                $sum_workingtime = 0 ;
                foreach ($breaks as $break)
                {
                    $sum_workingtime -= round(($break->getEnd()->getTimestamp() - $break->getBegin()->getTimestamp()) / 3600, 2);
                }
                $employeeworkingday->sum_breaktime  = $sum_workingtime ;
                $sum_workingtime += round(($employeeworkingday->getEndEmployeepositionDate()->getTimestamp() - $employeeworkingday->getBeginEmployeepositionDate()->getTimestamp()) / 3600, 2);
            }
               else if (empty($breaks)){
                    $begin =  $employeeworkingday->getBeginEmployeepositionDate();
                    $end = $employeeworkingday->getEndEmployeepositionDate();
                    //ANA HOUN 3m shof esa fi l pause
                    if($WorkingHours[$begin->format("N")-1]->getAutoBreak() == 1 and !empty($WorkingHours[$begin->format("N")-1]->getBreakBegin()) AND !empty($WorkingHours[$begin->format("N")-1]->getBreakEnd()) )
                    {
                      $breakBegin = clone($begin);
                      $breakEnd = clone($begin);
                      $breakBegin->setTime($WorkingHours[$begin->format("N")-1]->getBreakBegin()->format('H'), $WorkingHours[$begin->format("N")-1]->getBreakBegin()->format('i'), 0 );
                      $breakEnd->setTime($WorkingHours[$begin->format("N")-1]->getBreakEnd()->format('H'), $WorkingHours[$begin->format("N")-1]->getBreakEnd()->format('i'), 0 );

                      if($begin < $breakBegin and $end > $breakEnd )
                      {
                          $workingtime[0] = [
                              "begin" => $begin,
                              "end" => $breakBegin,
                          ];
                          $workingtime[1] = [
                              "begin" => $breakEnd,
                              "end" => $end,
                          ];
                        $sum_workingtime = round(($end->getTimestamp() - $begin->getTimestamp()) / 3600, 2)-round(($breakEnd->getTimestamp() - $breakBegin->getTimestamp()) / 3600, 2);
                        $employeeworkingday->sum_breaktime =-1* round(($breakEnd->getTimestamp() - $breakBegin->getTimestamp()) / 3600, 2);

                      }
                      else{
                        $workingtime[0] = [
                            "begin" => $begin,
                            "end" => $end,
                        ];
                        $sum_workingtime = ($begin<$end)?(round(($end->getTimestamp() - $begin->getTimestamp()) / 3600, 2)): 0;
                        $employeeworkingday->sum_breaktime = 0;
                      }
                      /*KOOOOOOOOOOOOOOOOOOOD*/
                    }
                    else{
                      $workingtime[0] = [
                          "begin" => $begin,
                          "end" => $end,
                      ];
                      $sum_workingtime = ($begin < $end)?round(($employeeworkingday->getEndEmployeepositionDate()->getTimestamp() - $employeeworkingday->getBeginEmployeepositionDate()->getTimestamp()) / 3600, 2):0;
                      $employeeworkingday->sum_breaktime = 0;
                    }
                }




                //ist
                if (!empty($breaks))
                {   $workingtimeist = [];
                    $i=0;$h=0;
                        for ($index=0 ; $index<= count($breaks);$index++)
                        {
                            if($index == 0)
                            {
                                $workingtimeist[$index] = [
                                    "begin" => $beginist,
                                    "end" => $breaks[$index]->getBegin(),
                                ];
                            }
                            else if($index == count($breaks))
                            {
                                $workingtimeist[$index] = [
                                    "begin" => $breaks[$index-1]->getEnd(),
                                    "end" => $endist,
                                ];
                            }
                            else
                            {
                                $workingtimeist[$index] = [
                                    "begin" => $breaks[$index-1]->getEnd(),
                                    "end" => $breaks[$index]->getBegin(),
                                ];
                            }
                        }

                    }
                       else if (empty($breaks)){
                                $workingtimeist[0] = [
                                    "begin" => $beginist,
                                    "end" => $endist,
                                ];
                        }
                ////////////ist
                $employeeworkingday->datum = $date;
                $employeeworkingday->arrayworkingtimes = $workingtime;
                $employeeworkingday->arrayworkingtimesist = $workingtimeist;
                $employeeworkingday->sum_workingtime = $sum_workingtime;

            }
        return $employeeworkingday;
}

public function employeeByMonthPrintAction($id ,$date )   // use
  {
    $em = $this->getDoctrine()->getManager();
    $employee = $em->getRepository('EmployeeBundle:Employee')->find($id);
    $workingtimes = $this->getDoctrine()->getRepository('EmployeeBundle:WorkingHours')->findBy(array('employeeId' => $employee->getId()));
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
        $workingtimesRow->setAutoBreak(1);
        $workingtimesRow->setSchool(0);
        $workingtimesRow->setAllowOvertime(0);
        $workingtimesRow->setOvertime(8);
        if($i > 5)
        {
          $workingtimesRow->setDeletedAt(new \Datetime());
        }
        $em->persist($workingtimesRow);
        $em->flush();
        $i++;
      }
    }
    $WorkingHours = $this->getDoctrine()->getRepository('EmployeeBundle:WorkingHours')->findBy(array('employeeId' => $employee->getId()));
      date_default_timezone_set("Europe/Berlin");
      $Date=new \DateTime($date);
      $HoursOfWork=0.0;
      $employeeworkingdays=[];
      for($i=1 ;$i<=$Date->format('t');$i++)
      {
        $Day = $Date->format('Y-m') . '-' . $i;
        $DayDatetime=clone(new \DateTime($Day));
          $employeeworkingday=  $this->findEmployeeworkdayPrint($id,$Day,$WorkingHours);
          $HoursOfWork=($WorkingHours[$DayDatetime->format('N')-1]->getOvertime());
          $employeeworkingday->HoursOfWork = $HoursOfWork;

          $employeeworkingday->overtimePremium=0;
          if(!empty($employeeworkingday->kuz))
          {
            $employeeworkingday->kuz =$WorkingHours[(new \Datetime($employeeworkingday->datum))->format('N')-1]->getOvertime() * $employeeworkingday->kuz;
            $employeeworkingday->HoursOfWork =  $employeeworkingday->HoursOfWork -   $employeeworkingday->kuz  ;
          }
          if(!empty($employeeworkingday->ausgleichBereitschaft ))
          {
            $employeeworkingday->ausgleichBereitschaft =$WorkingHours[(new \Datetime($employeeworkingday->datum))->format('N')-1]->getOvertime() * $employeeworkingday->ausgleichBereitschaft;
            $employeeworkingday->HoursOfWork =  $employeeworkingday->HoursOfWork -   $employeeworkingday->ausgleichBereitschaft  ;
          }
          if(!empty($employeeworkingday->uberstundenausgleich))
          {
            $employeeworkingday->uberstundenausgleich =$WorkingHours[(new \Datetime($employeeworkingday->datum))->format('N')-1]->getOvertime() * $employeeworkingday->uberstundenausgleich;
            $employeeworkingday->HoursOfWork =  $employeeworkingday->HoursOfWork -   $employeeworkingday->uberstundenausgleich  ;
          }
          if(!empty($employeeworkingday->urlaub))
          {
            $employeeworkingday->urlaub =$WorkingHours[(new \Datetime($employeeworkingday->datum))->format('N')-1]->getOvertime() * $employeeworkingday->urlaub;
            $employeeworkingday->HoursOfWork =  $employeeworkingday->HoursOfWork -   $employeeworkingday->urlaub  ;
          }
          if($WorkingHours[$DayDatetime->format('N')-1]->getAllowOvertime() == 1 AND  $DayDatetime < (new \Datetime()) )
          {
            if( $employeeworkingday->sum_workingtime > $employeeworkingday->HoursOfWork  )
            {
              $employeeworkingday->overtimePremium= $employeeworkingday->sum_workingtime -  $employeeworkingday->HoursOfWork  ;
            }
            else
            {
                $employeeworkingday->overtimePremium= $employeeworkingday->sum_workingtime -  $employeeworkingday->HoursOfWork  ;
            }

          }


          $employeeworkingdays[] =$employeeworkingday;
      }

      return $this->render('EmployeeBundle:Employeeworkday:indexEmployeeByMonthPrint.html.twig', array(
          'workingtimes' => $employeeworkingdays,
          'employee' =>  $employee,
          'Date' =>  $Date,
          'workingHours' => $WorkingHours,
      ));
  }

    public function managersAction()
    {
       $em = $this->getDoctrine()->getManager();
       $mYgroup = $em->getRepository('EmployeeBundle:Employeeworkingtimegroup')->findEmployee($this->getUser()->getEmployee()->getId());
       $ManagersList = $em->getRepository('EmployeeBundle:Employeeworkingtimeeditgroup')->findByGroup($mYgroup->getGroupId());
       $myManagers=[];
          foreach($ManagersList as $employee)
          {
           if($employee->getEmployeeId()->getId() != $this->getUser()->getEmployee()->getId())
                {
                    //2m => Monat
                        $connection = $em->getConnection();
                        $statement = $connection->prepare("
                        SELECT count(*) As attempt FROM employeetempdataview where from_employee='".$this->getUser()->getEmployee()->getId()."' AND to_employee = '". $employee->getEmployeeId()->getId()."'
                        AND Create_at LIKE '".$now=(new \DateTime())->format('Y-m')."%'
                        GROUP by to_employee
                        ");
                        $statement->execute();
                        $TempsData = $statement->fetchAll();
                    //
                if(empty($TempsData) or $TempsData[0]['attempt'] < 2 ){  $myManagers[] =array('name' => $employee->getEmployeeId()->getName(),'id' => $employee->getEmployeeId()->getId());}
                }
          }
          return new JsonResponse([
            'managers' 		=> $myManagers,
        ]);
    }

    public function EmployeesQrAction()  // use
    {
        $em = $this->getDoctrine()->getManager();
        $employees = $em->getRepository('EmployeeBundle:Employee')->getAllAvailable();
        return $this->render('EmployeeBundle:Employeeworkday:employeesQr.html.twig', array(
               'employees' => $employees
        ));
    }


    public function EmployeesQrCardAction()  // use
    {
        $em = $this->getDoctrine()->getManager();
        $employees = $em->getRepository('EmployeeBundle:Employee')->getAllAvailable();
        return $this->render('EmployeeBundle:Employeeworkday:employeesQrCard.html.twig', array(
               'employees' => $employees
        ));
    }

    public function employeeByMonthJsonAction($id ,$date )  // use
    {
        date_default_timezone_set("Europe/Berlin");
        $Date=new \DateTime($date);
        function minutes($time){
            $time = explode(':', $time);
            return ($time[0]*60) + ($time[1]) ;
        }
        function minutescontract($time){
           return $time * 60;
            $time = explode('.', $time);
            if(empty($time[1]))return ($time[0]*60);
            return ($time[0]*60) + ($time[1]) ;
        }
        function minutestoHours($intTime){
            $h=0;
            if($intTime >0 )
                    {while ( $intTime >= 60 )
                    { $h++;$intTime-=60;}
                    return '-'.$h .':'.$intTime;
            }
            else
                {while ( $intTime <= 60 )
                    { $h++;$intTime+=60;}
                return  $h.':'.$intTime;
                }
        }
        $employeeworkingdays=[];
        $em = $this->getDoctrine()->getManager();
        $employee = $em->getRepository('EmployeeBundle:Employee')->find($id);
        if (!empty($employee->getContract()) && !empty( $employee->getContract()->getWeeklyHoursOfWork()))
        $HoursOfWork = $employee->getContract()->getWeeklyHoursOfWork()/5 ;
        for($i=1 ;$i<=$Date->format('t');$i++)
        {   $Day = (new \DateTime($date))->format('Y-m') . '-' . $i ;
            $employeeworkingday=  $this->findEmployeeworkday($id,$Day);
            if(empty($HoursOfWork))$HoursOfWork=0.1;
            $employeeworkingday->overtimePremium= minutestoHours(minutescontract($HoursOfWork)-(minutes($employeeworkingday->sum_workingtime)));
            $HoursofWork=  minutestoHours($HoursOfWork*60);
            $employeeworkingday->HoursOfWork= $HoursofWork;
            $employeeworkingdays[]= $employeeworkingday;
        }
            return new JsonResponse(
                $employeeworkingdays
            );
    }

    public function editDayTableAction($id,$date)   // use
    {
        if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL') or $id == $this->getUser()->getEmployee()->getId())
        {
        }
        else{
            $id =$this->getUser()->getEmployee()->getId();
        }
            $breaks = new Breaktime();
            $em = $this->getDoctrine()->getManager();
            $employee = $em->getRepository('EmployeeBundle:Employee')->find($id);
            $employeeworking = $em->getRepository('EmployeeBundle:Employeeworkday')->findbyMonthWithOutEnd($id, $date);
            if(!empty($employeeworking ))
            {$breaks = $em->getRepository('EmployeeBundle:Breaktime')->findByEmployeeWorkingday($employeeworking->getId());}
            return $this->render('EmployeeBundle:Employeeworkday:editDay.html.twig', array(
                'employeeworking' => $employeeworking,
                'breaks' =>  $breaks,
                'employee' => $employee,
                'date' => $date,
            ));
    }

    public function deleteWorkingdayByIdJsonAction($id)  //use
    {
        $em = $this->getDoctrine()->getManager();
        $breaks = $em->getRepository('EmployeeBundle:Breaktime')->findByEmployeeWorkingday($id);
        foreach ($breaks as $break)
        {
            $break->setDeletedAt(new \Datetime());
            $em->persist($break);
            $em->flush();
        }
        $employeeworking = $em->getRepository('EmployeeBundle:Employeeworkday')->find($id);
        if(!empty($employeeworking))
        {
            $employeeworking->setDeletedAt(new \DateTime());
            $em->persist($employeeworking);
            $em->flush();
        }
        return new JsonResponse([
            'status' 		=> 'true',
        ]);
    }

    public function deleteBreakByIdJsonAction($id)  //use
    {
        $em = $this->getDoctrine()->getManager();
        $break = $em->getRepository('EmployeeBundle:Breaktime')->find($id);
        $break->setDeletedAt(new \Datetime());
        $em->persist($break);
        $em->flush();
        return new JsonResponse([
            'status' 		=> 'deleted',
        ]);
    }

    public function EditBeginBreakByIdJsonAction($id,$date) // use
    {
      $daterequest =  new \DateTime($date);
      $em = $this->getDoctrine()->getManager();
        $break= $em->getRepository('EmployeeBundle:Breaktime')->find($id);
        if($break->getEnd() < $daterequest )
         {
            return new JsonResponse([
            'status' 		=> 'false Time',
            'date' => $daterequest->format('H:i')
            ]);
         }
        $workday= $em->getRepository('EmployeeBundle:Employeeworkday')->checkByDateForEditFunction($break->getEmployeeworkdayId()->getId() , $daterequest);

        if(!($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL') or isset($break) or $this->getUser()->getEmployee()->getId() == $break->getEmployeeworkday()->getEmployeeId()))
        {
            return new JsonResponse([
                'status' 		=> 'false',
                'date' => $daterequest->format('H:i')
                ]);
        }
         if(empty($workday)){
                 return new JsonResponse([
                 'status' 		=> 'false workday',
                 'date' => $daterequest->format('H:i')
                 ]);
             }
        $Breaktimes= $em->getRepository('EmployeeBundle:Breaktime')->findByEmployeeWorkingday( $workday->getId() , $daterequest);
             if(!empty($Breaktimes))
             {
                 foreach ($Breaktimes as $Breaktime)
                 {
                     if($Breaktime->getId() != $id and (
                      (($Breaktime->getBegin() <= $daterequest and $Breaktime->getEnd() >= $daterequest )
                      or($Breaktime->getBegin() <= $break->getEnd()   and $Breaktime->getEnd() >= $break->getEnd()  ))
                     or ($Breaktime->getBegin() >= $daterequest and $Breaktime->getEnd() <= $break->getEnd() )))
                     return new JsonResponse([
                         'status' 		=> 'false',
                         ]);
                 }
             }
        $break->setBegin($daterequest);
        $em->persist($break);
        $em->flush();
        return new JsonResponse([
            'status' 		=> 'true',
            'date' => $daterequest->format('H:i')
            ]);
    }

    public function EditEndBreakByIdJsonAction($id,$date) // use
    {
      $daterequest =  new \DateTime($date);
      $em = $this->getDoctrine()->getManager();

         $break= $em->getRepository('EmployeeBundle:Breaktime')->find($id);
         if($break->getBegin() > $daterequest )
         {
            return new JsonResponse([
            'status' 		=> 'false Time',
            'date' => $daterequest->format('H:i')
            ]);
         }
         $workday= $em->getRepository('EmployeeBundle:Employeeworkday')->checkByDateForEditFunction($break->getEmployeeworkdayId()->getId() , $daterequest);

         if(!($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL') or isset($break) or $this->getUser()->getEmployee()->getId() == $break->getEmployeeworkday()->getEmployeeId()))
         {
             return new JsonResponse([
                 'status' 		=> 'false',
                 'date' => $daterequest->format('H:i')
                 ]);
         }
         if(empty($workday)){
                 return new JsonResponse([
                 'status' 		=> 'false workday',
                 'date' => $daterequest->format('H:i')
                 ]);
             }
         if(empty($break->getEnd()) and ($daterequest > $break->getBegin())  )
         {
            $break->setEnd($daterequest );
            $em->persist($break);
            $em->flush();
            return new JsonResponse([
                'status' 		=> 'true',
                'date' => $daterequest->format('H:i')
                ]);
         }

          $Breaktimes= $em->getRepository('EmployeeBundle:Breaktime')->findByEmployeeWorkingday( $workday->getId() , $daterequest);
            if(!empty($Breaktimes))
            {
                foreach ($Breaktimes as $Breaktime)
                {
                    if($Breaktime->getId() != $id and (
                     (($Breaktime->getBegin() <= $break->getBegin() and $Breaktime->getEnd() >= $break->getBegin() )
                     or($Breaktime->getBegin() <= $daterequest  and $Breaktime->getEnd() >= $daterequest ))
                    or ($Breaktime->getBegin() >= $break->getBegin() and $Breaktime->getEnd() <= $daterequest )))
                    return new JsonResponse([
                        'status' 		=> 'false',
                        ]);
                }
            }

        $break->setEnd($daterequest);
        $em->persist($break);
        $em->flush();
        return new JsonResponse([
            'status' 		=> 'true',
            'date' => $daterequest->format('H:i')
            ]);
    }

    public function EditBeginWorkingdayJsonAction($id,$date)
    {
      $daterequest =  new \DateTime($date);
      if($daterequest > (new \DateTime()))
            {
                return new JsonResponse([
                'status' 		=> 'false',
                'date' => $daterequest->format('H:i')
                ]);
            }
      $em = $this->getDoctrine()->getManager();
      $break= $em->getRepository('EmployeeBundle:Breaktime')->checkByDateBegin($id,$daterequest);
          if(!empty($break)){
                    return new JsonResponse([
                    'status' 		=> 'false',
                    'date' => $daterequest->format('H:i')
                    ]);
              }
        $Employeeworkday= $em->getRepository('EmployeeBundle:Employeeworkday')->find($id);
        if(!($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL') or $this->getUser()->getEmployee()->getId() == $Employeeworkday->getEmployeeId()))
        {
            return new JsonResponse([
                'status' 		=> 'false',
                'date' => $daterequest->format('H:i')
                ]);
        }
        if( !empty($Employeeworkday->getEndEmployeepositionDate()) and ($Employeeworkday->getEndEmployeepositionDate()) <= $daterequest )
        {
            return new JsonResponse([
                'status' 		=> 'false',
                'date' => $daterequest->format('H:i')
                ]);
        }
        if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL')){
            $Employeeworkday->setBeginEmployeepositionDate($daterequest);
            $Employeeworkday->setStatusBeginId('1');
            $Employeeworkday->setBeginEditByEmployeeId($this->getUser()->getEmployee()->getId());
        }
        else{
            $Employeeworkday->setNewBeginEmployeepositionDate($daterequest);
            $Employeeworkday->setNewBeginEmployeepositionStatusId('3');
            $Employeeworkday->setBeginEditByEmployeeId($this->getUser()->getEmployee()->getId());
        }
        $em->persist($Employeeworkday);
        $em->flush();
        return new JsonResponse([
            'status' 		=> 'true',
            'date' => $daterequest->format('H:i')
            ]);
      }
      public function EditEndWorkingdayJsonAction($id,$date)   // use
      {
        $daterequest =  new \DateTime($date);
        if($daterequest > (new \DateTime()))
            {
                return new JsonResponse([
                'status' 		=> 'false',
                'date' => $daterequest->format('H:i')
                ]);
            }
        $em = $this->getDoctrine()->getManager();
        $break= $em->getRepository('EmployeeBundle:Breaktime')->checkByDateEnd($id,$daterequest);
        if(!empty($break)){
                      return new JsonResponse([
                      'status' 		=> 'false',
                      'date' => $daterequest->format('H:i')
                      ]);
            }
        $Employeeworkday= $em->getRepository('EmployeeBundle:Employeeworkday')->find($id);
        if(!($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL') or $this->getUser()->getEmployee()->getId() == $Employeeworkday->getEmployeeId()))
        {
            return new JsonResponse([
                'status' 		=> 'false',
                'date' => $daterequest->format('H:i')
                ]);
        }
        if( ($Employeeworkday->getBeginEmployeepositionDate()) >= $daterequest )
        {
            return new JsonResponse([
                'status' 		=> 'false',
                'date' => $daterequest->format('H:i')
                ]);
        }
        if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL')){
        $Employeeworkday->setEndEmployeepositionDate($daterequest);
        $Employeeworkday->setEndLoginTypeId('3');
        $Employeeworkday->setEndEmployeepositionId($Employeeworkday->getBeginEmployeepositionId());
        $Employeeworkday->setStatusEndId('1');
        $Employeeworkday->setEndEditByEmployeeId($this->getUser()->getEmployee()->getId());
        }
        else{
            $Employeeworkday->setNewEndEmployeepositionDate($daterequest);
            $Employeeworkday->setNewEndEmployeepositionStatusId('3');
            $Employeeworkday->setEndLoginTypeId('3');
            $Employeeworkday->setEndEditByEmployeeId($this->getUser()->getEmployee()->getId());
        }
        $em->persist($Employeeworkday);
        $em->flush();
          return new JsonResponse([
              'status' 		=> 'true',
              'date' => $daterequest->format('H:i')
              ]);
        }

      public function editstatusAjaxAction($id)   // use
      {
        $em = $this->getDoctrine()->getManager();
        $Employeeworkday= $em->getRepository('EmployeeBundle:Employeeworkday')->find($id);
        $Employeeworkday->setBeginEditByEmployeeId($this->getUser()->getEmployee()->getId());
        $Employeeworkday->setStatusBeginId('1');
        $Employeeworkday->setEndEditByEmployeeId($this->getUser()->getEmployee()->getId());
        $Employeeworkday->setStatusEndId('1');
          $em->persist($Employeeworkday);
          $em->flush();
          return new JsonResponse([
              'status' 		=> 'true',
              ]);
        }


        public function moveWorkingdayByIdJsonAction($id,$date,$status)
        {
            $em = $this->getDoctrine()->getManager();
            $Employeeworkday= $em->getRepository('EmployeeBundle:Employeeworkday')->find($id);
            if(!empty($Employeeworkday->getNewBeginEmployeepositionStatusId())){$Btime=  $Employeeworkday->getNewBeginEmployeepositionDate();}else{$Btime=  $Employeeworkday->getBeginEmployeepositionDate();}
            if(!empty($Employeeworkday->getNewEndEmployeepositionStatusId())){$Etime=  $Employeeworkday->getNewEndEmployeepositionDate();}else{$Etime=  $Employeeworkday->getEndEmployeepositionDate();}
            if( $Btime >= $Etime  and $Btime > (new \DateTime()) )
                {
                    return new JsonResponse([
                        'status' 		=> 'false time',
                        ]);
                }
            $em = $this->getDoctrine()->getManager();
            $employeeworkday = $em->getRepository('EmployeeBundle:Employeeworkday')->findbyMonthWithOutEnd($id, $date);
            if(!empty($employeeworkday))
            {
                return new JsonResponse([
                    'status' 		=> 'false',
                    ]);
            }
            //New begin//
            if($status == 2)
            {
                $Employeeworkday->setNewBeginEmployeepositionStatusId($status);
                $Employeeworkday->setNewEndEmployeepositionStatusId($status);
                $em->persist($Employeeworkday);
                $em->flush();
                    return new JsonResponse([
                        'status' 		=> 'true',
                    ]);
            }
            if(!empty($Employeeworkday->getNewBeginEmployeepositionStatusId()))
            {
                $Employeeworkday->setBeginEmployeepositionDate($Btime);
                $Employeeworkday->setNewBeginEmployeepositionStatusId($status);
                $Employeeworkday->setBeginEditByEmployeeId($this->getUser()->getEmployee()->getId());
                $Employeeworkday->setStatusBeginId('1');
            }
            //New end//
            if(!empty($Employeeworkday->getNewEndEmployeepositionStatusId()))
            {
                $Employeeworkday->setEndEmployeepositionDate($Etime);
                $Employeeworkday->setNewEndEmployeepositionStatusId($status);
                $Employeeworkday->setEndEditByEmployeeId($this->getUser()->getEmployee()->getId());
                $Employeeworkday->setStatusEndId('1');
            }
            //End employee//
            $em->persist($Employeeworkday);
            $em->flush();
            return new JsonResponse([
                'status' 		=> 'true',
                ]);
        }


        public function createWorkingdayAjaxAction($id,$date,$Btime,$Etime,$comment)
        {
            if($comment =="NULL" or empty($comment))
            {
                return new JsonResponse([
                    'status' 		=> 'false',
                ]);
            }

              if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL'))
                 {
                        $Btime=  new \DateTime($date. " ".$Btime);
                        $Etime= new \DateTime($date. " ".$Etime);
                        if( $Btime >= $Etime  and $Btime > (new \DateTime()) )
                        {
                            return new JsonResponse([
                                'status' 		=> 'false time',
                                ]);
                        }
                        $em = $this->getDoctrine()->getManager();
                        $employeeworkday = $em->getRepository('EmployeeBundle:Employeeworkday')->findbyMonthWithOutEnd($id, $date);
                        if(!empty($employeeworkday))
                        {
                            return new JsonResponse([
                                'status' 		=> 'false',
                                ]);
                        }
                        $position = new Employeeposition;
                        $position->setName($comment);
                        $position->setStreet(' ');
                        $position->setCreateAt(new \DateTime());
                        $em->persist($position);
                        $em->flush();

                        $Employeeworkday = new Employeeworkday();
                        $Employeeworkday->setEmployeeId($id);
                        $Employeeworkday->setBeginEmployeepositionId($position);
                        $Employeeworkday->setBeginEmployeepositionDate($Btime);
                        $Employeeworkday->setBeginLoginTypeId('3');
                        $Employeeworkday->setBeginEditByEmployeeId($this->getUser()->getEmployee()->getId());
                        $Employeeworkday->setStatusBeginId('1');
                        $Employeeworkday->setBeginWebbrowser('');
                        $Employeeworkday->setBeginIp('');
                        $Employeeworkday->setEndEmployeepositionId($position);
                        $Employeeworkday->setEndEmployeepositionDate($Etime);
                        $Employeeworkday->setEndLoginTypeId('3');
                        $Employeeworkday->setEndEditByEmployeeId($this->getUser()->getEmployee()->getId());
                        $Employeeworkday->setStatusEndId('1');
                        $Employeeworkday->setEndWebbrowser('');
                        $Employeeworkday->setEndIp('');
                        $Employeeworkday->setCreateAt(new \DateTime());
                        $em->persist($Employeeworkday);
                        $em->flush();
                        return new JsonResponse([
                            'status' 		=> 'true',
                            ]);
                    }
                    else
                    {
                        $Btime=  new \DateTime($date. " ".$Btime);
                        $Etime= new \DateTime($date. " ".$Etime);
                        if( $Btime >= $Etime  and $Btime > (new \DateTime()) )
                        {
                            return new JsonResponse([
                                'status' 		=> 'false time',
                                ]);
                        }
                        $em = $this->getDoctrine()->getManager();
                        $employeeworkday = $em->getRepository('EmployeeBundle:Employeeworkday')->findbyMonthWithOutEnd($id, $date);
                        if(!empty($employeeworkday) or $id != $this->getUser()->getEmployee()->getId())
                        {
                            return new JsonResponse([
                                'status' 		=> 'false',
                                ]);
                        }
                        $position = new Employeeposition;
                        $position->setName($comment);
                        $position->setStreet(' ');
                        $position->setCreateAt(new \DateTime());
                        $em->persist($position);
                        $em->flush();
                        $Employeeworkday = new Employeeworkday();
                        $Employeeworkday->setEmployeeId($id);
                        $Employeeworkday->setBeginEmployeepositionId($position);
                        $Employeeworkday->setBeginEmployeepositionDate($Btime);
                        //New begin//
                        $Employeeworkday->setNewBeginEmployeepositionDate($Btime);
                        $Employeeworkday->setNewBeginEmployeepositionStatusId(3);
                        $Employeeworkday->setNewBeginEmployeepositionComment($comment);
                        //New end//
                        $Employeeworkday->setNewEndEmployeepositionDate($Etime);
                        $Employeeworkday->setNewEndEmployeepositionStatusId(3);
                        $Employeeworkday->setNewEndEmployeepositionComment($comment);
                        //End employee//
                        $Employeeworkday->setBeginLoginTypeId('3');
                        $Employeeworkday->setBeginEditByEmployeeId($this->getUser()->getEmployee()->getId());
                        $Employeeworkday->setStatusBeginId('3');
                        $Employeeworkday->setBeginWebbrowser('');
                        $Employeeworkday->setBeginIp('');
                        $Employeeworkday->setEndEmployeepositionId($position);
                        $Employeeworkday->setEndEmployeepositionDate($Etime);
                        $Employeeworkday->setEndLoginTypeId('3');
                        $Employeeworkday->setEndEditByEmployeeId($this->getUser()->getEmployee()->getId());
                        $Employeeworkday->setStatusEndId('3');
                        $Employeeworkday->setEndWebbrowser('');
                        $Employeeworkday->setEndIp('');
                        $Employeeworkday->setCreateAt(new \DateTime());
                        $em->persist($Employeeworkday);
                        $em->flush();
                        return new JsonResponse([
                            'status' 		=> 'true',
                            ]);
                    }
        }


        public function createWorkingday2AjaxAction($id,$date,$time,$comment)
        {
            if($comment =="NULL" or empty($comment))
            {
                return new JsonResponse([
                    'status' 		=> 'false',
                ]);
            }
            $Btime=  new \DateTime($date. " ".$time);
            $em = $this->getDoctrine()->getManager();
            $Employeeworkday = $em->getRepository('EmployeeBundle:Employeeworkday')->findbyMonthWithOutEnd($id, $date);

            if(!empty($Employeeworkday))
            {
                return new JsonResponse([
                    'status' 		=> 'false',
                    ]);
            }
            else
            {
                $position = new Employeeposition;
                $position->setName($comment);
                $position->setStreet(' ');
                $position->setCreateAt(new \DateTime());
                $em->persist($position);
                $em->flush();
                $Employeeworkday = new Employeeworkday();
                $Employeeworkday->setEmployeeId($id);
                $Employeeworkday->setBeginEmployeepositionId($position);
                $Employeeworkday->setBeginEmployeepositionDate($Btime);
                $Employeeworkday->setBeginLoginTypeId('3');
                $Employeeworkday->setBeginEditByEmployeeId($this->getUser()->getEmployee()->getId());
                $Employeeworkday->setStatusBeginId('1');
                $Employeeworkday->setBeginWebbrowser('');
                $Employeeworkday->setBeginIp('');
                $Employeeworkday->setCreateAt(new \DateTime());
            }
            $em->persist($Employeeworkday);
            $em->flush();
            return new JsonResponse([
                'status' 		=> 'true',
            ]);
        }

        public function createbreakAction($id,$date,$Btime,$Etime)
        {
            if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL') or $id == $this->getUser()->getEmployee()->getId())
            {

            }
            else{ $id =$this->getUser()->getEmployee()->getId();}
            $Btime=  new \DateTime($date. " ".$Btime);
            $Etime= new \DateTime($date. " ".$Etime);
            if( $Btime >= $Etime )
            {
                return new JsonResponse([
                    'status' 		=> 'false time',
                    ]);
            }
            $em = $this->getDoctrine()->getManager();
            $employeeworkday = $em->getRepository('EmployeeBundle:Employeeworkday')->findbyMonth($id, $date);
            if(empty($employeeworkday))
            {
                return new JsonResponse([
                    'status' 		=> 'false',
                    ]);
            }
            if( $Btime  <= $employeeworkday->getBeginEmployeepositionDate() or  $Etime >= $employeeworkday->getEndEmployeepositionDate() )
            {
                return new JsonResponse([
                    'status' 		=> 'false',
                    ]);
            }
            $Breaktimes= $em->getRepository('EmployeeBundle:Breaktime')->findByEmployeeWorkingday( $employeeworkday->getId() , $Btime);
            if(!empty($Breaktimes))
            {
                foreach ($Breaktimes as $Breaktime)
                {
                    if($Breaktime->getId() != $id and
                     (($Breaktime->getBegin() <= $Btime and $Breaktime->getEnd() >= $Btime )
                     or($Breaktime->getBegin() <= $Etime  and $Breaktime->getEnd() >= $Etime ))
                     or($Breaktime->getBegin() >= $Btime  and $Breaktime->getEnd() <= $Etime ))
                    return new JsonResponse([
                        'status' 		=> 'false',
                        ]);
                }
            }
            $break = new Breaktime();
            $break->setEmployeeworkdayId($employeeworkday);
            $break->setBegin($Btime);
            $break->setEnd($Etime);
            $break->setCreateAt(new \DateTime());
            $em->persist($break);
            $em->flush();
            return new JsonResponse([
                'status' 		=> 'true',
                ]);
        }

        public function deleteEndTimeWorkingdayByIdJsonAction($id)  //use
        {
            $em = $this->getDoctrine()->getManager();
            $employeeworking = $em->getRepository('EmployeeBundle:Employeeworkday')->find($id);
            if(!empty($employeeworking))
            {
                $employeeworking->setEndEmployeepositionId(NULL);
                $employeeworking->setEndEmployeepositionDate(NULL);
                $employeeworking->setEndLoginTypeId(NULL);
                $employeeworking->setEndEditByEmployeeId(NULL);
                $employeeworking->setStatusEndId(NULL);
                $employeeworking->setEndWebbrowser(NULL);
                $employeeworking->setEndIp(NULL);
                $em->persist($employeeworking);
                $em->flush();
            }
            return new JsonResponse([
                'status' 		=> 'true',
            ]);
        }

        public function absenceReportAction()
        {
            set_time_limit(0);
            ini_set('memory_limit','-1');
            // $notworkdays=[];
            // absence //
            $Resultemployees  = array();
            $em= $em = $this->getDoctrine()->getManager();
            $employees = $em->getRepository('EmployeeBundle:Employee')->getAllAvailable();
            $monthdate = (new \Datetime())->modify('-1 week');
            $month = (new \Datetime())->modify('-1 day');
         //   $reason = $em->getRepository('AbsenceBundle:Reason')->find(40);
         //   $status = $em->getRepository('AbsenceBundle:Status')->find(1);
            while($monthdate < $month)
            { $day = clone($monthdate);
                foreach ($employees as $employee)
                {
                    if($day->format('N') < 6  and $employee->getId() != 51 and ($employee->getDepartment() and !in_array($employee->getDepartment()->getId() , array('1','8')) or $employee->getDepartment() == NULL)){
                        if( $this->absenceReport($employee,$monthdate) == false )
                        {
                            array_push($Resultemployees,array('employee' => $employee->getFname() , 'department' => (empty($employee->getDepartment())?'':$employee->getDepartment()->getName()) ,  'date' => $day));
                        }
                        $employee->abwesendtag=$monthdate;
                    }
                }
                $monthdate =   $monthdate->modify('+1 day');
            }

            return $this->render('EmployeeBundle:Employeeworkday:abwesend.html.twig', array(
                'absencesReport' => $Resultemployees,
            ));
        }

        public function absenceReport( $employee , $date )
        {
            $em = $this->getDoctrine()->getManager();
            $connection = $em->getConnection();
            $statement = $connection->prepare("SELECT 'true' from public_holiday where type = 1 and start = '".$date->format('Y-m-d')."' LIMIT 1 ");
            $statement->execute();
            $status = $statement->fetchAll();
            if(!empty($status ))
            return true;

            $connection = $em->getConnection();
            $statement = $connection->prepare("SELECT 'true' from employeeworkday where employee_id = ".$employee->getId()." AND begin_employeeposition_date LIKE '".$date->format('Y-m-d')."%' ");
            $statement->execute();
            $status = $statement->fetchAll();
            if(!empty($status ))
            return true;

            $connection = $em->getConnection();
            $statement = $connection->prepare("SELECT 'true' from absence where employee_id = ".$employee->getId()." and reason_id in (2,3,4,5,6,7,8,9,10,13,14,15,16,17,20,23,27,29,31,33,39) and  from_date <='".$date->format('Y-m-d')."' and to_date >= '".$date->format('Y-m-d')."' LIMIT 1 ");
            $statement->execute();
            $status = $statement->fetchAll();
            if(!empty($status ))
            return true;

            $statement = $connection->prepare("SELECT 'true' from workday where employee_id = ".$employee->getId()." and status_id = 1 and  date = '".$date->format('Y-m-d')."' LIMIT 1 ");
            $statement->execute();
            $status = $statement->fetchAll();
            if(!empty($status ))
            return true;

            $statement = $connection->prepare("SELECT 'true' from vehicle_log where employee_id = ".$employee->getId()." and reason_id = 1 and  vehicle_log_begin_time > '".$date->format('Y-m-d')."' LIMIT 1 ");
            $statement->execute();
            $status = $statement->fetchAll();
            if(!empty($status ))
            return true;

            $date_end = clone $date;
            $date_end->modify('midnight +1 day');
            $statement = $connection->prepare("SELECT 'true' from expenseview where did = ".$employee->getTrimbleId()." and time >= '".$date->format('Y-m-d')."' and time <'".$date_end->format('Y-m-d')."' LIMIT 1 ");
            $statement->execute();
            $status = $statement->fetchAll();
            if(!empty($status))
            return true;

            $statement = $connection->prepare("SELECT 'true' from tracedata where did = ".$employee->getTrimbleId()." and time >= '".$date->format('Y-m-d')."' and time <'".$date_end->format('Y-m-d')."' LIMIT 1 ");
            $statement->execute();
            $status = $statement->fetchAll();
            if(!empty($status ))
            return true;
        return false;
        }
}
