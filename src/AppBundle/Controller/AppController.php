<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\TelephoneList;
use AppBundle\Entity\LinkList;
use EmployeeBundle\Entity\Employeeworkday;

class AppController extends Controller
{
    public function indexAction()
    {

      //beginn_:Zeiterfassung
      if($this->get('security.authorization_checker')->isGranted('ROLE_ROBOT'))
      return $this->render('AppBundle:Default:index_robot.html.twig', array(
        ));
      //ende:_ Zeiterfassung
        $em = $this->getDoctrine()->getManager();

        $announcements = $em->getRepository('AnnouncementsBundle:Announcement')->getAllNotExpired();
        $usr = $this->container->get('security.context')->getToken()->getUser();
       //New new new new
       $workingTime = $em->getRepository('EmployeeBundle:Employeeworkday')->checksession($this->getUser()->getEmployee()->getId());
       if(!empty($workingTime))
       {
          if(empty($workingTime->getEndLoginTypeId()))
              {$workingTimeStatus = 'start';}
          else
          {$workingTimeStatus = 'end';}
         $breakstatus = $em->getRepository('EmployeeBundle:Breaktime')->checkBreak($workingTime->getId());
         if($breakstatus == null )
         {$breakstatus = 'start';}
         else {$breakstatus = 'end';}
       }
       else {$breakstatus = null;$workingTimeStatus=null;}

       $workingTime =  $em->getRepository('EmployeeBundle:Employeeworkday')->checksession($this->getUser()->getEmployee()->getId());

        $connection = $em->getConnection();
        $statement = $connection->prepare("
        SELECT * FROM employeetempdataview where to_employee='".$this->getUser()->getEmployee()->getId()."' AND Create_at LIKE '".$now=(new \DateTime())->format('Y-m-d')."%' GROUP by from_employee
        ");
        $statement->execute();
        $TempsData = $statement->fetchAll();
        $EditEmployeesStatus = [];
        foreach ($TempsData as $temp)
        {
          $employeeWorkdayInformation =  $em->getRepository('EmployeeBundle:Employeeworkday')->checksessionWithStatusInBearbeitung($temp['from_employee']);
          if(!empty($employeeWorkdayInformation ))
          {$editemployeeInformation = $em->getRepository('EmployeeBundle:Employee')->find($temp['from_employee']);
          $employeeWorkdayInformation->name =$editemployeeInformation->getName() ;
          $EditEmployeesStatus []=  $employeeWorkdayInformation;}
        }
        // zeit = h * 10 for Gui
       if(!empty($workingTime) && !empty($workingTime->getEndEmployeepositionDate()))
       {
        $workingTime->zeit = $workingTime->getBeginEmployeepositionDate()->diff($workingTime->getEndEmployeepositionDate())->format('%H:%I');
       }
       elseif (!empty($workingTime) && !empty($workingTime->getBeginEmployeepositionDate()))
       {       $workingTime->zeit = $workingTime->getBeginEmployeepositionDate()->diff(new \DateTime())->format('%H:%I');}
       if(empty($workingTime))
       { $workingTime = new Employeeworkday(); $workingTime->zeit = '--:--' ;}
         $breaksinfo=$this->breakstime($workingTime->getId());

       $TelephoneList = $this->getDoctrine()->getRepository('AppBundle:TelephoneList')->getallOrderByGroup();
       $TelephoneListgroups = $this->getDoctrine()->getRepository('AppBundle:TelephoneList')->getallGroup();

       $linkList = $this->getDoctrine()->getRepository('AppBundle:LinkList')->getallOrderByGroup();
       $linkListgroups = $this->getDoctrine()->getRepository('AppBundle:LinkList')->getallGroup();
       $employees = $em->getRepository('EmployeeBundle:Employee')->getLatestBirthdays();
       $employeesnew = [];

      foreach ($employees as $key => $employee) {
           $employee = $em->getRepository('EmployeeBundle:Employee')->find($employee["id"]);
           $employees[$key] =$employee;
          $oBirthday = $employee->getBirthday()->format('md');
          $oCurrentDate = (new \DateTime())->format('md');
          if ($oCurrentDate > $oBirthday) {
              $element = $employees[$key];
              unset($employees[$key]);
              array_push($employees, $employee);
          }
      }

        return $this->render('AppBundle:Default:index.html.twig', array(
          'breaksinfo' => $breaksinfo,
          'workingTime' => $workingTime,
          'editEmployeesStatus' => $EditEmployeesStatus,
          'workingTimeStatus' => $workingTimeStatus,
          'breakstatus' => $breakstatus,
          'employees' => $employees,
          'announcements' => $announcements,
          'TelephoneList' => $TelephoneList,
          'usr' => $usr,
          'telephoneListgroups' => $TelephoneListgroups,
          'linkList' => $linkList,
          'linkListgroups' => $linkListgroups,
      ));
    }

    public function breakstime($employeeworkdayId)
    {
      $em = $this->getDoctrine()->getManager();
      $breakstime= $em->getRepository('EmployeeBundle:Breaktime')->findByEmployeeworkdayId($employeeworkdayId);
      $begintime = null;
      $difftime = null;
      $endtime = null;
      $h=0;$i=0;
      foreach ($breakstime as $breaktime)
      {
       if(!empty($breaktime->getEnd()))
       {$h += $breaktime->getEnd()->diff($breaktime->getBegin())->format('%h') ;
        $i += $breaktime->getEnd()->diff($breaktime->getBegin())->format('%i') ;
        $begintime = $breaktime->getBegin()->format('H:i');
        $endtime = $breaktime->getEnd()->format('H:i'); }
        else {$begintime = $breaktime->getBegin()->format('H:i');$endtime = $breaktime->getEnd();}
      }
      while ($i >= 60 ){$i -= 60 ; $h++;}
      $difftime = sprintf("%02d",$h).':'.sprintf("%02d",$i);
      return array('difftime' => $difftime,'begintime' => $begintime,'endtime'=> $endtime ) ;
    }

    public function downloadRestgehaltanspruchAction($date)
    {
        if (
          !($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
          ) {
              throw new AccessDeniedException();
            }
        $employee_controller = $this->get('app.employee_controller');
        $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllAvailable();
        $date = Carbon::parse($date)->startOfDay()->endOfMonth();
        $absenceService = $this->get('app.absence_service', $this->getDoctrine());
        $start = Carbon::instance($date)->startOfYear();
        $end = Carbon::instance($date)->endOfMonth();
        $year = Carbon::instance($date)->year;
        $cols = [];
        array_push($cols, [
          'Abteilung',
          'Mitarbeiter',
          'Restgehalt',
          'effektiver Resturlaub',
          'theoretischer Resturlaub',
          'Monatsgehalt'
      ]);

        foreach ($employees as $employee) {
            if (!is_null($employee->getDepartment())) {
                $row = [
                  $employee->getDepartment()->getName(),
                  $employee->getName(),
                  $employee_controller->getSalaryRemainingDaysOfVacation($employee, $date),
                  $absenceService->getRemainingMtl2($employee->getId(), $start, $end, $year)['remainingmtl'],
                  $employee->getTheoreticRemainingDaysOfVacation($date),
                  $employee->getDailySalary(),
                  // $employee_controller->getRealRemainingDaysOfVacation($employee, $date),
              ];

                array_push($cols, $row);
            }
        }

        $filename = 'restgehaltsanspruch_'.$date->format('Y-m-d').'.xls';

        $xlsxService = $this->get('app.xlsx_service');

        $download = $xlsxService->createXlsx($cols, $filename);
    }





    public function  ShowTelephoneListDatatablesAction()
    {

        $em = $this->getDoctrine()->getManager();

        if( !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ACCOUNTING') && !$this->get('security.authorization_checker')->isGranted('ROLE_DISPOSITION') )
           {dump('Nicht admin');}
           else{
               $telephoneListe = $this->getDoctrine()->getRepository('AppBundle:TelephoneList')->getallOrderById();
                }
            //    dump($addresses);die();

        return $this->render('AppBundle:Default:ShowTelephoneList.html.twig', array(
            'telephoneListe' => $telephoneListe,
        ));
    }

    public function  DeleteTelephoneListeDatatablesAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $address = $em->getRepository('AppBundle:TelephoneList')->find($id);
        if (!$address) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }
        $em->remove($address);
        $em->flush();


        return $this->redirectToRoute('ShowAddressDatatables');
    }

    public function  EditTelephoneListeDatatablesAction($id ,Request $request)
    {

    if( !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ACCOUNTING') && !$this->get('security.authorization_checker')->isGranted('ROLE_DISPOSITION') )
       {dump('Nicht admin');}
       else{
           $TelefoneNumber = $this->getDoctrine()->getRepository('AppBundle:TelephoneList')->find($id);
            }

   $form = $this->createForm('AppBundle\Form\TelephoneListType', $TelefoneNumber);
   $form->handleRequest($request);

   if ($form->isSubmitted() && $form->isValid()) {

    $TelefoneNumber->setGroupname($form->get('Groupname')->getData());
    $TelefoneNumber->setName($form->get('name')->getData());
    $TelefoneNumber->setSymbol($form->get('symbol')->getData());
    $TelefoneNumber->setTelephone($form->get('telephone')->getData());
    $em = $this->getDoctrine()->getManager();
    $em->persist($TelefoneNumber);
    $em->flush();
    $em = $this->getDoctrine()->getManager();


     return $this->redirectToRoute('ShowAddressDatatables');

   }

   return $this->render('AppBundle:Default:TelephoneListCreate.html.twig', array(
    'form' => $form->createView(),
));
}

    public function  NewTelephoneListeDatatablesAction(Request $request)
    {

        if( !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ACCOUNTING') && !$this->get('security.authorization_checker')->isGranted('ROLE_DISPOSITION') )
           {dump('Nicht admin');}
           else{
               $addresses = $this->getDoctrine()->getRepository('AppBundle:TelephoneList')->getallOrderByGroup();
                }


       // return $this->render('VehicleLogBundle:VehicleLog:TelefoneNumber.html.twig', array(
         //   'addresses' => $addresses,
         //   'employees_deleted' => $employees_deleted,
        //    'salaryRemainingDaysOfVacation' => $salaryRemainingDaysOfVacation,
       // ));
       $telephoneList = new TelephoneList();

       $form = $this->createForm('AppBundle\Form\TelephoneListType', $telephoneList);

       $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {
          $telephoneList->setGroupname($form->get('Groupname')->getData());
          $telephoneList->setName($form->get('name')->getData());
          $telephoneList->setSymbol($form->get('symbol')->getData());
          $telephoneList->setTelephone($form->get('telephone')->getData());
          $em = $this->getDoctrine()->getManager();
          $em->persist($telephoneList);
          $em->flush();
          $em = $this->getDoctrine()->getManager();

         return $this->redirectToRoute('ShowAddressDatatables');

       }

       return $this->render('AppBundle:Default:TelephoneListCreate.html.twig', array(
        'form' => $form->createView(),
    ));
    }



    public function  ShowLinkListDatatablesAction()
    {

        $em = $this->getDoctrine()->getManager();

        if( !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ACCOUNTING') && !$this->get('security.authorization_checker')->isGranted('ROLE_DISPOSITION') )
           {dump('Nicht admin');}
           else{
               $linkListe = $this->getDoctrine()->getRepository('AppBundle:LinkList')->getallOrderById();
                }
            //    dump($addresses);die();

        return $this->render('AppBundle:Default:ShowLinkList.html.twig', array(
            'linkListe' => $linkListe,
        ));
    }

    public function  DeleteLinkListeDatatablesAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $address = $em->getRepository('AppBundle:LinkList')->find($id);
        if (!$address) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }
        $em->remove($address);
        $em->flush();


        return $this->redirectToRoute('ShowLinkDatatables');
    }

    public function  EditLinkListeDatatablesAction($id ,Request $request)
    {

    if( !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ACCOUNTING') && !$this->get('security.authorization_checker')->isGranted('ROLE_DISPOSITION') )
       {dump('Nicht admin');}
       else{
           $TelefoneNumber = $this->getDoctrine()->getRepository('AppBundle:LinkList')->find($id);
            }

   $form = $this->createForm('AppBundle\Form\LinkListType', $TelefoneNumber);
   $form->handleRequest($request);

   if ($form->isSubmitted() && $form->isValid()) {

    $TelefoneNumber->setGroupname($form->get('Groupname')->getData());
    $TelefoneNumber->setName($form->get('name')->getData());
    $TelefoneNumber->setSymbol($form->get('symbol')->getData());
    $TelefoneNumber->setLink($form->get('link')->getData());
    $em = $this->getDoctrine()->getManager();
    $em->persist($TelefoneNumber);
    $em->flush();
    $em = $this->getDoctrine()->getManager();
     return $this->redirectToRoute('ShowLinkDatatables');
   }
   return $this->render('AppBundle:Default:LinkListCreate.html.twig', array(
    'form' => $form->createView(),
));
}

    public function  NewLinkListeDatatablesAction(Request $request)
    {
        if( !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ACCOUNTING') && !$this->get('security.authorization_checker')->isGranted('ROLE_DISPOSITION') )
           {dump('Nicht admin');}
           else{
               $addresses = $this->getDoctrine()->getRepository('AppBundle:LinkList')->getallOrderByGroup();
                }
       // return $this->render('VehicleLogBundle:VehicleLog:TelefoneNumber.html.twig', array(
       //   'addresses' => $addresses,
       //   'employees_deleted' => $employees_deleted,
       //    'salaryRemainingDaysOfVacation' => $salaryRemainingDaysOfVacation,
       // ));
       $linkList = new LinkList();

       $form = $this->createForm('AppBundle\Form\LinkListType', $linkList);

       $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {
          $linkList->setGroupname($form->get('Groupname')->getData());
          $linkList->setName($form->get('name')->getData());
          $linkList->setSymbol($form->get('symbol')->getData());
          $linkList->setLink($form->get('link')->getData());
          $em = $this->getDoctrine()->getManager();
          $em->persist($linkList);
          $em->flush();
          $em = $this->getDoctrine()->getManager();
         return $this->redirectToRoute('ShowLinkDatatables');
       }
       return $this->render('AppBundle:Default:LinkListCreate.html.twig', array(
        'form' => $form->createView(),
    ));
    }


}
