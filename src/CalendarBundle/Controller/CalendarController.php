<?php

namespace CalendarBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Carbon\Carbon;
use EmployeeBundle\Entity\Employee;
use AbsenceBundle\Entity\Absence;
use AbsenceBundle\Entity\PublicHoliday;
use AbsenceBundle\Entity\Vacationlock;
use MessageBundle\Entity\Messages;
use EmployeeBundle\Entity\AbsenceClearing;
use AbsenceBundle\Entity\Holidayschedule;
use AbsenceBundle\Entity\AbsenceClearingRecords;

class CalendarController extends Controller
{
    /**
   * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PERSONAL') or is_granted('ROLE_HOLIDAY')")
   */
    public function indexAction(Request $request)
    {
        $absence = new Absence();
        $em = $this->getDoctrine()->getManager();

        //add Holiday to Archive
        $employees = $em->getRepository('EmployeeBundle:Employee')->getAllAvailable();
        $year = (new \Datetime())->format('Y');
        $createAt = (new \Datetime());
        foreach ($employees as $employee) {
        $entryDate = $employee->getEntryDate()->format('Y');
        if(!empty($employee->getContract()))
        {
          while( $entryDate <= $year )
          { $holidayschedule = $em->getRepository('AbsenceBundle:Holidayschedule')->getHolidayscheduleByEmployeeAndYear($employee,$entryDate);
            if(empty($holidayschedule))
            {//dump($entryDate);
              $holidayscheduleLast = $em->getRepository('AbsenceBundle:Holidayschedule')->getHolidayscheduleByEmployeeAndYear($employee,($entryDate-1));
              if(empty($holidayscheduleLast))
              {//dump($entryDate);
                $holiday= $employee->getContract()->getHolidays();
              }
              else{
                $holiday = $holidayscheduleLast->getHoliday();
              }
              $holidayschedule =  new Holidayschedule();
              $holidayschedule->setEmployee($employee);
              $holidayschedule->setYear($entryDate);
              if(empty($holiday)) { $holiday = 30; }
              $holidayschedule->setHoliday($holiday);
              $holidayschedule->setCreateAt($createAt);
              $em->persist($holidayschedule);
              $em->flush();
            }
            $entryDate++;
          }
        }
        }
        // End Code  =>  add Holiday to Archive
        $form = $this->createForm('CalendarBundle\Form\CalendarModalAbsenceAddType', $absence, [
            'user' => $this->getUser()
        ]);
        return $this->render('CalendarBundle:Calendar:index.html.twig', [
              'form' => $form->createView(),
        ]);
    }

    public function DialogcalendarAction(Request $request)
    {
        // das ist für nur Roman und michael und marc
        $absence = new Absence();
        $form = $this->createForm('CalendarBundle\Form\CalendarModalAbsenceAddType', $absence, [
            'user' => $this->getUser()
        ]);
        return $this->render('CalendarBundle:Calendar:Dialogindex.html.twig', [
              'form' => $form->createView(),
        ]);
    }

    public function DialogjsonEmployeeAction(Request $request)
    {
        $year = Carbon::parse($request->query->get('start'))->year;
        $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllAvailableDialog();
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

    public function AzubicalendarAction(Request $request)
    {
        // das ist für nur Roman und michael und marc
        $absence = new Absence();
        $form = $this->createForm('CalendarBundle\Form\CalendarModalAbsenceAddType', $absence, [
            'user' => $this->getUser()
        ]);
        return $this->render('CalendarBundle:Calendar:Azubiindex.html.twig', [
              'form' => $form->createView(),
        ]);
    }

    public function azubijsonEmployeeAction(Request $request)
    {
        $year = Carbon::parse($request->query->get('start'))->year;
        $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllAvailableAzubi();
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

    public function showAction(Employee $employee)
    {
        $usr = $this->getUser();
        if (!is_null($employee)) {
            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL') && !$this->get('security.authorization_checker')->isGranted('ROLE_HOLIDAY') && !$this->get('security.authorization_checker')->isGranted('ROLE_AZUBI_PERSONAL') && !$this->get('security.authorization_checker')->isGranted('ROLE_DIALOG_PERSONAL')) {
                if ($employee->getId() != $usr->getEmployee()->getId()) {
                    throw new AccessDeniedException();
                }
            }
        }
        if($this->get('security.authorization_checker')->isGranted('ROLE_AZUBI_PERSONAL'))
        {
            if ( $employee->getId() != $usr->getEmployee()->getId() && $employee->getDepartment()->getId() != '4' )  {
                throw new AccessDeniedException();
            }
        }
        if($this->get('security.authorization_checker')->isGranted('ROLE_DIALOG_PERSONAL'))
        {
            if ( $employee->getId() != $usr->getEmployee()->getId() && $employee->getDepartment()->getId() != '10' )  {
                throw new AccessDeniedException();
            }
        }


        $em = $this->getDoctrine()->getManager();
        $absencesApproved = $em->getRepository('AbsenceBundle:Absence')->getAbsencesByStatus($employee, 1);
        $absencesDeclined = $em->getRepository('AbsenceBundle:Absence')->getAbsencesByStatus($employee, 2);
        $absencesPending = $em->getRepository('AbsenceBundle:Absence')->getAbsencesByStatus($employee, 3);

        $vacationlockService = $this->get('app.vacation_lock_service');
        $vacationlocks = $vacationlockService->findAllVacationlocks();


        $start = (new Carbon())->startOfYear();
        $end = (new Carbon())->endOfMonth();
        $year = (new Carbon())->year;

        $absenceService = $this->get('app.absence_service', $this->getDoctrine());
        $remainingmtl = $absenceService->getRemainingMtl2($employee->getId(), $start, $end, $year);

        $statistic = $absenceService->getDaysByReason($employee, $year);
        // $holidayStatistic = $absenceService->getHolidayStatistik($employee, new Carbon($year));

        $publicHolidays = $this->getDoctrine()->getRepository('AbsenceBundle:PublicHoliday')->findAll();

        $publicHolidayService = $this->get('app.public_holiday_service');

        $publicHolidays = $publicHolidayService->publicHolidaysToJson($publicHolidays);

        $absencesJson = $absenceService->getAbsenceJson($employee);

        $holidayStatistic = $absenceService->getHolidays($year, $employee);
        $absenceDetailClearings = $em->getRepository('EmployeeBundle:AbsenceClearing')->findByEmployee($employee);
        $holidayschedule =  $em->getRepository('AbsenceBundle:Holidayschedule')->findByEmployee($employee);

        $holidays=[];

        foreach ($holidayschedule as $value) {
          $holidays[$value->getYear()]=[ 'id'=>$value->getId()  , 'value' => $value->getHoliday() ];
        }
        // add Urlaubsänderung to Archive
        foreach ($absenceDetailClearings as $absenceDetailClearing) {
          if($absenceDetailClearing->getSubstractDaysOfVacation() > 0  or $absenceDetailClearing->getAdditionalDaysOfVacation() > 0 )
          { //add to Arctive
              $absenceClearingRecords =  $em->getRepository('AbsenceBundle:AbsenceClearingRecords')->findByAbsenceClearing($absenceDetailClearing);
              if(empty($absenceClearingRecords))
              {
                if($absenceDetailClearing->getAdditionalDaysOfVacation() > 0 )
                {
                  $absenceClearingRecords =new AbsenceClearingRecords();
                  $absenceClearingRecords->setEmployee($employee);
                  $absenceClearingRecords->setAbsenceClearing($absenceDetailClearing);
                  $absenceClearingRecords->setType('AdditionalDaysOfVacation');
                  $absenceClearingRecords->setValue($absenceDetailClearing->getAdditionalDaysOfVacation());
                  $absenceClearingRecords->setComment($absenceDetailClearing->getComment2());
                  $absenceClearingRecords->setCreateAt(new \Datetime());
                  $em->persist($absenceClearingRecords);
                  $em->flush();
                }
                if($absenceDetailClearing->getSubstractDaysOfVacation() > 0 )
                {
                  $absenceClearingRecords =new AbsenceClearingRecords();
                  $absenceClearingRecords->setEmployee($employee);
                  $absenceClearingRecords->setAbsenceClearing($absenceDetailClearing);
                  $absenceClearingRecords->setType('SubstractDaysOfVacation');
                  $absenceClearingRecords->setValue($absenceDetailClearing->getSubstractDaysOfVacation());
                  $absenceClearingRecords->setComment($absenceDetailClearing->getComment());
                  $absenceClearingRecords->setCreateAt(new \Datetime());
                  $em->persist($absenceClearingRecords);
                  $em->flush();
                }
              }
          }
        }
        //end
        $absenceClearingRecords =  $em->getRepository('AbsenceBundle:AbsenceClearingRecords')->findByEmployee($employee);
        return $this->render('CalendarBundle:Calendar:show.html.twig', [
          'employee' => $employee,
          'absencesApproved' => $absencesApproved,
          'absencesPending' =>  $absencesPending,
          'absencesDeclined' =>  $absencesDeclined,
          'statistic' => $statistic,
          'vacationlocks' => $vacationlocks,
          'remainingmtl' => $remainingmtl,
          'holidayStatistic' => $holidayStatistic,
          'publicHolidays' => $publicHolidays,
          'absencesJson' => $absencesJson,
          'absenceDetailClearing' => $absenceDetailClearings,
          'holidays' => $holidays,
          'absenceClearingRecords' => $absenceClearingRecords,
        ]);
    }

    public function deleteAction(Request $request, Absence $absence)
    {
        $form = $this->createDeleteForm($absence);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($absence);
            $em->flush();
        }
        return $this->redirectToRoute('calendar_index');
    }

    private function createDeleteForm(Absence $absence)
    {
        return $this->createFormBuilder()
          ->setAction($this->generateUrl('absence_delete', ['id' => $absence->getId()]))
          ->setMethod('DELETE')
          ->getForm();
    }

    /**
   * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PERSONAL') or is_granted('ROLE_HOLIDAY')")
   */
    public function requestAction()
    {
        $em = $this->getDoctrine()->getManager();
        $absencesApproved = $em->getRepository('AbsenceBundle:Absence')->getAllAbsencesByStatus(1);
        $absencesDeclined = $em->getRepository('AbsenceBundle:Absence')->getAllAbsencesByStatus(2);
        $absencesPending = $em->getRepository('AbsenceBundle:Absence')->getAllAbsencesByStatus(3);
        return $this->render('CalendarBundle:Calendar:request.html.twig', array(
          'absencesApproved' => $absencesApproved,
          'absencesPending' =>  $absencesPending,
          'absencesDeclined' =>  $absencesDeclined,
        ));
    }

    /**
   * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PERSONAL') or is_granted('ROLE_HOLIDAY')")
   */
    public function settingsAction()
    {
        return $this->render('CalendarBundle:Calendar:settings.html.twig');
    }

    /**
   * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PERSONAL') or is_granted('ROLE_HOLIDAY')")
   */
    public function capacityAction()
    {
        return $this->render('CalendarBundle:Calendar:capacity.html.twig');
    }

    /**
   * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PERSONAL') or is_granted('ROLE_HOLIDAY')")
   */
    public function vacationlockAction()
    {
        return $this->render('CalendarBundle:Calendar:vacationlock.html.twig');
    }

    /**
   * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PERSONAL') or is_granted('ROLE_HOLIDAY')")
   */
    public function ajaxPostVacationlockAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $content = $request->getContent();
            $dates = json_decode($content);
            $em = $this->getDoctrine()->getManager();

            foreach ($dates as $key => $value) {
                $vacationlock = $this->getDoctrine()
                ->getRepository('AbsenceBundle:Vacationlock')
                ->findOneBy(array('year' => $key));

                if (!$vacationlock) {
                    $vacationlock = new Vacationlock();
                }

                $vacationlock->setYear($key);
                $vacationlock->setDays($value);
                $em->persist($vacationlock);
                $em->flush();
            }
            return new JsonResponse(array('status' => 'success'));
        } else {
            return new JsonResponse(array('status' => 'error'));
        }
    }

    public function ajaxGetVacationlockAction()
    {
        $vacationlockService = $this->get('app.vacation_lock_service');
        return new JsonResponse($vacationlockService->findAllVacationlocks());
    }

    public function jsonAbsenceReasonAction(Employee $employee, Request $request)
    {
        $usr = $this->getUser();
        if (!is_null($employee)) {
            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL') && !$this->get('security.authorization_checker')->isGranted('ROLE_HOLIDAY')  ) {
                if ($employee->getId() != $usr->getEmployee()->getId()) {
                    throw new AccessDeniedException();
                }
            }
        }

        return new JsonResponse($this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getDaysByReason($employee, $request->get('year')));
    }

    public function jsonRemainingAbsenceAction(Request $request)
    {
        $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($request->get('id'));
        $year = Carbon::createFromFormat('Y', $request->get('date'))->startOfYear()->year;

        $absenceService = $this->get('app.absence_service', $this->getDoctrine());

        $json = $absenceService->getHolidays($year, $employee);

        return new JsonResponse($json);
    }

    public function jsonAbsenceBetweenByEmployeeAction(Request $request)
    {
        return new JsonResponse($this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getAbsencesBetweenByEmployee($request->get('start'), $request->get('end')));
    }

    public function ajaxGetAbsenceAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $absence = $em->getRepository('AbsenceBundle:Absence')->find($request->get('id'));

        return new JsonResponse([
          'days' 		=> $absence->getDay(),
          'reason' 		=> $absence->getReason()->getId(),
          'status' 		=> $absence->getStatus()->getId(),
          'note'  		=> $absence->getNote(),
          'approvedBy' 	=> $absence->getApprovedBy()->getFullname(),
          'createdat' 	=> $absence->getCreatedAt(),
        ]);
    }

    public function ajaxAction(Request $request)
    {
            $repoEmployee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee');
            $repoReason = $this->getDoctrine()->getRepository('AbsenceBundle:Reason');
            $repoStatus = $this->getDoctrine()->getRepository('AbsenceBundle:Status');
            $repoAbsence = $this->getDoctrine()->getRepository('AbsenceBundle:Absence');
            $absence = new Absence();
            if ($request->get('absence')) {
                $absence = $repoAbsence->find($request->get('absence'));
            }

            $employee = $repoEmployee->find($request->get('employee'));
            $reason = $repoReason->find($request->get('reason'));
            $status = $repoStatus->find(3);

            if (
                $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ||
                $this->get('security.authorization_checker')->isGranted('ROLE_HOLIDAY')
            ) {
                $status = $repoStatus->find($request->get('status'));
            }

            $absence->setEmployee($employee);
            $absence->setFromDate(Carbon::parse($request->get('fromDate')));
            $absence->setToDate(Carbon::parse($request->get('toDate')));
            $absence->setReason($reason);
            $absence->setNote($request->get('note'));
            $absence->setDay($request->get('day'));
            $absence->setStatus($status);
            $absence->setApprovedBy($this->getUser()->getEmployee());

            $em = $this->getDoctrine()->getManager();
            $em->persist($absence);
            $em->flush();
            $absenceDetailsClearingService = $this->get('app.absence_details_clearing_service');

            $absenceDetailsClearingService->createOrUpdateAbsenceDetailsFromArray($request->get('absenceoptions'), $employee, $absence);
            $absenceclearingservice = $this->container->get('app.absence_clearing_service', $this->getDoctrine());
            $absenceclearingservice->updateClearing($employee, $absence);

            //email send Function //
            if($absence->getReason()->getId() == 2 AND $request->get('sendInfo') == 'true' ){
            if($absence->getStatus()->getId() == 1)
            {
              if ($employee->getSalutation() != "Frau")
              {$bname= 'Lieber '.$employee->getFName();}
              else {$bname= 'Liebe '.$employee->getFName();}
              $to = $employee->getEmailPrivate();
              if(!empty($to)){
              if(strpos($to, '@') === false)
              {
                  $to= $to . '@giesker-laakmann.de';
              }
              $bodytext = '<br>dein Urlaubsantrag (Von:'.(Carbon::parse($request->get('fromDate')))->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage wurden genehmigt. <br>Wir wünschen dir einen erholsamen und erlebnisreichen Urlaub und freuen uns über Urlaubsgrüße :-) <br> Schöne Ferien und eine gute Zeit wünscht dir dein G&L Team.<br>';
              $message = (new \Swift_Message('Information zum Urlaub'))
                  ->setFrom('gl@361gradmedien.de')
                  ->setBcc('gl@361gradmedien.de')
                  ->setTo($to)
                  ->setBody( $bname. $bodytext , 'text/html');
              $this->get('mailer')->send($message);
            }
              $bodytext = 'dein Urlaubsantrag (Von:'.(Carbon::parse($request->get('fromDate')))->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage wurden genehmigt. Wir wünschen dir einen erholsamen und erlebnisreichen Urlaub und freuen uns über Urlaubsgrüße. Schöne Ferien und eine gute Zeit wünscht dir dein G&L Team.';
              $message = new Messages();
              $message->setEmployee($employee);
              $message->setMessage($bodytext);
              $message->setType('1');
              $em->persist($message);
              $em->flush();
            }
            if($absence->getStatus()->getId() == 2)
            {
              if ($employee->getSalutation() != "Frau")
              {$bname= 'Lieber '.$employee->getFName();}
              else {$bname= 'Liebe '.$employee->getFName();}
              $to = $employee->getEmailPrivate();
              if(!empty($to)){
              if(strpos($to, '@') === false)
              {
                  $to = $to .'@giesker-laakmann.de';
              }
              $bodytext = '<br>dein Urlaubsantrag (Von:'.(Carbon::parse($request->get('fromDate')))->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage konnte leider nicht genehmigt werden.<br>Madeleine wird dich zeitnah kontaktieren um mit dir die Gründe zu besprechen und einen neuen Termin zu finden an dem dein Urlaub genehmigt werden kann.<br>Wir sind uns sicher, dass wir eine einvernehmliche Lösung für dich finden.<br>';
              $message = (new \Swift_Message('Information zum Urlaub'))
                  ->setFrom('gl@361gradmedien.de')
                  ->setBcc('gl@361gradmedien.de')
                  ->setTo($to)
                  ->setBody( $bname. $bodytext , 'text/html');
              $this->get('mailer')->send($message);
            }
              $bodytext = 'dein Urlaubsantrag (Von:'.(Carbon::parse($request->get('fromDate')))->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage konnte leider nicht genehmigt werden. Madeleine wird dich zeitnah kontaktieren um mit dir die Gründe zu besprechen und einen neuen Termin zu finden an dem dein Urlaub genehmigt werden kann. Wir sind uns sicher, dass wir eine einvernehmliche Lösung für dich finden.';
              $message = new Messages();
              $message->setEmployee($employee);
              $message->setMessage($bodytext);
              $message->setType('1');
              $em->persist($message);
              $em->flush();
            }
            if($absence->getStatus()->getId() == 3)
            {
              if ($employee->getSalutation() != "Frau")
              {$bname= 'Lieber '.$employee->getFName();}
              else {$bname= 'Liebe '.$employee->getFName();}
              $to = $employee->getEmailPrivate();
              if(!empty($to)){
              if(strpos($to, '@') === false)
              {
                  $to = $to .'@giesker-laakmann.de';
              }
              $bodytext = '<br>dein Urlaubsantrag (Von:'.(Carbon::parse($request->get('fromDate')))->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) ist eingegangen und wird zeitnah bearbeitet.<br>In den nächsten Tagen erhältst du eine Rückmeldung.<br>Bis dahin wünschen wir dir eine stressfreie Zeit.<br>Schöne Grüße dein G&L Team<br>';
              $message = (new \Swift_Message('Information zum Urlaub'))
                  ->setFrom('gl@361gradmedien.de')
                  ->setBcc('gl@361gradmedien.de')
                  ->setTo($to)
                  ->setBody( $bname. $bodytext , 'text/html');
              $this->get('mailer')->send($message);
            }
            }}
            //end email send funciton //
        return $this->redirectToRoute('calendar_index');
    }

    public function jsonPublicHolidaysbyYearAction(Request $request)
    {
      /*
          DELETE public_holiday1 FROM public_holiday public_holiday1
          INNER JOIN public_holiday public_holiday2
          WHERE
              public_holiday1.id < public_holiday2.id AND
              public_holiday1.title = public_holiday2.title AND
          	public_holiday1.start = public_holiday2.start
          	;
      */
        $year = $request->get('year');
        $publicHolidayService = $this->get('app.public_holiday_service');
        if (!$publicHolidayService->checkIfYearIsSaved($year)) {
            $publicHolidayService->getPublicHolidaysFromAPIandSave($year);
            $publicHolidayService->getVacationsFromAPIandSave($year);
        }
        return new JsonResponse();
    }

    public function jsonPublicHolidaysAction(Request $request)
    {
      if ($request->get('start') != "")
      {
        $year = (new \DateTime($request->get('start')))->format('Y');
        $publicHolidays = $this->getDoctrine()->getRepository('AbsenceBundle:PublicHoliday')->findByYear($year);
      }
      else {
        $publicHolidays = $this->getDoctrine()->getRepository('AbsenceBundle:PublicHoliday')->findAll();
        }
        $publicHolidayService = $this->get('app.public_holiday_service');
        $json = $publicHolidayService->publicHolidaysToJson($publicHolidays);

        return new JsonResponse($json);
    }

    public function jsonStatisticsAction(Request $request)
    {
        $year = $request->get('year');
        $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($request->get('id'));
        $absenceService = $this->get('app.absence_service', $this->getDoctrine());
        $statistic = $absenceService->getDaysByReason($employee, $year);
        return new JsonResponse($statistic);
    }
    /**
    * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PERSONAL') or is_granted('ROLE_HOLIDAY')")
    */
    public function globalAction(Request $request)
    {
      set_time_limit(0);
      ini_set('memory_limit','-1');
      $absence = new Absence();
      $form = $this->createForm('CalendarBundle\Form\GlobalType', $absence);
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
        $fromDate= new \Datetime($request->get("CalendarBundle_Global")["fromDate"]);
        $toDate= new \Datetime($request->get("CalendarBundle_Global")["toDate"]);
        $day= $request->get("CalendarBundle_Global")["day"];
        $repo = $this->getDoctrine()->getRepository('AbsenceBundle:Status');
        $status = $repo->find(1);
        $reason = $absence->getReason();
        $approvedBy = $this->getUser()->getEmployee();
        $messagetext = $request->get("CalendarBundle_Global")["note"];
        $group =$request->get("CalendarBundle_Global")["group"];
        $absenceoptions = $request->get("absenceoptions");
        $sendInfo = !empty($request->get("CalendarBundle_Global")["sendInfo"]);

        if($group == 1) $departments = $request->get("CalendarBundle_Global")["departments"];
        if($group == 2) $employees =$request->get("CalendarBundle_Global")["employee"];
        $em = $this->getDoctrine()->getManager();
        if($group == 1)//department
        {
          foreach($departments as $department_id )
          {
            $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllEmployeeByDepartment($department_id);
            if(!empty($employees)){
                foreach ($employees as $employee) {
                  $absence = new Absence();
                  $absence->setFromDate($fromDate);
                  $absence->setToDate($toDate);
                  $absence->setNote($messagetext);
                  $absence->setDay($day);
                  $absence->setReason($reason);
                  $absence->setStatus($status);
                  $absence->setApprovedBy($approvedBy);
                  $absence->setEmployee($employee);

                  $em->persist($absence);
                  $em->flush();
                  $absenceDetailsClearingService = $this->get('app.absence_details_clearing_service');
                  $absenceDetailsClearingService->createOrUpdateAbsenceDetailsFromArray($absenceoptions, $employee, $absence);
                  $absenceclearingservice = $this->container->get('app.absence_clearing_service', $this->getDoctrine());
                  $absenceclearingservice->updateClearing($employee, $absence);
                  if($sendInfo)
                  {
                    $message = new Messages();
                    $message->setEmployee($employee);
                    $bodytext = 'dein Urlaubsantrag (Von:'.$fromDate->format('d.m.Y').' bis:'.$toDate->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage wurden genehmigt. Wir wünschen dir einen erholsamen und erlebnisreichen Urlaub und freuen uns über Urlaubsgrüße :-) Schöne Ferien und eine gute Zeit wünscht dir dein G&L Team.';
                    $message->setMessage($bodytext);
                    $message->setType(1);
                    $em->persist($message);
                    $em->flush();
                  }
                }
            }
          }
        }
        elseif($group == 2 and !empty($employees))//employee
        {
          foreach ($employees as $employee_id) {

            $absence = new Absence();
            $employee= $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
            $absence->setFromDate($fromDate);
            $absence->setToDate($toDate);
            $absence->setNote($messagetext);
            $absence->setDay($day);
            $absence->setReason($reason);
            $absence->setStatus($status);
            $absence->setApprovedBy($approvedBy);
            $absence->setEmployee($employee);

            $em->persist($absence);
            $em->flush();
            $absenceDetailsClearingService = $this->get('app.absence_details_clearing_service');
            $absenceDetailsClearingService->createOrUpdateAbsenceDetailsFromArray($absenceoptions, $employee, $absence);
            $absenceclearingservice = $this->container->get('app.absence_clearing_service', $this->getDoctrine());
            $absenceclearingservice->updateClearing($employee, $absence);
            if($sendInfo)
            {
              $message = new Messages();
              $message->setEmployee($employee);
              $bodytext = 'dein Urlaubsantrag (Von:'.$fromDate->format('d.m.Y').' bis:'.$toDate->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage wurden genehmigt. Wir wünschen dir einen erholsamen und erlebnisreichen Urlaub und freuen uns über Urlaubsgrüße :-) Schöne Ferien und eine gute Zeit wünscht dir dein G&L Team.';
              $message->setMessage($bodytext);
              $message->setType(1);
              $em->persist($message);
              $em->flush();
            }
          }
        }
        return $this->redirectToRoute('calendar_index');
      }
      return $this->render('CalendarBundle:Calendar:global.html.twig', array(
       'form' => $form->createView(),
     ));
    }
    /**
    * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PERSONAL') or is_granted('ROLE_HOLIDAY')")
    */
    public function urlaubsanderungAction(Request $request)
    {
      set_time_limit(0);
      ini_set('memory_limit','-1');
      $absenceClearing = new AbsenceClearing();
      $form = $this->createForm('CalendarBundle\Form\GlobalAbsenceClearingType', $absenceClearing);
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {

        $type= $request->get("CalendarBundle_GlobalAbsenceClearing")["type"];
        $day= $request->get("CalendarBundle_GlobalAbsenceClearing")["day"];
        $comment = $request->get("CalendarBundle_GlobalAbsenceClearing")["note"];
        $group =$request->get("CalendarBundle_GlobalAbsenceClearing")["group"];
        $year = (new \DateTime())->format('Y');
        if($group == 1) $departments = $request->get("CalendarBundle_GlobalAbsenceClearing")["departments"];
        if($group == 2) $employees =$request->get("CalendarBundle_GlobalAbsenceClearing")["employee"];

        $em = $this->getDoctrine()->getManager();
        if($group == 1)//department
        {
          foreach($departments as $department_id )
          {
            $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllEmployeeByDepartment($department_id);
            if(!empty($employees)){
                foreach ($employees as $employee) {

                  $absenceClearing= $this->getDoctrine()->getRepository('EmployeeBundle:AbsenceClearing')->getAbsenceClearingByEmployee( $year,$employee);
                  if(!empty($absenceClearing))
                  {
                    if($type == 1) //add
                    {
                      $absenceClearing->setAdditionalDaysOfVacation( $absenceClearing->getAdditionalDaysOfVacation() + $day );
                      $absenceClearing->setComment2( $absenceClearing->getComment2() .', '.$day . ':' .$comment);
                      $em->persist($absenceClearing);
                      $em->flush();
                      $this->saveAbsenceClearingRecorde($employee,$absenceClearing,'AdditionalDaysOfVacation',$day,$comment);

                    }
                    if($type == 2) //sub
                    {
                      $absenceClearing->setSubstractDaysOfVacation( $absenceClearing->getSubstractDaysOfVacation() + $day );
                      $absenceClearing->setComment( $absenceClearing->getComment(). $day . ':' .$comment );
                      $em->persist($absenceClearing);
                      $em->flush();
                      $this->saveAbsenceClearingRecorde($employee,$absenceClearing,'SubstractDaysOfVacation',$day,$comment);
                    }
                  }
                  else
                  {
                    if($type == 1) //add
                    {
                      $absenceClearing =  new AbsenceClearing();
                      $absenceClearing->setEmployee( $employee );
                      $absenceClearing->setAdditionalDaysOfVacation( $day );
                      $absenceClearing->setComment2( $day . ':' .$comment );
                      $absenceClearing->setYear($year);
                      $em->persist($absenceClearing);
                      $em->flush();
                      $this->saveAbsenceClearingRecorde($employee,$absenceClearing,'AdditionalDaysOfVacation',$day,$comment);
                    }
                    if($type == 2) //sub
                    {
                      $absenceClearing =  new AbsenceClearing();
                      $absenceClearing->setEmployee( $employee );
                      $absenceClearing->setSubstractDaysOfVacation( $day );
                      $absenceClearing->setComment( $day . ':' .$comment );
                      $absenceClearing->setYear($year);
                      $em->persist($absenceClearing);
                      $em->flush();
                      $this->saveAbsenceClearingRecorde($employee,$absenceClearing,'SubstractDaysOfVacation',$day,$comment);
                    }
                  }

                }
            }
          }

        }
        elseif($group == 2 and !empty($employees))//employee
        {
          foreach ($employees as $employee_id) {
            $employee=$this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
            $absenceClearing= $this->getDoctrine()->getRepository('EmployeeBundle:AbsenceClearing')->getAbsenceClearingByEmployee( $year,$employee);
            if(!empty($absenceClearing))
            {
              if($type == 1) //add
              {
                $absenceClearing->setAdditionalDaysOfVacation( $absenceClearing->getAdditionalDaysOfVacation() + $day );
                $absenceClearing->setComment2( $absenceClearing->getComment2() .', '.$day . ':' .$comment);
                $em->persist($absenceClearing);
                $em->flush();
                $this->saveAbsenceClearingRecorde($employee,$absenceClearing,'AdditionalDaysOfVacation',$day,$comment);
              }
              if($type == 2) //sub
              {
                $absenceClearing->setSubstractDaysOfVacation( $absenceClearing->getSubstractDaysOfVacation() + $day );
                $absenceClearing->setComment2( $absenceClearing->getComment2() .', '.$day . ':' .$comment);
                $em->persist($absenceClearing);
                $em->flush();
                $this->saveAbsenceClearingRecorde($employee,$absenceClearing,'SubstractDaysOfVacation',$day,$comment);
              }
            }
            else
            {
              if($type == 1) //add
              {
                $absenceClearing =  new AbsenceClearing();
                $absenceClearing->setEmployee( $employee );
                $absenceClearing->setAdditionalDaysOfVacation( $day );
                $absenceClearing->setComment2( $day . ':' .$comment );
                $absenceClearing->setYear($year);
                $em->persist($absenceClearing);
                $em->flush();
                $this->saveAbsenceClearingRecorde($employee,$absenceClearing,'AdditionalDaysOfVacation',$day,$comment);
              }
              if($type == 2) //sub
              {
                $absenceClearing =  new AbsenceClearing();
                $absenceClearing->setEmployee( $employee );
                $absenceClearing->setSubstractOfVacation( $day );
                $absenceClearing->setComment( $day . ':' .$comment );
                $absenceClearing->setYear($year);
                $em->persist($absenceClearing);
                $em->flush();
                $this->saveAbsenceClearingRecorde($employee,$absenceClearing,'SubstractDaysOfVacation',$day,$comment);
              }
            }

          }
        }
        return $this->redirectToRoute('app');
      }
      return $this->render('CalendarBundle:Calendar:globalAbsenceClearing.html.twig', array(
       'form' => $form->createView(),
     ));
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
    /**
    * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PERSONAL') or is_granted('ROLE_HOLIDAY')")
    */
    public function reporterrorsAction(Request $request)
    {
      $em = $this->getDoctrine()->getManager();
      $errors = $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findErrors();
      foreach ($errors as $key=> $value) {
        $errors[$key]["employeeName"] = $em->getRepository('EmployeeBundle:Employee')->find($value["employee_id"])->getFName();
        $errors[$key]["reasonName"] = $em->getRepository('AbsenceBundle:Reason')->find($value["reason_id"])->getName();
      }
      return $this->render('CalendarBundle:Calendar:errors.html.twig',array(
       'errors' => $errors
     ));
    }


}
