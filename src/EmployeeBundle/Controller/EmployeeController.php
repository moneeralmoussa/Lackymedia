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
use EmployeeBundle\Entity\WorkingHours;
use AbsenceBundle\Entity\AbsenceClearingRecords;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EmployeeBundle\Entity\User;
use LocationBundle\Entity\Location;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
/*
 * Employee controller.
 *
 */
class EmployeeController extends Controller
{
    public function indexAction()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $em = $this->getDoctrine()->getManager();
        $employees = $em->getRepository('EmployeeBundle:Employee')->getAllAvailable();
        $employees_deleted = $em->getRepository('EmployeeBundle:Employee')->getAllSoftDeleted();
        $salaryRemainingDaysOfVacation = [
            'date' => (new \DateTime())->format('t.m.Y'),
            'gl' => 0,
            'dialog' => 0,
        ];
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            foreach ($employees as $employee) {
                $salaryRemainingDaysOfVacation[(!is_null($employee->getDepartment()) && $employee->getDepartment() == 'Dialog')?'dialog':'gl'] += $this->getSalaryRemainingDaysOfVacation($employee);
            }
        }
        foreach($employees as $employee) {
            // Edit Salary
                $salary= $em->getRepository('EmployeeBundle:Salary')->findByEmployeeIdNewSalary($employee->getId());
                if($salary != Null  )
                if($salary[0]->getSalary() != $employee->getSalary())
                {
                    $Editemployee = $em->getRepository('EmployeeBundle:Employee')->find($employee->getId());
                    $Editemployee->setSalary($salary[0]->getSalary());
                    $em->persist($Editemployee);
                    $em->flush();
                }
            //

         $timenow = new \DateTime();
         if(!empty($employee->getEntryDate()))
         {
              $employee->workdaysnumber =  $timenow->diff($employee->getEntryDate())->format('%a');
              $employee->workdaysnumberstring  = sprintf('%07d', $employee->workdaysnumber );

              $diffYear= 0 ;
              $diffMonth = 0;
              $diffDay = 0;
              $back =false ;
              while($timenow > $employee->getEntryDate() )
              {

                $timenow->modify('-1 year');
                if($timenow >=  $employee->getEntryDate()) {$diffYear ++ ; }else {$back = true; }

              }
              if($back == true){$timenow->modify('+1 year');}
              $back =false ;
              while( $timenow >  $employee->getEntryDate())
              {
                $timenow->modify('-1 month');
                if($timenow >=  $employee->getEntryDate()) {$diffMonth ++ ; } else{$back = true; }
              }
              if($back == true){$timenow->modify('+1 month');}

             while($timenow >  $employee->getEntryDate())
              {
                $timenow->modify('-1 day');
                if($timenow > $employee->getEntryDate()) {$diffDay ++ ; }
              }
            $employee->workdaysnumber =  'Von '.$diffYear.' Jahren,'.$diffMonth.' Monaten,'.$diffDay.' Tagen';
             if(in_array($diffYear,array(4,9,14,19,24,29,34,39)))
              {  if($diffMonth == 11 & $diffDay > 15 )
                   {

                    $diffYear++;
                    $connection = $em->getConnection();
                    $statement = $connection->prepare("SELECT * FROM employeeinfo WHERE employee_id = ".$employee->getId()." and type = ".$diffYear."");
                    $statement->execute();
                    $result = $statement->fetchAll();
                    if(empty($result)){
                    $statement = $connection->prepare("
                    INSERT INTO employeeinfo (id,employee_id, type,create_at) VALUES (NULL,'".$employee->getId()."', '".$diffYear."', current_timestamp());
                    ");
                    $statement->execute();
                    if($diffYear == 10) $body = "Bitte Pralinen/ ein Blumenstrauß besorgen und ein Kugelschreiber mit Gravur kaufen und 500 € Prämie (im jeweiligen Monat bei der Lohnabrechnung berücksichtigen)." ;
                    if($diffYear == 20) $body = "Bitte Pralinen/ ein Blumenstrauß besorgen und 1.000 € Prämie (im jeweiligen Monat bei der Lohnabrechnung berücksichtigen).";
                    if($diffYear == 30) $body = "Bitte Pralinen/ ein Blumenstrauß besorgen und 1.500 € Prämie (im jeweiligen Monat bei der Lohnabrechnung berücksichtigen).";
                    if($diffYear == 40) $body = "Bitte Pralinen/ ein Blumenstrauß besorgen und 2.000 € Prämie (im jeweiligen Monat bei der Lohnabrechnung berücksichtigen).";
                    if($diffYear == 5 ) $body = "Bitte Pralinen/ ein Blumenstrauß besorgen und 1 Tag Urlaub gutschreiben.";
                    if($diffYear == 15) $body = "Bitte Pralinen/ ein Blumenstrauß besorgen und 1 Tag Urlaub gutschreiben.";
                    if($diffYear == 25) $body = "Bitte Pralinen/ ein Blumenstrauß besorgen und 1 Tag Urlaub gutschreiben.";
                    if($diffYear == 35) $body = "Bitte Pralinen/ ein Blumenstrauß besorgen und 1 Tag Urlaub gutschreiben.";
                        if(!empty($diffMonth)){
                            $to = array(
                                'st.laakmann@dialog-bz.de',
                            );
                            $cc = array(
                                //'hlaakmann@giesker-laakmann.de',
                                'malmoussa@giesker-laakmann.de',
                            );
                            $message = (new \Swift_Message('Erinnerungen zum Jubiläum'))
                                    ->setFrom('gl@361gradmedien.de')
                                    ->setTo($to)
                                    ->setCc($cc)
                                    ->setBcc('gl@361gradmedien.de')
                                    ->setBody('Hallo,<br> Mitarbeiter '.$employee->getFName().' hat ein '.$diffYear.' jahriges Jubiläum.<br>'.$body.'<br>Eintrittsdatum : '. $employee->getEntryDate()->format('d.m.Y') , 'text/html');
                            $this->get('mailer')->send($message);
                       }
                   }
                }
              }

           $employee->probezeitablaufdatum = clone $employee->getEntryDate();
           // $employee->probezeitablaufdatum =$employee->probezeitablaufdatum->modify('6 month')->format('t.m.Y');
           $dateA = clone($employee->probezeitablaufdatum);
           $dateB = clone($employee->probezeitablaufdatum);
           $plusMonths = clone($dateA->modify('6 Month'));
           //check whether reversing the month addition gives us the original day back
            if($dateB != $dateA->modify(6*-1 . ' Month')){
           //  $employee->probezeitablaufdatum = $plusMonths->modify('last day of last month');
           } elseif($employee->probezeitablaufdatum == $dateB->modify('last day of this month')){
           //   $employee->probezeitablaufdatum =  $plusMonths->modify('last day of this month');
           } else {
            $employee->probezeitablaufdatum = $plusMonths;
           }
           $employee->probezeitablaufdatum =$employee->probezeitablaufdatum->modify('-1 day')->format('d.m.Y');
          }
           else {$employee->workdaysnumber= '';     $employee->probezeitablaufdatum = '';  $employee->workdaysnumberstring = '' ; }
    }
        foreach($employees_deleted as $employee_deleted) {
            $timenow = new \DateTime();
            if(!empty($employee_deleted->getEntryDate()))
            { $employee_deleted->ausgeschieden =  $timenow->diff($employee_deleted->getDeletedAt())->format('%a');
              $employee_deleted->ausgeschiedenstring  = sprintf('%07d', $employee_deleted->ausgeschieden );
              $employee_deleted->ausgeschieden =  $timenow->diff($employee_deleted->getDeletedAt())->format('Von %y Jahren,%m Monaten,%d Tagen ausgeschieden');
              $deleteatdate=clone($employee_deleted->getDeletedAt());
              $employee_deleted->totalworkingdays =  $deleteatdate->diff($employee_deleted->getEntryDate())->format('%a');
              $employee_deleted->totalworkingdaysstring  = sprintf('%07d', $employee_deleted->totalworkingdays );
              $diffYear= 0 ;
              $diffMonth = 0;
              $diffDay = 0;
              $back =false ;
              while($deleteatdate > $employee_deleted->getEntryDate() )
              {
                $deleteatdate->modify('-1 year');
                if($deleteatdate >=  $employee_deleted->getEntryDate()) {$diffYear ++ ; }else {$back = true; }
              }
              if($back == true){$deleteatdate->modify('+1 year');}
              $back =false;
              while( $deleteatdate >  $employee_deleted->getEntryDate())
              {
                $deleteatdate->modify('-1 month');
                if($deleteatdate >=  $employee_deleted->getEntryDate()) {$diffMonth ++ ; } else{$back = true; }
              }
              if($back == true){$deleteatdate->modify('+1 month');}
              while($deleteatdate >  $employee_deleted->getEntryDate())
              {
                $deleteatdate->modify('-1 day');
                if($deleteatdate > $employee_deleted->getEntryDate()) {$diffDay ++ ; }
              }
              $deleteatdate=clone($employee_deleted->getDeletedAt());
              $employee_deleted->totalworkingdays =   $diffYear ." Jahren," . $diffMonth ." Monaten,".  $diffDay ." Tagen";
              $employee_deleted->probezeitablaufdatum = clone $employee_deleted->getEntryDate();
              $dateA = clone($employee_deleted->probezeitablaufdatum);
              $dateB = clone($employee_deleted->probezeitablaufdatum);
              $plusMonths = clone($dateA->modify('6 Month'));
              //check whether reversing the month addition gives us the original day back
               if($dateB != $dateA->modify(6*-1 . ' Month')){
              //  $employee->probezeitablaufdatum = $plusMonths->modify('last day of last month');
              } elseif($employee_deleted->probezeitablaufdatum == $dateB->modify('last day of this month')){
              //   $employee->probezeitablaufdatum =  $plusMonths->modify('last day of this month');
              } else {
               $employee_deleted->probezeitablaufdatum = $plusMonths;
              }
              $employee_deleted->probezeitablaufdatum =$employee_deleted->probezeitablaufdatum->modify('-1 day')->format('d.m.Y');
            }
            else
            {
                $employee_deleted->totalworkingdays = '' ;
                $employee_deleted->workdaysnumberstring = ''; $employee_deleted->totalworkingdaysstring  = '';     $employee_deleted->probezeitablaufdatum = '' ;
            }
        }

        /// send email
        $employeesBirthdays = $em->getRepository('EmployeeBundle:Employee')->getLatestBirthdaysOld();
        $timenow = new \Datetime();
        $timenow->modify('+10 day');
        foreach($employeesBirthdays as $employee_b) {
            $year = $timenow->diff($employee_b->getBirthday())->format('%y');
            if($year % 10 == 0){
            $connection = $em->getConnection();
            $statement = $connection->prepare("SELECT * FROM employeeinfo WHERE employee_id = ".$employee_b->getId()." and type = 'Runder Geburtstag ".$year."'");
            $statement->execute();
            $result = $statement->fetchAll();
            if(empty($result)){
            $statement = $connection->prepare("
            INSERT INTO employeeinfo (id,employee_id, type,create_at) VALUES (NULL,'".$employee_b->getId()."', 'Runder Geburtstag ".$year."', current_timestamp());
            ");
            $statement->execute();
                    $to = array(
                            'st.laakmann@dialog-bz.de',
                    );
                    $cc = array(
                         // 'hlaakmann@giesker-laakmann.de',
                         // 'slaakmann@giesker-laakmann.de',
                            'malmoussa@giesker-laakmann.de',
                    );
                    $message = (new \Swift_Message('Runder Geburtstag : '.$employee_b->getFName() ))
                            ->setFrom('gl@361gradmedien.de')
                            ->setTo($to)
                            ->setCc($cc)
                            ->setBcc('gl@361gradmedien.de')
                            ->setBody('Hallo,<br> Mitarbeiter '.$employee_b->getFName().' wird '. $year .' Jahre alt.<br>
                            Bitte eine handgeschriebene Glückwunschkarte schreiben.<br>
                            Für Frauen: Bluemnstrauß + 50 € Gutschein SPA <br>
                            Für Männer: Pralinen + 50 € Gutschein Restaurant  <br>
                            <br>Geburtsdatum : '. $employee_b->getBirthday()->format('d.m.Y') , 'text/html');
                    $this->get('mailer')->send($message);
               }
            }
        }

        ////
        return $this->render('EmployeeBundle:Employee:index.html.twig', array(
            'employees' => $employees,
            'employees_deleted' => $employees_deleted,
            'salaryRemainingDaysOfVacation' => $salaryRemainingDaysOfVacation,
        ));
    }


    /**
     * Creates a new employee entity.
     *
     */
     public function newAction(Request $request)
     {
         $employee = new Employee();
         $form = $this->_createForm(
             'EmployeeBundle\Form\NewEmployeeType',
             $employee,
             array('user' => $this->getUser())
         );
         $employeeService = $this->get('app.employee_service', $this->getDoctrine());
         $form->handleRequest($request);
         if ($form->isSubmitted() && $form->isValid()) {

                 $em = $this->getDoctrine()->getManager();
                 $employee->setSleepsInCompanyMeansSleepsAtHome(1);
                 $em->persist($employee);
                 $em->flush();
                 if (empty($employee->getUser()) && !$employee->isDeleted()) {
                     $sNewUsername = $employeeService->generateUsername($employee);
                     $lUser = array(
                             'username' => $sNewUsername,
                             'email' => empty($email)?$sNewUsername:$email,
                             'password' => $employeeService->generatePassword(),
                         );
                         $lNewUsers[] = array('user'=>$lUser, 'employee'=>$employee);
                         $user = new User();
                         $user->setUsername($lUser['username']);
                         $user->setEmail($lUser['email']);
                         $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $lUser['password']));
                         $user->setEnabled(true);
                         $user->setEmployee($employee);
                         $user->setRoles($form->get('roles')->getData());
                         $em->persist($user);
                         $em->flush();
                         $to = array(
                             'st.laakmann@dialog-bz.de',
                             'malmoussa@giesker-laakmann.de',
                         );
                         $message = (new \Swift_Message('neue Benutzer angelegt'))
                             ->setFrom('statistik@cloudmail.de')
                             ///->setBcc('malmoussa@giesker-laakmann.de')
                             ->setTo($to)
                             ->setBody(
                                 $this->renderView(
                                     'EmployeeBundle:Employee:newUserEmail.html.twig',
                                     array(
                                         'employee' => $employee,
                                         'email' => $lUser['email'],
                                         'user' => $lUser['username'],
                                         'password' => $lUser['password'],
                                     )
                                 ),'text/html');
                         $this->get('mailer')->send($message);
                 }
             return $this->redirectToRoute('employee_show', array('id' => $employee->getId()));
         }
         return $this->render('EmployeeBundle:Employee:new.html.twig', array(
             'employee' => $employee,
             'form' => $form->createView(),
         ));
     }

    /**
     * Finds and displays a employee entity.
     *
     */
    public function showAction(Employee $employee)
    {
        $deleteForm = $this->createDeleteForm($employee);
        $realRemainingDaysOfVacation = $this->getRealRemainingDaysOfVacation($employee);
        $salaryRemainingDaysOfVacation = $this->getSalaryRemainingDaysOfVacation($employee);

        $em = $this->getDoctrine()->getManager();
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
           $workingtimes = $this->getDoctrine()->getRepository('EmployeeBundle:WorkingHours')->findBy(array('employeeId' => $employee->getId()));
         }
          $salarys = $this->getDoctrine()->getRepository('EmployeeBundle:Salary')->findByEmployeeId($employee->getId());
          $files = $em->getRepository('EmployeeBundle:Files')->findByEmployee($employee);
          $holidayschedule = $em->getRepository('AbsenceBundle:Holidayschedule')->findByEmployee($employee);

          return $this->render('EmployeeBundle:Employee:show.html.twig', array(
              'employee' => $employee,
              'delete_form' => $deleteForm->createView(),
              'realRemainingDaysOfVacation' => $realRemainingDaysOfVacation,
              'salaryRemainingDaysOfVacation' => $salaryRemainingDaysOfVacation,
              'workingtimes' => $workingtimes,
              'files' => $files,
              'salarys' => $salarys,
              'holidayschedule' => $holidayschedule
          ));
    }

    protected function _createForm($formtype, $employee, $options=array())
    {
        $form = $this->createForm($formtype, $employee, $options);

        if (!empty($employee->getUser()) && $employee->getUser()->isEnabled() && $form->has('isUser')) {
            $form->get('isUser')->setData(true);
        }

        return $form;
    }

    protected function _createUser(Form $form, Employee $employee)
    {
        $user = new User();
        $user->setUsername($form->get('username')->getData());
        $user->setEmail($form->get('email')->getData());
        $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $form->get('password')->getData()));
        $user->setEnabled(true);

        $user->setEmployee($employee);
        $this->getDoctrine()->getManager()->persist($user);
        return $user;
    }

    protected function _createUserNoForm($lUser, Employee $employee)
    {
        $user = new User();
        $user->setUsername($lUser['username']);
        $user->setEmail($lUser['email']);
        $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $lUser['password']));
        $user->setEnabled(true);

        $user->setEmployee($employee);
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $user;
    }

    /**
     * Displays a form to edit an existing employee entity.
     *
     */
     public function editAction(Request $request, Employee $employee)
    {
     $salaryvalue=$employee->getSalary();
     $deleteForm = $this->createDeleteForm($employee);
     $editForm = $this->_createForm(
         'EmployeeBundle\Form\EmployeeType',
         $employee,
         array('user' => $this->getUser())
     );

     $editForm->handleRequest($request);


     if ($editForm->isSubmitted() && $editForm->isValid()) {
         if ($editForm->has('isUser') && $editForm->get('isUser')->getData()) {
             if (empty($employee->getUser())) {
                 $this->_createUser($editForm, $employee);
             } else {
                 $employee->getUser()->setEnabled(true);
                 $employee->getUser()->setRoles($editForm->get('roles')->getData());
                 if (!empty($editForm->get('password')->getData())) {
                     $employee->getUser()->setPassword($this->get('security.password_encoder')->encodePassword($employee->getUser(), $editForm->get('password')->getData()));
                 }

                 if($editForm->has('salary') && ($editForm->get('salary')->getData() != $salaryvalue)){
                 if( ($editForm->has('salary')  && !empty($editForm->get('fromdate')->getData()) &&  $editForm->get('fromdate')->getData() <= date_modify(new \DateTime('now'), '+1 day') && !empty($editForm->get('todate')->getData()) && $editForm->get('todate')->getData() > new \DateTime('now') )
                 || ($editForm->has('salary')  && !empty($editForm->get('fromdate')->getData()) &&  $editForm->get('fromdate')->getData() <= date_modify(new \DateTime('now'), '+1 day') && empty($editForm->get('todate')->getData()) ) )
                 {   // save date and salary
                         $salary = new Salary();
                         $salary->setSalary($editForm->get('salary')->getData());
                         $salary->setEmployeeId($employee->getId());
                         $salary->setComment($editForm->get('commentsalary')->getData());
                         $salary->setFromDate($editForm->get('fromdate')->getData());
                         $salary->setToDate($editForm->get('todate')->getData());
                         $salary->setCreateAt(new \DateTime());
                         $em = $this->getDoctrine()->getManager();
                         $em->persist($salary);
                         $em->flush();
                 }else{
                         $salary = new Salary();
                         $salary->setSalary($editForm->get('salary')->getData());
                         $salary->setEmployeeId($employee->getId());
                         $salary->setComment($editForm->get('commentsalary')->getData());
                         $salary->setFromDate($editForm->get('fromdate')->getData());
                         $salary->setToDate($editForm->get('todate')->getData());
                         $salary->setCreateAt(new \DateTime());
                         $em = $this->getDoctrine()->getManager();
                         $em->persist($salary);
                         $em->flush();
                         $employee->setSalary($salaryvalue);
                         }
                     }

             }
         } elseif ($editForm->has('isUser') && !$editForm->get('isUser')->getData() && !empty($employee->getUser())) {
             $employee->getUser()->setEnabled(false);
        }
         $this->getDoctrine()->getManager()->flush();
       return $this->redirectToRoute('employee_edit', array('id' => $employee->getId()));
     } elseif ($editForm->has('isUser') && !$editForm->isSubmitted() && !empty($employee->getUser())) {
       $editForm->get('roles')->setData($employee->getUser()->getRoles());
     }
     $salarys = $this->getDoctrine()->getRepository('EmployeeBundle:Salary')->findByEmployeeId($employee->getId());
     return $this->render('EmployeeBundle:Employee:edit.html.twig', array(
         'employee' => $employee,
         'edit_form' => $editForm->createView(),
         'salarys' => $salarys,
         'delete_form' => $deleteForm->createView(),
     ));
 }
    /**
     * Deletes a employee entity.
     *
     */
    public function deleteAction(Request $request, Employee $employee)
    {
        $form = $this->createDeleteForm($employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $employee->setDeletedAt(new \DateTime());
            $em->persist($employee);
            //$em->remove($employee);
            $em->flush();
        }

        return $this->redirectToRoute('employee_index');
    }

    /**
     * Creates a form to delete a employee entity.
     *
     * @param Employee $employee The employee entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Employee $employee)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('employee_delete', array('id' => $employee->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    public function homeUpdateAction(Request $request)
    {
        $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($request->get("employee"));
        $home = $request->get("home");

        $nonremove = [];
        foreach ($home["geofences"] as $geofence) {
            if (!empty($geofence["id"])) {
                $nonremove[] = $geofence["id"];
            }
        }

        foreach ($employee->getGeofences() as $geofence) {
            if (!in_array((string)$geofence->getId(), $nonremove)) {
                $employee->removeGeofence($geofence);
                $geofence->setEmployee(null);
                $geofence->setDeletedAt(new \DateTime());
            }
        }

        foreach ($home["geofences"] as $geofence) {
            if (!empty($geofence["id"])) {
                $location = $this->getDoctrine()->getRepository('LocationBundle:Location')->find($geofence["id"]);
                $location->setLat($geofence["latLng"]["lat"]);
                $location->setLon($geofence["latLng"]["lng"]);
                $location->setGeofenceMeters($geofence["radius"]);
            } else {
                $location = new Location();
                $location->setLocationtype($this->getDoctrine()->getRepository('LocationBundle:Locationtype')->find(4));
                $location->setLat($geofence["latLng"]["lat"]);
                $location->setLon($geofence["latLng"]["lng"]);
                $location->setGeofenceMeters($geofence["radius"]);
                $location->setName('Wohnort '.$employee->getFullname());
                $location->setStreet($employee->getStreet());
                $location->setZipCode($employee->getZipCode());
                $location->setTown($employee->getTown());
                $location->setEmployee($employee);
                $employee->addGeofence($location);
                $this->getDoctrine()->getManager()->persist($location);
            }
        }

        $employee->setLat($geofence["latLng"]["lat"]);
        $employee->setLon($geofence["latLng"]["lng"]);
        $employee->setGeofenceMeters($geofence["radius"]);

        $this->getDoctrine()->getManager()->flush();

        return new Response('New geofence for Employee '.$employee->getId());
    }

    public function updateDaysOfVacationAction(Request $request)
    {
        $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($request->get("employee"));
        $year = (int)$request->get("year");
        $currentAbsence = false;


        $em = $this->getDoctrine()->getManager();

        foreach ($employee->getAbsenceClearings() as $absenceClearing) {
            if ($absenceClearing->getYear() == $year) {
                $currentAbsence = true;
                $remaining  = $request->get("remaining");
                $substract  = $request->get("substract");
                $additional = $request->get("additinal");
                $comment = $request->get("comment");
                $comment2 = $request->get("comment2");
                if (!empty($remaining)) {
                    $absenceClearing->setRemainingDaysOfVacation($remaining);
                }
                if (is_numeric($substract)) {
                    $absenceClearing->setSubstractDaysOfVacation($absenceClearing->getSubstractDaysOfVacation()+$substract);
                }
                if (is_numeric($additional)) {
                    $absenceClearing->setAdditionalDaysOfVacation($absenceClearing->getAdditionalDaysOfVacation()+$additional);
                }
                if (!empty($comment)) {
                    $absenceClearing->setComment($comment);
                }
                if (!empty($comment2)) {
                    $absenceClearing->setComment2($comment2);
                    }
                    $saverecordabsenceClearing = $absenceClearing;
                }
              }
        if (!$currentAbsence) {
            $absenceClearing = new AbsenceClearing();
            $remaining  = $request->get("remaining");
            $substract  = $request->get("substract");
            $additional = $request->get("additinal");
            $comment = $request->get("comment");
            $comment2 = $request->get("comment2");
            $absenceClearing->setYear($year);
            $absenceClearing->setEmployee($employee);
            if (!empty($remaining)) {
                $absenceClearing->setRemainingDaysOfVacation($remaining);
            }
            if (is_numeric($substract)) {
                $absenceClearing->setSubstractDaysOfVacation($substract);
            }
            if (is_numeric($additional)) {
                $absenceClearing->setAdditionalDaysOfVacation($additional);
            }
            if (!empty($comment)) {
                $absenceClearing->setComment($comment);
            }
            if (!empty($comment2)) {
                $absenceClearing->setComment2($comment2);
            }
            $employee->addAbsenceClearing($absenceClearing);
            $em->persist($employee);
            $saverecordabsenceClearing = $absenceClearing;
        }
        $em->persist($absenceClearing);
        $em->flush();
        // add to Archive
        if (is_numeric($additional)) {
          $this->saveAbsenceClearingRecorde($employee,$saverecordabsenceClearing,'AdditionalDaysOfVacation',$additional,$comment);
        }
        if (is_numeric($substract)) {
          $this->saveAbsenceClearingRecorde($employee,$saverecordabsenceClearing,'SubstractDaysOfVacation',$substract,$comment2);
        }
        return new Response('Updated days of vacation for Employee '.$employee->getId());
    }
    public function saveAbsenceClearingRecorde($employee,$absenceClearing,$typeString,$value,$comment)
    {
      $em = $this->getDoctrine()->getManager();
      $absenceClearingRecords =new AbsenceClearingRecords();
      $absenceClearingRecords->setEmployee($employee);
      $absenceClearingRecords->setAbsenceClearing($absenceClearing);
      $absenceClearingRecords->setType($typeString);
      $absenceClearingRecords->setValue($value);
      $absenceClearingRecords->setComment($comment);
      $absenceClearingRecords->setCreateAt(new \Datetime());
      $em->persist($absenceClearingRecords);
      $em->flush();
    }

    public function weihnachtenAction()
    {
      die();
        $currentAbsence = false;
        $i=0;
        $em = $this->getDoctrine()->getManager();
        $employees = $em->getRepository('EmployeeBundle:Employee')->getAllAvailable();
        foreach ($employees as $employee)
        if ($employee->getDepartment() !="Gesellschafter"  && $employee->getDepartment() !="Aushilfe" && $employee->getDepartment() !="Dialog" )
       { $Dataweilnacht =$i++ . " " . $employee->getName() . " Abteilung : " . $employee->getDepartment();
        foreach ($employee->getAbsenceClearings() as $absenceClearing) {
            if ($absenceClearing->getYear() == '2020') {
                $currentAbsence = true;
                echo($Dataweilnacht ." Alter_Wert:" . $absenceClearing->getAdditionalDaysOfVacation() ." ");
                 $absenceClearing->setAdditionalDaysOfVacation($absenceClearing->getAdditionalDaysOfVacation()+3);
                 $absenceClearing->setComment2($absenceClearing->getComment2()." +3 Weihnachtsgeld");
                 echo(" Neuer_Wert:" . $absenceClearing->getAdditionalDaysOfVacation() ."<br>");
                 }
            }
            $em->persist($absenceClearing);
            $em->flush();
        }
        die();
    }

    public function getRealRemainingDaysOfVacation($employee, $date=null)
    {
        if (empty($date)) {
            $date= new \DateTime();
        }

        $holidayNew = is_null($employee->getContract()) ? 0 : $employee->getContract()->getHolidays();
        $remainingOld = $employee->getRemainingDaysOfVacation();

        $substract = 0;
        $additional = 0;

        foreach ($employee->getAbsenceClearings() as $absenceClearing) {
            if ($absenceClearing->getYear() == (int)$date->format('Y')) {
                $substract = $absenceClearing->getSubstractDaysOfVacation();
                $additional = $absenceClearing->getAdditionalDaysOfVacation();
                $remainingOld = (empty($absenceClearing->getRemainingDaysOfVacation())?$remainingOld:$absenceClearing->getRemainingDaysOfVacation());
            }
        }

        $holiday = $holidayNew + $remainingOld - $substract + $additional;

        $date_temp = clone $date;
        return $holiday - $this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getSumHolidaysByEmployeeDate($employee, $date->format('Y').'-01-01', $date_temp->modify('first day of next month')->format('Y-m-d'));
    }

    public function getSalaryRemainingDaysOfVacation($employee, $date=null)
    {
        if (empty($date)) {
            $date= new \DateTime();
        }
        // $realRemainingDaysOfVacation = $this->getRealRemainingDaysOfVacation($employee, $date);
        $start = Carbon::instance($date)->startOfYear();
        $end = Carbon::instance($date)->endOfMonth();
        $year = Carbon::instance($date)->year;
        $absenceService = $this->get('app.absence_service', $this->getDoctrine());
        $realRemainingDaysOfVacation = $absenceService->getRemainingMtl2($employee->getId(), $start, $end, $year)['remainingmtl'];
        // يرجع عدد الاجازات الباقية الى اخر السنة سواء المحجوزة او لاء اي يرجع الاجازات التي يمكن تفعيلها
        return ($realRemainingDaysOfVacation - $employee->getTheoreticRemainingDaysOfVacation($date)) * $employee->getDailySalary();
    }

    public function jsonSalaryRemainingDaysOfVacationAction($date)
    {
        if (
            !($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        ) {
            throw new AccessDeniedException();
        }

        $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllAvailable();

        $date= new \DateTime($date);
        $salaryRemainingDaysOfVacation = [
            'date' => $date->format('t.m.Y'),
            'gl' => 0,
            'dialog' => 0,
        ];

        foreach ($employees as $employee) {
            $salaryRemainingDaysOfVacation[(!is_null($employee->getDepartment()) && $employee->getDepartment() == 'Dialog')?'dialog':'gl'] += $this->getSalaryRemainingDaysOfVacation($employee, $date);
        }

        return new JsonResponse($salaryRemainingDaysOfVacation);
    }

    public function jsonSalaryRemainingDaysOfVacationDepartmentAction($date)
    {
        if (
            !($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        ) {
            throw new AccessDeniedException();
        }

        $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllAvailable();

        $date= new \DateTime($date);
        $salaryRemainingDaysOfVacation = [
            'date' => $date->format('t.m.Y'),
            'department' => [],
        ];
        foreach ($employees as $employee) {
            if (!is_null($employee->getDepartment())) {
                if (!array_key_exists($employee->getDepartment()->getName(), $salaryRemainingDaysOfVacation['department'])) {
                    $salaryRemainingDaysOfVacation['department'][$employee->getDepartment()->getName()]['sum'] = 0;
                }
                $salaryRemainingDaysOfVacation['department'][$employee->getDepartment()->getName()]['employee'][$employee->getName()]['salary'] = $this->getSalaryRemainingDaysOfVacation($employee, $date);
                $salaryRemainingDaysOfVacation['department'][$employee->getDepartment()->getName()]['employee'][$employee->getName()]['holidays'] = $this->getSalaryRemainingDaysOfVacation($employee, $date);
                $salaryRemainingDaysOfVacation['department'][$employee->getDepartment()->getName()]['sum'] += $this->getSalaryRemainingDaysOfVacation($employee, $date);
            }
        }
        return new JsonResponse($salaryRemainingDaysOfVacation);
    }

    public function jsonEmployeeAction(Request $request)
    {
        $year = Carbon::parse($request->query->get('start'))->year;
        $type = $request->query->get('type');
        if($type == 'AllAvailable')
        $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllAvailable();
        else
        $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->findAll();
        $arrayCollection = array();
        $departments = array();
        $employeecount = 0;
        foreach ($employees as $employee) {
            if ($employee->isDeleted()) {
                $department = 'ausgeschiedene Mitarbeiter';
            } else {
                $department = is_null($employee->getDepartment()) ? 'nicht Zugeordnet' : $employee->getDepartment()->getName();
                $employeecount++;
            }
            if (!array_key_exists($department, $departments)) {
                $departments[$department] = 0;
            }
            $departments[$department]++;
        }
        foreach ($employees as $employee) {
            $department = $employee->isDeleted() ? 'ausgeschiedene Mitarbeiter' : (is_null($employee->getDepartment()) ? 'nicht Zugeordnet' : $employee->getDepartment()->getName());
            $absenceclearing = $this->getDoctrine()->getRepository('EmployeeBundle:AbsenceClearing')->findOneBy([
                'employee' => $employee,
                'year' => $year,
            ]);
            $absenceService = $this->get('app.absence_service', $this->getDoctrine());
            $holidays = $absenceService->getHolidays($year, $employee);
            $education_year = null;
            if (strpos($employee->getPrename(), 'Lehrjahr') !== false) {
                $education_year = intval(substr($employee->getPrename(), 0, 1));
            }
            $salary = null;
            if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                if (function_exists('money_format')) {
                    $salary = money_format('%.2n', $this->getSalaryRemainingDaysOfVacation($employee));
                } else {
                    $salary = sprintf('%01.2f', $this->getSalaryRemainingDaysOfVacation($employee));
                }
            }
            $arrayCollection[] = array(
                'id'          	=> $employee->getId(),
                'pNumber'     	=> $employee->getTrimbleId(),
                'holiday'     	=> sprintf("%.1f", ($holidays['contract'] + $holidays['remaining'] + $holidays['additional'] - $holidays['substract'])),
                'remainingOld'  => sprintf("%.1f", $holidays['remaining']),
                'holidayNew'    => sprintf("%.1f", $holidays['contract']),
                'remaining'   	=> sprintf("%.1f", $holidays['sum']),
                'employee'    	=> $employee->getName(),
                'address'     	=> $employee->getAddress(),
                'department'  	=> $department." (".$departments[$department].")",
                'substract'   	=> sprintf("%.1f", $holidays['substract']),
                'additional'  	=> sprintf("%.1f", $holidays['additional']),
                'comment'     	=> $absenceclearing ? $absenceclearing->getComment() : '',
                'comment2'    	=> $absenceclearing ? $absenceclearing->getComment2() : '',
                'education_year' => $education_year,
                'salaryRemainingDaysOfVacation' => $salary,
            );
            if (!$employee->isDeleted()) {
                $arrayCollection[] = array(
                    'id'          	=> "alle".$employee->getId(),
                    'pNumber'     	=> $employee->getTrimbleId(),
                    'holiday'     	=> sprintf("%.1f", ($holidays['contract'] + $holidays['remaining'] + $holidays['additional'] - $holidays['substract'])),
                    'remainingOld'  => sprintf("%.1f", $holidays['remaining']),
                    'holidayNew'    => sprintf("%.1f", $holidays['contract']),
                    'remaining'   	=> sprintf("%.1f", $holidays['sum']),
                    'employee'    	=> $employee->getName(),
                    'address'     	=> $employee->getAddress(),
                    'department'  	=> "alle Mitarbeiter (".$employeecount.")",
                    'substract'   	=> sprintf("%.1f", $holidays['substract']),
                    'additional'  	=> sprintf("%.1f", $holidays['additional']),
                    'comment'     	=> $absenceclearing ? $absenceclearing->getComment() : '',
                    'comment2'    	=> $absenceclearing ? $absenceclearing->getComment2() : '',
                    'education_year' => $education_year,
                    'salaryRemainingDaysOfVacation' => $salary,
                );
            }
            /*if (!in_array($department, $departments)) {
                $departments[] = $department;
            }*/
        }
        return new JsonResponse($arrayCollection);
    }

    public function importEmpoyeesXlsxAction(Request $request)
    {
        if ($request->get('step')==2) {
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            $errors=[];
            $em = $this->getDoctrine()->getManager();

            $employeeService = $this->get('app.employee_service', $this->getDoctrine());

            $inserted = 0;
            $lNewUsers = array();
            $lDeletedUsers = array();
            $durchlauf=0;
            $orderedByOldId = [];

            foreach ($request->request->all() as $import_id=>$old_id) {
                if (strpos($import_id, "employee_")!==false) {
                    if (!array_key_exists($old_id, $orderedByOldId)) {
                        $orderedByOldId[$old_id] = [];
                    }
                    $orderedByOldId[$old_id][] = explode("_", $import_id)[1];
                }
            }
            foreach ($orderedByOldId as $old_id=>$import_ids) {
                $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($old_id);
                $employee_imports = $this->getDoctrine()->getRepository('EmployeeBundle:EmployeeImport')->findBy(['id'=>$import_ids], ['deleted_at'=>'ASC']);
                if ($old_id == -1) {
                    foreach ($employee_imports as $employee_import) {
                        array_push($errors, $employee_import->getId()."=>".$old_id.": nicht anlegen");
                        $em->remove($employee_import);
                    }
                    $em->flush();
                    continue;
                }
                if (is_null($employee)) {
                    $employee = new Employee();
                    $old_employee = null;
                } else {
                    $old_employee = [
                        'employee_id' => $employee,
                        'department_id' => $employee->getDepartment(),
                        'contract_id' => $employee->getContract(),
                        'komalog_id' => $employee->getKomalogId(),
                        'trimble_id' => $employee->getTrimbleId(),
                        'country_code' => $employee->getCountryCode(),
                        'name' => $employee->getName(),
                        'prename' => $employee->getPrename(),
                        'salutation' => $employee->getSalutation(),
                        'street' => $employee->getStreet(),
                        'zip_code' => $employee->getZipCode(),
                        'town' => $employee->getTown(),
                        'phone' => $employee->getPhone(),
                        'fax' => $employee->getFax(),
                        'mobile' => $employee->getMobile(),
                        'birthday' => $employee->getBirthday(),
                        'vehicle_log_blocked' => $employee->getVehicleLogBlocked(),
                        'entry_date' => $employee->getEntryDate(),
                        'initial' => $employee->getInitial(),
                        'deleted_at' => $employee->getDeletedAt(),
                        'lat' => $employee->getLat(),
                        'lon' => $employee->getLon(),
                        'geofence_meters' => $employee->getGeofenceMeters(),
                        'sleeps_in_company_means_sleeps_at_home' => $employee->getSleepsInCompanyMeansSleepsAtHome(),
                        'salary' => $employee->getSalary(),
                        'remaining_days_of_vacation' => $employee->getRemainingDaysOfVacation(),
                        'usual_home_travel_hours' => $employee->getUsualHomeTravelHours(),
                    ];
                }
                //try {
                $email= null;
                foreach ($employee_imports as $employee_import) {
                    try {
                        if ($old_id == 0) {
                            if ($employee_import->isDeleted()) {
                                array_push($errors, $employee_import->getId()."=>".$old_id.": neu anlegen, aber is deleted");
                                $em->remove($employee_import);
                                $em->flush();
                                continue;
                            }
                            array_push($errors, $employee_import->getId()."=>".$old_id.": neu anlegen");
                            $employee = new Employee();
                            $email = null;
                        } else {
                            array_push($errors, $employee_import->getId()."=>".$old_id.": bestehende verwenden");
                        }
                        if (!empty($employee_import->getTrimbleId())) {
                            $employee->setTrimbleId($employee_import->getTrimbleId());
                        }

                        if (strtolower($employee->getStreet().", ".$employee->getZipCode()." ".$employee->getTown()) != strtolower($employee_import->getStreet().", ".$employee_import->getZipCode()." ".$employee_import->getTown())) {
                            $employee->setLat(null);
                            $employee->setLon(null);
                        }

                        if (!empty($employee_import->getInitial())) {
                            $employee->setInitial($employee_import->getInitial());
                        }
                        $employee->setName($employee_import->getName());
                        if (!empty($employee_import->getPrename())) {
                            $employee->setPrename($employee_import->getPrename());
                        }
                        if (!empty($employee_import->getSalutation())) {
                            $employee->setSalutation($employee_import->getSalutation());
                        }
                        if (!empty($employee_import->getStreet())) {
                            $employee->setStreet($employee_import->getStreet());
                        }
                        if (!empty($employee_import->getCountryCode())) {
                            $employee->setCountryCode($employee_import->getCountryCode());
                        }
                        if (!empty($employee_import->getZipCode())) {
                            $employee->setZipCode($employee_import->getZipCode());
                        }
                        if (!empty($employee_import->getTown())) {
                            $employee->setTown($employee_import->getTown());
                        }
                        if (!empty($employee_import->getPhone())) {
                            $employee->setPhone($employee_import->getPhone());
                        }
                        if (!empty($employee_import->getFax())) {
                            $employee->setFax($employee_import->getFax());
                        }
                        if (!empty($employee_import->getMobile())) {
                            $employee->setMobile($employee_import->getMobile());
                        }
                        if (!empty($employee_import->getEmail())) {
                            $employee->setEmailPrivate($employee_import->getEmail());
                        }
                        if (!empty($employee_import->getBirthday())) {
                            $employee->setBirthday($employee_import->getBirthday());
                        }
                        if (!empty($employee_import->getEntryDate())) {
                            $employee->setEntryDate($employee_import->getEntryDate());
                        }
                        if (!empty($employee_import->getDeletedAt())) {
                            $employee->setDeletedAt($employee_import->getDeletedAt());
                        }
                        if (is_null($employee->getSleepsInCompanyMeansSleepsAtHome())) {
                            $employee->setSleepsInCompanyMeansSleepsAtHome(1);
                        }
                        $email = empty($employee_import->getEmail())?$email:$employee_import->getEmail();
                        $em->remove($employee_import);
                        if ($old_id == 0) {
                            $em->persist($employee);
                            $em->flush();
                            $inserted += 1;

                            $employeeService->geocodeEmployee($employee);
                            if (empty($employee->getUser()) && !$employee->isDeleted()) {
                                $sNewUsername = $employeeService->generateUsername($employee);
                                $lUser = array(
                                        'username' => $sNewUsername,
                                        'email' => empty($email)?$sNewUsername:$email,
                                        'password' => $employeeService->generatePassword(),
                                    );
                                $lNewUsers[] = array('user'=>$lUser, 'employee'=>$employee);
                                $this->_createUserNoForm($lUser, $employee);
                            } elseif ($employee->getUser() && $employee->isDeleted()) {
                                $lDeletedUsers[] = $employee->getUser()->getUsername();
                                $employee->setUser();
                                $em->flush();
                            }
                        }
                    } catch (\Exception $e) {
                        array_push($errors, $import_id."=>".$old_id.": innen");//." (".$durchlauf.")");
                    }
                }
                if ($old_id != 0) {
                    $em->persist($employee);
                    $em->flush();
                    $inserted += 1;

                    $employeeService->geocodeEmployee($employee);
                    if (empty($employee->getUser()) && !$employee->isDeleted()) {
                        $sNewUsername = $employeeService->generateUsername($employee);
                        $lUser = array(
                                'username' => $sNewUsername,
                                'email' => empty($email)?$sNewUsername:$email,
                                'password' => $employeeService->generatePassword(),
                            );
                        $lNewUsers[] = array('user'=>$lUser, 'employee'=>$employee);
                        $this->_createUserNoForm($lUser, $employee);
                    } elseif ($employee->getUser() && $employee->isDeleted()) {
                        $lDeletedUsers[] = $employee->getUser()->getUsername();
                        $employee->getUser()->setPassword($employeeService->generatePassword());
                        $em->flush();
                        $employee->setUser();
                        $em->flush();
                    }

                    if (!empty($old_employee)) {
                        $new_employee = [
                                'employee_id' => $employee,
                                'department_id' => $employee->getDepartment(),
                                'contract_id' => $employee->getContract(),
                                'komalog_id' => $employee->getKomalogId(),
                                'trimble_id' => $employee->getTrimbleId(),
                                'country_code' => $employee->getCountryCode(),
                                'name' => $employee->getName(),
                                'prename' => $employee->getPrename(),
                                'salutation' => $employee->getSalutation(),
                                'street' => $employee->getStreet(),
                                'zip_code' => $employee->getZipCode(),
                                'town' => $employee->getTown(),
                                'phone' => $employee->getPhone(),
                                'fax' => $employee->getFax(),
                                'mobile' => $employee->getMobile(),
                                'birthday' => $employee->getBirthday(),
                                'vehicle_log_blocked' => $employee->getVehicleLogBlocked(),
                                'entry_date' => $employee->getEntryDate(),
                                'initial' => $employee->getInitial(),
                                'deleted_at' => $employee->getDeletedAt(),
                                'lat' => $employee->getLat(),
                                'lon' => $employee->getLon(),
                                'geofence_meters' => $employee->getGeofenceMeters(),
                                'sleeps_in_company_means_sleeps_at_home' => $employee->getSleepsInCompanyMeansSleepsAtHome(),
                                'salary' => $employee->getSalary(),
                                'remaining_days_of_vacation' => $employee->getRemainingDaysOfVacation(),
                                'usual_home_travel_hours' => $employee->getUsualHomeTravelHours(),
                            ];
                        $old_diffs_new = 0;
                        foreach ($new_employee as $key => $value) {
                            if ($old_employee[$key] != $value) {
                                $old_diffs_new++;
                            }
                        }
                        if ($old_diffs_new>0) {
                            $employee_archiv = new EmployeeArchiv();
                            $employee_archiv->setEmployee($old_employee['employee_id']);
                            $employee_archiv->setDepartment($old_employee['department_id']);
                            $employee_archiv->setContract($old_employee['contract_id']);
                            $employee_archiv->setKomalogId($old_employee['komalog_id']);
                            $employee_archiv->setTrimbleId($old_employee['trimble_id']);
                            $employee_archiv->setCountryCode($old_employee['country_code']);
                            $employee_archiv->setName($old_employee['name']);
                            $employee_archiv->setPrename($old_employee['prename']);
                            $employee_archiv->setSalutation($old_employee['salutation']);
                            $employee_archiv->setStreet($old_employee['street']);
                            $employee_archiv->setZipCode($old_employee['zip_code']);
                            $employee_archiv->setTown($old_employee['town']);
                            $employee_archiv->setPhone($old_employee['phone']);
                            $employee_archiv->setFax($old_employee['fax']);
                            $employee_archiv->setMobile($old_employee['mobile']);
                            $employee_archiv->setBirthday($old_employee['birthday']);
                            $employee_archiv->setVehicleLogBlocked($old_employee['vehicle_log_blocked']);
                            $employee_archiv->setEntryDate($old_employee['entry_date']);
                            $employee_archiv->setInitial($old_employee['initial']);
                            $employee_archiv->setDeletedAt($old_employee['deleted_at']);
                            $employee_archiv->setLat($old_employee['lat']);
                            $employee_archiv->setLon($old_employee['lon']);
                            $employee_archiv->setGeofenceMeters($old_employee['geofence_meters']);
                            $employee_archiv->setSleepsInCompanyMeansSleepsAtHome($old_employee['sleeps_in_company_means_sleeps_at_home']);
                            $employee_archiv->setSalary($old_employee['salary']);
                            $employee_archiv->setRemainingDaysOfVacation($old_employee['remaining_days_of_vacation']);
                            $employee_archiv->setUsualHomeTravelHours($old_employee['usual_home_travel_hours']);
                            $em->persist($employee_archiv);
                            $em->flush();
                        }
                    }
                }
                //} catch (\Exception $e) {
                //    array_push($errors, $import_id."=>".$old_id.": aussen");
                //}
            }
            // $this->get('session')->getFlashBag()->add('errors', $errors);

            if (count($lNewUsers)>0 || count($lDeletedUsers)>0) {
                $to = array(
                  'st.laakmann@dialog-bz.de',
                  'malmoussa@giesker-laakmann.de',
                );

                $message = (new \Swift_Message(count($lNewUsers).' neue Benutzer angelegt'))
                    ->setFrom('gl@361gradmedien.de')
                    ->setBcc('gl@361gradmedien.de')
                    ->setTo($to)
                    ->setBody(
                        $this->renderView(
                            'EmployeeBundle:Employee:newUserEmail.html.twig',
                            array(
                                'newUsers' => $lNewUsers,
                                'deletedUsers' => $lDeletedUsers,
                            )
                        ),
                        'text/html'
                    )
                    /*
                     * If you also want to include a plaintext version of the message
                    ->addPart(
                        $this->renderView(
                            'Emails/registration.txt.twig',
                            array('name' => $name)
                        ),
                        'text/plain'
                    )
                    */
                ;

                $this->get('mailer')->send($message);
            }

            return $this->render('EmployeeBundle:Employee:import.html.twig', array(
                'step' => 3,
                'inserted' => $inserted,
            ));
        } elseif (!is_null($request->files->get('excelfile'))) {
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            $errors=[];
            $em = $this->getDoctrine()->getManager();
            $con = $em->getConnection();
            $plat = $con->getDatabasePlatform();
            $con->executeUpdate($plat->getTruncateTableSQL('employee_import', true));

            $uploadService = $this->get('app.upload_service');
            $fileName = $uploadService->upload($request->files->get('excelfile'));

            $myXlsLoader = $uploadService->xlsLoader($fileName);

            $lColumnNames = $myXlsLoader[0];
            $objPHPExcel = $myXlsLoader[1];

            $lColumnMap = array(
                'Personalnummer' => 'trimble_id',
                'Name' => 'name',
                'Vorname' => 'prename',
                'Anrede' => 'salutation',
                'Straße' => 'street',
                'PLZ' => 'zip_code',
                'Ortname' => 'town',
                'Telefon' => 'phone',
                'Telefax' => 'fax',
                'Handy' => 'mobile',
                'Geburtsdatum' => 'birthday',
                'Beschäftigt ab' => 'entry_date',
                'Initialen' => 'initial',
                'LKZ' => 'country_code',
                'Abteilung' => 'department',
                'Beschäftigt bis' => 'deleted_at',
                'EMail-Adresse' => 'email',
            );

            $knownEmployees = [];
            $unknownEmployees = [];
            $allEmployees_mapped = null;

            $iColumnName = array_search("Name", $lColumnNames);
            $iColumnInitial = array_search("Personalnummer", $lColumnNames);
            $pRow = 2;
            // $errors = [];
            $departments = $this->getDoctrine()->getRepository('EmployeeBundle:Department')->findAll();

            $arrDepartments = [];
            foreach ($departments as $department) {
                array_push($arrDepartments, $department->getName());
            }

            while (!empty($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($iColumnName, $pRow)->getValue())) {
                //if ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($iColumnInitial, $pRow)->getValue() != "") {
                $lRowValues = array();
                for ($pColumn=0;$pColumn<count($lColumnNames);$pColumn++) {
                    if (array_key_exists($lColumnNames[$pColumn], $lColumnMap)) {
                        if (in_array($lColumnMap[$lColumnNames[$pColumn]], ['deleted_at','birthday'])) {
                            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($pColumn, $pRow)->getStyle()->getNumberFormat()->setFormatCode('yyyy-mm-dd');
                        }
                        $cellValue = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($pColumn, $pRow)->getFormattedValue();
                        $lRowValues[$lColumnMap[$lColumnNames[$pColumn]]] = ($cellValue==""?null:$cellValue);
                    }
                }

                if (!empty($lRowValues["trimble_id"])) {
                    if (!is_null($lRowValues["department"]) && !in_array($lRowValues["department"], $arrDepartments)) {
                        array_push($arrDepartments, $lRowValues["department"]);
                        $department = new Department();
                        $department->setName($lRowValues["department"]);
                        $em->persist($department);
                        $em->flush();
                    }

                    $employee_known = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->findOneBy(array('trimbleId'=>$lRowValues["trimble_id"]));
                    try {
                        $employee = new EmployeeImport();
                        $employee->setTrimbleId($lRowValues["trimble_id"]);

                        if (!empty($lRowValues["initial"])) {
                            $employee->setInitial($lRowValues["initial"]);
                        }
                        $employee->setName($lRowValues["name"]);
                        if (!empty($lRowValues["prename"])) {
                            $employee->setPrename($lRowValues["prename"]);
                        }
                        if (!empty($lRowValues["salutation"])) {
                            $employee->setSalutation($lRowValues["salutation"]);
                        }
                        if (!empty($lRowValues["street"])) {
                            $employee->setStreet($lRowValues["street"]);
                        }
                        if (!empty($lRowValues["country_code"])) {
                            $employee->setCountryCode($lRowValues["country_code"]);
                        }
                        if (!empty($lRowValues["zip_code"])) {
                            $employee->setZipCode(substr($lRowValues["zip_code"], 0, 5));
                        }
                        if (!empty($lRowValues["town"])) {
                            $employee->setTown($lRowValues["town"]);
                        }
                        if (!empty($lRowValues["phone"])) {
                            $employee->setPhone($lRowValues["phone"]);
                        }
                        if (!empty($lRowValues["fax"])) {
                            $employee->setFax($lRowValues["fax"]);
                        }
                        if (!empty($lRowValues["mobile"])) {
                            $employee->setMobile($lRowValues["mobile"]);
                        }
                        if (!empty($lRowValues["birthday"])) {
                            $employee->setBirthday(new \DateTime($lRowValues["birthday"]));
                        }
                        if (!empty($lRowValues["entry_date"])) {
                            $employee->setEntryDate(\DateTime::createFromFormat('d/m/Y', $lRowValues["entry_date"]));
                        }
                        if (!empty($lRowValues["email"])) {
                            $employee->setEmail($lRowValues["email"]);
                        }
                        if (!empty($lRowValues["deleted_at"])) {
                            $employee->setDeletedAt(new \DateTime($lRowValues["deleted_at"]));
                        }

                        /*if($employee->isDeleted()) {
                            continue;
                        }*/

                        $em->persist($employee);
                        $em->flush();
                    } catch (\Exception $e) {
                        //array_push($errors, $lRowValues);
                    }

                    if (!empty($employee_known)) {
                        $knownEmployees[$employee->getId()] = $employee_known->getId();
                    } else {
                        // Liste der bekannten Mitarbeiter für Levenstein vorbereiten
                        if (empty($allEmployees_mapped)) {
                            $allEmployees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->findBy([], ['name'=>'ASC','prename'=>'ASC']);
                            $allEmployees_mapped = [];
                            foreach ($allEmployees as $curEmployee) {
                                $allEmployees_mapped[$curEmployee->getId()] = [
                                    $curEmployee->getTrimbleId(),
                                    $curEmployee->getName(),
                                    $curEmployee->getPrename(),
                                    $curEmployee->getSalutation(),
                                    $curEmployee->getStreet(),
                                    $curEmployee->getZipCode(),
                                    $curEmployee->getTown(),
                                ];
                            }
                        }

                        // Zuordnung nach Levenstein
                        $newEmployee = [
                            $employee->getTrimbleId(),
                            $employee->getName(),
                            $employee->getPrename(),
                            $employee->getSalutation(),
                            $employee->getStreet(),
                            $employee->getZipCode(),
                            $employee->getTown(),
                        ];

                        $orderme_temp = $this->prepareOrder($newEmployee, $allEmployees_mapped);
                        $orderme =$orderme_temp[0];
                        /*usort($orderme, function($a, $b) {
                            return $this->sortByLev($a, $b);
                        });*/

                        if ($orderme_temp[1] == 0) {
                            $knownEmployees[$employee->getId()] = $orderme_temp[2];
                        } elseif (!$employee->isDeleted()) {
                            $unknownEmployees[$employee->getId()] = [
                                implode(", ", $newEmployee),
                                $orderme,
                                $orderme_temp[1],
                            ];
                        }
                    }
                }
                $pRow++;
            }

            unlink($fileName);

            $em = $this->getDoctrine()->getManager();
            $connection = $em->getConnection();
            $statement = $connection->prepare("
            SELECT employee.*,
            employee_import.name AS newname,
            employee_import.prename AS newprename,
            employee_import.komalog_id AS newkomalog_id,
            employee_import.street AS newstreet,
            employee_import.entry_date AS newentry_date,
            employee_import.zip_code AS newzip_code,
            employee_import.town AS newtown,
            employee_import.mobile AS newmobile
                        from employee_import LEFT JOIN employee on employee_import.trimble_id = employee.trimble_id
                            WHERE
                            employee_import.name != employee.name
                            OR
                            employee_import.prename != employee.prename
                            OR
                            employee_import.street != employee.street
                            OR
                            employee_import.entry_date != employee.entry_date
                            OR
                            employee_import.zip_code != employee.zip_code
                            OR
                            employee_import.town != employee.town
                            OR
                            employee_import.mobile != employee.mobile
                ");

            $statement->execute();
            $OldEmployeeWithNewData = $statement->fetchAll();


            return $this->render('EmployeeBundle:Employee:import.html.twig', array(
                'step' => 2,
                'inserted' => 0,
                'knownEmployees' => $knownEmployees,
                'unknownEmployees' => $unknownEmployees,
                'OldEmployeesWithNewData' => $OldEmployeeWithNewData,
                ));
        }

        return $this->render('EmployeeBundle:Employee:import.html.twig', array(
            'step' => 1,
            'inserted' => 0,
        ));
    }

    private function prepareOrder($newEmployee, $allEmployees_mapped)
    {
        $ret = [];
        $spalten = count($newEmployee);
        $spaltenlaenge = [];
        $spaltengewicht= [0,100,20,1,1,1,1];
        $maxfault=1000000;
        $maxkey=null;
        for ($i=0;$i<$spalten;$i++) {
            $spaltenlaenge[$i] = strlen($newEmployee[$i]);
        }
        foreach ($allEmployees_mapped as $tempkey => $value) {
            for ($i=0;$i<$spalten;$i++) {
                $spaltenlaenge[$i] = strlen($value[$i])>$spaltenlaenge[$i]?strlen($value[$i]):$spaltenlaenge[$i];
            }
        }
        foreach ($allEmployees_mapped as $tempkey => $value) {
            $temprow = [$tempkey];
            $temprow[] = implode(", ", $value);
            $tempfault = 0;
            $spaltensumme = 0;
            for ($i=1;$i<$spalten;$i++) {
                $lev = levenshtein($newEmployee[$i], $value[$i]);
                $tempfault += ($lev/$spaltenlaenge[$i])*$spaltengewicht[$i];
                $spaltensumme += $spaltengewicht[$i];
            }
            $temprow[] = $tempfault/$spaltensumme;
            $ret[] = $temprow;
            $maxkey=($tempfault/$spaltensumme)<$maxfault?$tempkey:$maxkey;
            $maxfault=($tempfault/$spaltensumme)<$maxfault?($tempfault/$spaltensumme):$maxfault;
        }
        return [$ret,$maxfault,$maxkey];
    }

    private function sortByLev($a, $b, $col=2)
    {
        if ($a[$col] == $b[$col]) {
            return (count($a) > $col+1)?$this->sortByLev($a, $b, $col+1):0;
        }
        return ($a[$col] < $b[$col]) ? -1 : 1;
    }

    public function remainingmtlAction(Request $request)
    {
        $employeeIds = $request->get('ids');
        $start = (new Carbon($request->get('date')))->startOfYear();
        $end = (new Carbon($request->get('date')))->endOfMonth();
        $year = (new Carbon($request->get('date')))->year;

        $absenceService = $this->get('app.absence_service', $this->getDoctrine());
        $remainingmtl = $absenceService->getRemainingMtl2($employeeIds, $start, $end, $year);

        return new JsonResponse($remainingmtl);
    }

        public function createSalaryToArchiveAction()
            { die(); // Cron add on server //
            $em = $this->getDoctrine()->getManager();
            $employees = $em->getRepository('EmployeeBundle:Employee')->getAllAvailable();
            foreach ($employees as $employee)
            { $salary= $em->getRepository('EmployeeBundle:Salary')->findByEmployeeId($employee->getId());
                if($salary == NULL)
                {
                  $salary=new Salary();
                  $salary->employeeId= $employee->getId();
                  $salary->salary=$employee->getSalary();
                  $now = new Date();
                  $salary->fromDate=$now;
                  $salary->createAt=$now;
                  $em->presist($salary);
                  $em-> flush();
                }
            }
            return new JsonResponse(true);
            }

            public function SalaryIndentsAction()
            {
            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('employee_index');
            }
            $salarys = [] ;
            $em = $this->getDoctrine()->getManager();
            $employees = $em->getRepository('EmployeeBundle:Employee')->getAllAvailable();
                foreach ($employees as $employee)
                {
                    $salary= $em->getRepository('EmployeeBundle:Salary')->findByEmployeeIdNewSalary($employee->getId());
                    if($salary != Null  )
                    if($salary[0]->getSalary() != $employee->getSalary())
                    {
                        $Editemployee = $em->getRepository('EmployeeBundle:Employee')->find($employee->getId());
                        $Editemployee->setSalary($salary[0]->getSalary());
                        $em->persist($Editemployee);
                        $em->flush();
                        $employee->newsalary = $salary[0]->getSalary();
                        $employee->fromDate = $salary[0]->getFromDate();
                        $employee->toDate = $salary[0]->getToDate();
                        $employee->comment = $salary[0]->getComment();
                        $employee->createAt = $salary[0]->getCreateAt();
                        array_push($salarys,$employee);
                    }
                }
                $employeesAll = $em->getRepository('EmployeeBundle:Employee')->getAllAvailable();
                foreach ($employeesAll as $employeea)
                {
                    $employeea->salarys= $em->getRepository('EmployeeBundle:Salary')->findByEmployeeId($employeea->getId());

                }
                return $this->render('EmployeeBundle:Employee:indexSalary.html.twig', array(
                'employees' => $salarys,
                'employeesAll' => $employeesAll,
            ));
            }

            public function deleteSalaryAction(Request $request,Salary $salary)
            {
              if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                  $em = $this->getDoctrine()->getManager();
                  $employee = $em->getRepository('EmployeeBundle:Employee')->find($salary->getEmployeeId());
                  $em->remove($salary);
                  $em->flush();
                  return $this->redirectToRoute('employee_edit',array('id' => $employee->getId()));
               }
            }

            public function ajaxEditAction(Request $request,$id)
                {
                  $type = $request->get("type");
                  $value = $request->get("value");
                  if(empty($value))
                  {
                    return new JsonResponse([
                        'status'            => false
                    ]);
                  }
                  $em = $this->getDoctrine()->getManager();
                  $employee = $em->getRepository('EmployeeBundle:Employee')->find($id);
                   if($type == 'trimbleid'){$employee->setTrimbleId($value);$em->persist($employee);$em->flush(); return new JsonResponse(['status' => true]);}
                   if($type == 'salutation'){$employee->setSalutation($value); $em->persist($employee);$em->flush();return new JsonResponse(['status' => true]);}
                   if($type == 'name'){$employee->setName($value);$em->persist($employee);$em->flush(); return new JsonResponse(['status' => true]);}
                   if($type == 'prename'){$employee->setPrename($value); $em->persist($employee);$em->flush();return new JsonResponse(['status' => true]);}
                   if($type == 'street'){$employee->setStreet($value);$em->persist($employee);$em->flush(); return new JsonResponse(['status' => true]);}
                   if($type == 'zipCode'){$employee->setZipCode($value);$em->persist($employee);$em->flush(); return new JsonResponse(['status' => true]);}
                   if($type == 'town'){$employee->setTown($value);$em->persist($employee);$em->flush(); return new JsonResponse(['status' => true]);}
                   if($type == 'countryCode'){$employee->setCountryCode($value);$em->persist($employee);$em->flush(); return new JsonResponse(['status' => true]);}
                   if($type == 'phone'){$employee->setPhone($value);$em->persist($employee);$em->flush(); return new JsonResponse(['status' => true]);}
                   if($type == 'mobile'){$employee->setMobile($value);$em->persist($employee);$em->flush(); return new JsonResponse(['status' => true]);}
                   if($type == 'email'){$employee->setEmail($value);$em->persist($employee);$em->flush(); return new JsonResponse(['status' => true]);}
                   if($type == 'birthday'){
                     $employee->setBirthday(new \DateTime($value));$em->persist($employee);
                     $em->flush();
                     return new JsonResponse(['status' => true]);
                   }
                   if($type == 'initial'){$employee->setInitial($value);$em->persist($employee);$em->flush(); return new JsonResponse(['status' => true]);}
                   if($type == 'email2'){$employee->setEmailPrivate($value);$em->persist($employee);$em->flush();
                      return new JsonResponse(['status' => true]);}
                   if($type == 'Telefon2'){$employee->setPhonePrivate($value);$em->persist($employee);$em->flush();
                      return new JsonResponse(['status' => true]);}
                  if($type == 'Urlaub'){
                      $holidayschedule = $em->getRepository('AbsenceBundle:Holidayschedule')->getHolidayscheduleByEmployeeAndYear($employee,$request->get("year"));

                      $holidayschedule->setHoliday($value);$em->persist($holidayschedule);$em->flush();
                      return new JsonResponse(['status' => true]);
                   }
                  return new JsonResponse([
                      'status'            => false,
                  ]);
                }

                public function saveEmailAction(Request $request)
                {

                    $mobile = $request->query->get("mobile");
                    $email = $request->query->get("email");
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)){
                      $employee =$this->getUser()->getEmployee();
                      $employee->setPhonePrivate($mobile);
                      $employee->setEmailPrivate($email);
                      $em = $this->getDoctrine()->getManager();
                      $em->persist($employee);
                      $em->flush();
                      return new JsonResponse([
                          'status'            => true,
                      ]);
                    }
                    return new JsonResponse([
                        'status'            => false,
                    ]);
                }

                public function delEmployeeAction(Request $request)
               {
                   if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                     $date= new \Datetime($request->get("date"));
                     $employeeId= $request->get("employee");
                     $em = $this->getDoctrine()->getManager();
                     $employee = $em->getRepository('EmployeeBundle:Employee')->find($employeeId);
                     $employee->setDeletedAt($date);
                     $em->persist($employee);
                     $em->flush();
                      $user = $employee->getUser();
                      $user->setPassword("Abgemeldet");
                      $user->setEnabled(false);
                      $em->persist($user);
                      $em->flush();
                      return new JsonResponse([
                          'status'            => 'true',
                      ]);
                  }
               }
}
