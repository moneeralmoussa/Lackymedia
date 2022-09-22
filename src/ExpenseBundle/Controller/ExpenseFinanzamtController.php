<?php

namespace ExpenseBundle\Controller;

use Carbon\Carbon;
use ExpenseBundle\Entity\Expense;
use ExpenseBundle\Entity\Expensefinanzamt;
use TrimbleSoapBundle\Entity\Expenseview;
use ExpenseBundle\Form\ExpenseType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExpenseFinanzamtController extends Controller
{  
   public function expensesSaveAction($employee_id,$date,$expense1,$expense2,$expense3)
   {  
       $em = $this->getDoctrine()->getManager();
       $connection = $em->getConnection();
       $usr = $this->container->get('security.context')->getToken()->getUser();
       $date = (new \Datetime($date))->modify('first day of this month')->format('Y-m-d');
       $status =  $this->getDoctrine()->getRepository('ExpenseBundle:Expensefinanzamt')->findstatus($employee_id,$date);
       $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
       if(empty($status))
       {  $statement = $connection->prepare("DELETE FROM expensefinanzamtprimary WHERE date ='".$date."' AND employee_id ='".$employee_id."' AND by_employee_id !='".$usr->getEmployee()->getId()."'");
          $statement->execute();
          /*$results=$statement->fetchAll();
          dump($results);die();*/
          $expenseFinanzamt = new Expensefinanzamt();
          $expenseFinanzamt->setEmployee($employee);
          $expenseFinanzamt->setDate(new \Datetime($date));
          $expenseFinanzamt->setExpenses1($expense1);
          $expenseFinanzamt->setExpenses2($expense2);
          $expenseFinanzamt->setExpenses3($expense3);
          $expenseFinanzamt->setByEmployee($this->getUser()->getEmployee());
          $expenseFinanzamt->setCreateAt(new \Datetime());
          $em->persist($expenseFinanzamt);
          $em->flush();
         return new JsonResponse(true);
       }
       return new JsonResponse(false);
   }
   public function printAction( $employee_id, $date)
   { 
       $usr = $this->container->get('security.context')->getToken()->getUser();
       if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL')) {
           $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
           if ($employee->getId() != $usr->getEmployee()->getId()) {
               throw new AccessDeniedException();
           }
       }
       $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
       $expensesfinanzamt =  $this->getDoctrine()->getRepository('ExpenseBundle:Expensefinanzamt')->findAllByEmployeeId($employee_id,$date);
       $arbeitstag=0;
       $summe14=0;
       $summe28=0;
       $summe8=0;
       foreach ($expensesfinanzamt as $expensefinanzamt)
       {
        $summe14+=$expensefinanzamt->getExpenses1();
        $summe28+=$expensefinanzamt->getExpenses2();
        $summe8+=$expensefinanzamt->getExpenses3();
       }
       $betragInHöhe=($summe14*(14))+($summe28*(28))+($summe8*(8));
       return $this->render('ExpenseBundle:ExpenseNew:finanzamtbescheinigung.html.twig', array(
         //'expenses' => $this->loadWorkdaysByMonthAction($employee_id, $date),
          'employee' =>  $employee,
          'Expenses' => $expensesfinanzamt,
          'summe14' =>  $summe14,
          'summe28' =>  $summe28,
          'summe8' =>  $summe8,
          'betragInHöhe' =>  $betragInHöhe,
          'date' => $date
       ));
   }

   public function printMonthAction( $employee_id, $date)
   {   
       $usr = $this->container->get('security.context')->getToken()->getUser();
       if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL')) {
           $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
           if ($employee->getId() != $usr->getEmployee()->getId()) {
               throw new AccessDeniedException();
           }
       }
       $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
       $em = $this->getDoctrine()->getManager(); 
       $connection = $em->getConnection();
       $statement = $connection->prepare("  
       select * from expensefinanzamtprimary WHERE employee_id='".$employee_id."' AND date ='".$date."'");
       $statement->execute();
       $expenses=$statement->fetchAll();


       $absences = $this->getDoctrine()->getRepository('AbsenceBundle:AbsenceDetailClearing')->findByEmployeeId($employee_id, (new \DateTime($date))->format('Y-m-d'), (new \DateTime($date))->modify('midnight +1 month')->format('Y-m-d'));

       $holidays = $em->getRepository('AbsenceBundle:PublicHoliday')->findByYear((new \DateTime($date)));
       $expensesfinanzamt =  $this->getDoctrine()->getRepository('ExpenseBundle:Expensefinanzamt')->findstatus($employee_id,$date);
         
       return $this->render('ExpenseBundle:ExpenseNew:finanzamtMonth.html.twig', array(
          'employee' =>  $employee,
          'date' => $date,
          'expenses' => $expenses,
          'byEmployee' => $this->container->get('security.context')->getToken()->getUser()->getEmployee(),
          'absences' => $absences,
          'holidays' => $holidays,
          'Expense' => $expensesfinanzamt,
       ));
   }


   public function expensesDeleteAction($id)
   {  
       $em = $this->getDoctrine()->getManager();
       if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        { $expenseFinanzamt =  $this->getDoctrine()->getRepository('ExpenseBundle:Expensefinanzamt')->find($id);
          $em->remove($expenseFinanzamt);
          $em->flush();
         return new JsonResponse(true);}
       
       return new JsonResponse(false);
   }

}
