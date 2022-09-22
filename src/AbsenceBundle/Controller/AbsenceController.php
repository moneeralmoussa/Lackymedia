<?php

namespace AbsenceBundle\Controller;

use AbsenceBundle\Entity\Absence;
use AbsenceBundle\Entity\AbsenceDetailClearing;
use EmployeeBundle\Entity\AbsenceClearing;
use EmployeeBundle\Entity\Employee;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Carbon\Carbon;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\Common\Collections\ArrayCollection;
use MessageBundle\Entity\Messages;

/**
 * Absence controller.
 *
 */
class AbsenceController extends Controller
{
    /**
     * Lists all absence entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $absences = $em->getRepository('AbsenceBundle:Absence')->findBy(array(), array('fromDate'=>'asc'));
        $deleteForms = array();
        foreach ($absences as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteForm($entity)->createView();
        }
        $startYear = Carbon::create(Carbon::now()->year, 1, 31, 12, 0, 0);
        $endYear = Carbon::create(Carbon::now()->year, 1, 31, 12, 0, 0);

        $absencesSum = $em->getRepository('AbsenceBundle:Absence')
                              ->getSumDays(
                                  $startYear->startOfYear(),
                                  $endYear->endOfYear()
                              );
        $absencesBetween = $em->getRepository('AbsenceBundle:Absence')
                              ->getSumDaysByReason(
                                  $startYear->startOfYear(),
                                  $endYear->endOfYear()
                              );
        $absenceLeftDays = $em->getRepository('AbsenceBundle:Absence')
                              ->getLeftSumDays(
                                  $startYear->startOfYear(),
                                  $endYear->endOfYear()
                              );
        $sumContractDays = $em->getRepository('AbsenceBundle:Absence')->getSumContractHolidays();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_REDAKTEUR')) {
            return $this->render('AbsenceBundle:Absence:index.html.twig', array(
              'absences' => $absences,
              'deleteForms' => $deleteForms,
              'absencesBetween' => $absencesBetween,
              'absencesSum' => $absencesSum,
              'absenceLeftDays' => $absenceLeftDays,
              'carbonnow' => \Carbon\Carbon::now()->year,
              'sumContractDays' => $sumContractDays,
          ));
        }


        return $this->forward('AbsenceBundle:Absence:show', array(
             'employee'  => $this->getUser()->getEmployee(),
         ));
    }

    /**
     * Creates a new absence entity.
     *
     */
    public function newAction(Request $request, Employee $employee = null, $formOnly = false)
    {
        $absence = new Absence();

        if (isset($employee)) {
            $absence->setEmployee($employee);
        }

        $repo = $this->getDoctrine()->getRepository('AbsenceBundle:Status');
        $status = $repo->find(3);
        $em = $this->getDoctrine()->getManager();
        $absenceDetailsClearingService = $this->get('app.absence_details_clearing_service');

        $form = $this->createForm('AbsenceBundle\Form\AbsenceType', $absence);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $absence->setApprovedBy($this->getUser()->getEmployee());
            if (empty($absence->getStatus())) {
                $absence->setStatus($status);
            }
            if ($this->get('security.authorization_checker')->isGranted('ROLE_AZUBI_PERSONAL') )
            {
                if($absence->getEmployee()->getId() == $this->getUser()->getEmployee()->getId())
                $absence->setStatus($status);
            }
            if ($this->get('security.authorization_checker')->isGranted('ROLE_DIALOG_PERSONAL') )
            {
                if($absence->getEmployee()->getId() == $this->getUser()->getEmployee()->getId())
                $absence->setStatus($status);
            }
            $em->persist($absence);
            $em->flush();
            $absenceDetailsClearingService->createOrUpdateAbsenceDetailsFromArray($request->get('absenceoptions'), $employee, $absence);
            $absenceclearingservice = $this->container->get('app.absence_clearing_service', $this->getDoctrine());
            $absenceclearingservice->updateClearing($employee, $absence);
            //email send Function //
            if($absence->getReason()->getId() == 2 and !empty($request->get('absencebundle_absence')['sendInfo']) ){
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
              $bodytext = '<br>dein Urlaubsantrag (Von:'.$absence->getFromDate()->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage wurden genehmigt. <br>Wir wünschen dir einen erholsamen und erlebnisreichen Urlaub und freuen uns über Urlaubsgrüße :-) <br> Schöne Ferien und eine gute Zeit wünscht dir dein G&L Team.<br>';
              $message = (new \Swift_Message('Information zum Urlaub'))
                  ->setFrom('gl@361gradmedien.de')
                  ->setBcc('gl@361gradmedien.de')
                  ->setTo($to)
                  ->setBody( $bname. $bodytext , 'text/html');
              $this->get('mailer')->send($message);
              }
              $bodytext = 'dein Urlaubsantrag (Von:'.$absence->getFromDate()->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage wurden genehmigt. Wir wünschen dir einen erholsamen und erlebnisreichen Urlaub und freuen uns über Urlaubsgrüße. Schöne Ferien und eine gute Zeit wünscht dir dein G&L Team.';
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
              $bodytext = '<br>dein Urlaubsantrag (Von:'.$absence->getFromDate()->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage konnte leider nicht genehmigt werden.<br>Madeleine wird dich zeitnah kontaktieren um mit dir die Gründe zu besprechen und einen neuen Termin zu finden an dem dein Urlaub genehmigt werden kann.<br>Wir sind uns sicher, dass wir eine einvernehmliche Lösung für dich finden.<br>';
              $message = (new \Swift_Message('Information zum Urlaub'))
                  ->setFrom('gl@361gradmedien.de')
                  ->setBcc('gl@361gradmedien.de')
                  ->setTo($to)
                  ->setBody( $bname. $bodytext , 'text/html');
              $this->get('mailer')->send($message);
            }
              $bodytext = 'dein Urlaubsantrag (Von:'.$absence->getFromDate()->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage konnte leider nicht genehmigt werden. Madeleine wird dich zeitnah kontaktieren um mit dir die Gründe zu besprechen und einen neuen Termin zu finden an dem dein Urlaub genehmigt werden kann. Wir sind uns sicher, dass wir eine einvernehmliche Lösung für dich finden.';
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
              $bodytext = '<br>dein Urlaubsantrag (Von:'.$absence->getFromDate()->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) ist eingegangen und wird zeitnah bearbeitet.<br>In den nächsten Tagen erhältst du eine Rückmeldung.<br>Bis dahin wünschen wir dir eine stressfreie Zeit.<br>Schöne Grüße dein G&L Team<br>';
              $message = (new \Swift_Message('Information zum Urlaub'))
                  ->setFrom('gl@361gradmedien.de')
                  ->setBcc('gl@361gradmedien.de')
                  ->setTo($to)
                  ->setBody( $bname. $bodytext , 'text/html');
              $this->get('mailer')->send($message);
            }
            }}
            //end email send funciton //
            return $this->redirectToRoute('calendar_show', array('id' => $absence->getEmployee()->getId()));
        }

        if ($formOnly) {
            return $this->render('AbsenceBundle:Absence:partials/form.html.twig', array(
          'absence' => $absence,
          'form' => $form->createView(),
          'employee' => $employee,
        ));
        } else {
            return $this->render('AbsenceBundle:Absence:new.html.twig', array(
          'absence' => $absence,
          'form' => $form->createView(),
          'employee' => $employee,
        ));
        }
    }

    /**
     * Finds and displays a absence entity.
     *
     */
    public function showAction(\EmployeeBundle\Entity\Employee $employee)
    {
        // $em = $this->getDoctrine()->getManager();
        // $absences = $em->getRepository('AbsenceBundle:Absence')->findById($employee->getID());
        $repo = $this->getDoctrine()
                   ->getRepository('EmployeeBundle:Contract');
        $contract = $repo->find(4); //Standardvertrag

        $repo = $this->getDoctrine()
                   ->getRepository('AbsenceBundle:Status');
        $status = $repo->find(3); //Standardvertrag

        if (!isset($employee->contract)) {
            $employee->setContract($contract);
        }

        if (
        (!($this->get('security.authorization_checker')->isGranted('ROLE_REDAKTEUR'))
        or
        !($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')))
      ) {
            $usr = $this->container->get('security.context')->getToken()->getUser();
            if (!is_null($employee) && !is_null($usr->getEmployee())) {
                if ($employee->getId() != $usr->getEmployee()->getId()) {
                    throw new AccessDeniedException();
                }
            }
        }
      return $this->render('AbsenceBundle:Absence:show.html.twig', array(
            'employee' => $employee,
            'absencemerged' => $this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getDaysByReason($employee),
            'absencecount' => $this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getHolidays($employee, 1),
      ));
    }

    /**
     * Displays a form to edit an existing absence entity.
     *
     */
    public function editAction(Request $request, Absence $absence)
    {
        $editForm = $this->createForm('AbsenceBundle\Form\AbsenceType', $absence);
        $editForm->handleRequest($request);

        if ($this->get('security.authorization_checker')->isGranted('ROLE_REDAKTEUR')) {
            $absence->setApprovedBy($this->getUser()->getEmployee());
        }

        $em = $this->getDoctrine()->getManager();

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $absenceDetailsClearingService = $this->get('app.absence_details_clearing_service');
            $absenceDetailsClearingService->createOrUpdateAbsenceDetailsFromArray($request->get('absenceoptions'), $absence->getEmployee(), $absence);
            $absenceclearingservice = $this->container->get('app.absence_clearing_service', $this->getDoctrine());
            $absenceclearingservice->updateClearing($absence->getEmployee(), $absence);
            $employee = $absence->getEmployee();
            //email send Function //
            if($absence->getReason()->getId() == 2 and !empty($request->get('absencebundle_absence')['sendInfo']) ){
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
              $bodytext = '<br>dein Urlaubsantrag (Von:'.$absence->getFromDate()->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage wurden genehmigt. <br>Wir wünschen dir einen erholsamen und erlebnisreichen Urlaub und freuen uns über Urlaubsgrüße :-) <br> Schöne Ferien und eine gute Zeit wünscht dir dein G&L Team.<br>';
              $message = (new \Swift_Message('Information zum Urlaub'))
                  ->setFrom('gl@361gradmedien.de')
                  ->setBcc('gl@361gradmedien.de')
                  ->setTo($to)
                  ->setBody( $bname. $bodytext , 'text/html');
              $this->get('mailer')->send($message);
            }
              $bodytext = 'dein Urlaubsantrag (Von:'.$absence->getFromDate()->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage wurden genehmigt. Wir wünschen dir einen erholsamen und erlebnisreichen Urlaub und freuen uns über Urlaubsgrüße. Schöne Ferien und eine gute Zeit wünscht dir dein G&L Team.';
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
              $bodytext = '<br>dein Urlaubsantrag (Von:'.$absence->getFromDate()->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage konnte leider nicht genehmigt werden.<br>Madeleine wird dich zeitnah kontaktieren um mit dir die Gründe zu besprechen und einen neuen Termin zu finden an dem dein Urlaub genehmigt werden kann.<br>Wir sind uns sicher, dass wir eine einvernehmliche Lösung für dich finden.<br>';
              $message = (new \Swift_Message('Information zum Urlaub'))
                  ->setFrom('gl@361gradmedien.de')
                  ->setBcc('gl@361gradmedien.de')
                  ->setTo($to)
                  ->setBody( $bname. $bodytext , 'text/html');
              $this->get('mailer')->send($message);
              }
              $bodytext = 'dein Urlaubsantrag (Von:'.$absence->getFromDate()->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) wurde bearbeitet und die Anfrage deiner Urlaubstage konnte leider nicht genehmigt werden. Madeleine wird dich zeitnah kontaktieren um mit dir die Gründe zu besprechen und einen neuen Termin zu finden an dem dein Urlaub genehmigt werden kann. Wir sind uns sicher, dass wir eine einvernehmliche Lösung für dich finden.';
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
              $bodytext = '<br>dein Urlaubsantrag (Von:'.$absence->getFromDate()->format('d.m.Y').' bis:'.$absence->getToDate()->format('d.m.Y').' Tagen:'.$absence->getDay().' ) ist eingegangen und wird zeitnah bearbeitet.<br>In den nächsten Tagen erhältst du eine Rückmeldung.<br>Bis dahin wünschen wir dir eine stressfreie Zeit.<br>Schöne Grüße dein G&L Team<br>';
              $message = (new \Swift_Message('Information zum Urlaub'))
                  ->setFrom('gl@361gradmedien.de')
                  ->setBcc('gl@361gradmedien.de')
                  ->setTo($to)
                  ->setBody( $bname. $bodytext , 'text/html');
              $this->get('mailer')->send($message);
            }
            }}
            //end email send funciton //

            return $this->redirectToRoute('calendar_show', array('id' => $absence->getEmployee()->getId()));
        }

        $deleteForm = $this->createDeleteForm($absence);

        return $this->render('AbsenceBundle:Absence:edit.html.twig', array(
            'absence' => $absence,
            'employee' => $absence->getEmployee(),
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
   * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PERSONAL') or is_granted('ROLE_HOLIDAY') or is_granted('ROLE_DIALOG_PERSONAL')")
   */
    public function deleteAction(Request $request, Absence $absence)
    {
        $form = $this->createDeleteForm($absence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo = $this->getDoctrine()->getRepository('AbsenceBundle:AbsenceDetailClearing');
            $absenceDetailsClearingService = $this->get('app.absence_details_clearing_service');
            $absenceDetailsClearingService->removeByAbsence($absence);
            $em = $this->getDoctrine()->getManager();
            $em->remove($absence);
            $em->flush();
        }

        $absenceclearingservice = $this->container->get('app.absence_clearing_service', $this->getDoctrine());
        $absenceclearingservice->updateClearing($absence->getEmployee(), $absence);

        return $this->redirectToRoute('calendar_show', array( 'id' => $absence->getEmployee()->getId()));
    }

    public function calendarAction()
    {
        return $this->render('AbsenceBundle:Absence:calendar.html.twig');
    }

    /**
     * Creates a form to delete a absence entity.
     *
     * @param Absence $absence The absence entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Absence $absence)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('absence_delete', array('id' => $absence->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    public function jsonAbsenceAction(Request $request, Employee $employee = null, $background = false)
    {
        $usr = $this->container->get('security.context')->getToken()->getUser();
        if (!is_null($employee)) {
            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL') && !$this->get('security.authorization_checker')->isGranted('ROLE_HOLIDAY') && !$this->get('security.authorization_checker')->isGranted('ROLE_AZUBI_PERSONAL') && !$this->get('security.authorization_checker')->isGranted('ROLE_DIALOG_PERSONAL')) {
                if ($employee->getId() != $usr->getEmployee()->getId()) {
                    throw new AccessDeniedException();
                }
            }
        }

        $absenceService = $this->get('app.absence_service', $this->getDoctrine());
        $absencesJson = $absenceService->getAbsenceJson($employee, $background);

        return new JsonResponse($absencesJson);
    }

    public function teamAbsenceAction(Request $request, Employee $employee = null, $background = false)
    {
        $repoEmployee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee');
        $repoAbsence = $this->getDoctrine()->getRepository('AbsenceBundle:Absence');
        $repoAbsenceDetailClearing = $this->getDoctrine()->getRepository('AbsenceBundle:AbsenceDetailClearing');

        $departmentID = $employee->getDepartment()->getID();
        $employees = $repoEmployee->findAll();

        $arr_absences = new ArrayCollection();
        foreach ($employees as $employee) {
            $absences = $repoAbsence->findBy(array('employee' => $employee->getID(), 'status' => '1', 'reason' => '2'));
            foreach ($absences as $absence) {
                $arr_absences->add($absence);
            }
        }

        $arrayCollection = array();
        foreach ($arr_absences as $item) {
            if ($background) {
                $arrayCollection[] =
              array(
                  'title' => $item->getReason()->getName(),
                  'start' => $item->getFromDate()->format('Y-m-d'),
                  'end' => $item->getToDate()->modify('+1 day')->format('Y-m-d'),
                  'backgroundColor' => $item->getReason()->getColor(),
                  //'rendering' => 'background',
                  'background' => true,
                  'icon' => 'check-circle-o',
              );
            } else {
                $arrayCollection[] =
              array(
                   'id' => $item->getId(),
                   'name' => $item->getEmployee()->getFullname(),
                   'color' => $item->getReason()->getColor(),
                   'reason' => $item->getReason()->getName(),
                   'day' => array_sum(array_column($repoAbsenceDetailClearing->findAllWithAbsenceAbsenceDetailClearings($item->getEmployee(), $item->getFromDate(), $item->getToDate(), $item), 'dayDetail')),
                   'dayDetail' => $repoAbsenceDetailClearing->findAllWithAbsenceAbsenceDetailClearings($item->getEmployee(), $item->getFromDate(), $item->getToDate(), $item),
                   'approvedBy' => $item->getApprovedBy()->getFullname(),
                   'status' => $item->getStatus()->getName(),
                   'note' => $item->getNote(),
                   'startDate' => $item->getFromDate(),
                   'endDate' => $item->getToDate()
               );
            }
        }

        return new JsonResponse($arrayCollection);
    }

    public function jsonAbsenceCalendarAction(Request $request)
    {


        $repo = $this->getDoctrine()->getRepository('AbsenceBundle:Absence');
        $date= new \DateTime($request->get('start'));

        $absence = $repo->findAllAbsencesByYearCalendarJson($date->format("Y"));
        $sumdays = $this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getSumDaysByDepartment();
        $arrayCollection = array();
        $i = 0;
        foreach ($absence as $item) {
            $icon = '';
            if ($item->getStatus()->getId() == 1) {
                $icon = 'check-circle-o';
            }
            if (!is_null($item->getReason())) {
                $arrayCollection[] =
          array(
               'id'               => $item->getId(),
               'resourceIds'      => [$item->getEmployee()->getId(), "alle".$item->getEmployee()->getId()],
               'start'            => $item->getFromDate()->format('Y-m-d'),
               'end'              => $item->getToDate()->modify('+1 day')->format('Y-m-d'),
               'title'            => $item->getReason()->getName(),
               'backgroundColor'  => $item->getReason()->getColor(),
               'icon'             => $icon,
               'halfDays' => $this->getDoctrine()->getRepository('AbsenceBundle:AbsenceDetailClearing')->getHalfDays($item),
           );
            }
        }
        foreach ($sumdays as $days) {
            array_push($arrayCollection, array(
          'id'               => $days['date'].$i,
          'resourceId'       => !is_null($days['department']) ? $days['department'] : 'nicht Zugeordnet',
          'start'            => Carbon::parse($days['date'])->format('Y-m-d'),
          'end'              => Carbon::parse($days['date'])->format('Y-m-d'),
          'title'            => $days['days'],
          'backgroundColor'  => '#000'
        ));
            $i++;
        }
        // array_push($arrayCollection,array(
        //   'id'               => "a",
        //   'resourceId'       => 44,
        //   'start'            => "2017-08-01 00:00:00",
        //   'end'              => "2017-08-01 23:59:00",
        //   'title'            => "2",
        //   'backgroundColor'  => "#fff"
        // ));
        return new JsonResponse($arrayCollection);
    }

    public function jsonAbsenceDaysAction(Request $carbon = null)
    {
        if (!($this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL')) or !($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) && !$this->get('security.authorization_checker')->isGranted('ROLE_HOLIDAY')) {
            throw new AccessDeniedException();
        }
        $startYear = Carbon::create(Carbon::now()->year, 1, 31, 12, 0, 0);
        $endDate = Carbon::parse($carbon->get('date'));
        $em = $this->getDoctrine()->getManager();
        $absencesLeftSumDays = $em->getRepository('AbsenceBundle:Absence')
                            ->getLeftSumDays(
                                $startYear->startOfYear(),
                                $endDate
                            );
        return new JsonResponse($absencesLeftSumDays);
    }

    public function newabsencemailAction()
    {
        $newAbsences = $this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getNewAbsences((new \DateTime())->modify('midnight -1 day'));
        $illWithoutCertification = $this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getIllWithoutCertification((new \DateTime())->modify('-14 days'));

        if (count($newAbsences)>0 || count($illWithoutCertification)>0) {
            $to = array(
                'slaakmann@giesker-laakmann.de',
            );
            $cc = array(
                'hlaakmann@giesker-laakmann.de',
                'mbusch@giesker-laakmann.de',
            );

            $message = (new \Swift_Message('Änderungen im Urlaubskalender'))
                ->setFrom('gl@361gradmedien.de')
                ->setTo($to)
                ->setCc($cc)
                ->setBcc('gl@361gradmedien.de')
                ->setBody(
                    $this->renderView(
                        'AbsenceBundle:Absence:email.html.twig',
                        array(
                            'newAbsences' => $newAbsences,
                            'illWithoutCertification' => $illWithoutCertification,
                        )
                    ),
                    'text/html'
                );
            $this->get('mailer')->send($message);
        }

        return $this->render('AbsenceBundle:Absence:email.html.twig', array(
            'newAbsences' => $newAbsences,
            'illWithoutCertification' => $illWithoutCertification,
        ));
    }

    public function jsonAbsenceDetailsClearingAction(Request $request)
    {
        $input = $request->request->all();
        $repo = $this->getDoctrine()->getRepository('AbsenceBundle:AbsenceDetailClearing');
        $absenceDetailClearing = $repo->findAllAbsenceDetailClearings(
            $input['employee'],
            Carbon::parse($input['start']),
            Carbon::parse($input['end'])
        );
        return new JsonResponse($absenceDetailClearing);
    }

    public function delAbsenceclearingrecordAction(Request $request)
    {
        if ( $this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL') or $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') )
        {
            $id = $request->get("id");
            $em = $this->getDoctrine()->getManager();
            $absenceClearingRecords = $em->getRepository('AbsenceBundle:AbsenceClearingRecords')->find($id);
            if(!empty($absenceClearingRecords))
            {
              $absenceClearingRecords->setDeletedAt(new \Datetime());
              $absenceClearing=$absenceClearingRecords->getAbsenceClearing();
              if($absenceClearingRecords->getType()== "SubstractDaysOfVacation")
              {
                $absenceClearing->setSubstractDaysOfVacation($absenceClearing->getSubstractDaysOfVacation() - $absenceClearingRecords->getValue());
              }
              if($absenceClearingRecords->getType()== "AdditionalDaysOfVacation")
              {
                $absenceClearing->setAdditionalDaysOfVacation($absenceClearing->getAdditionalDaysOfVacation() -  $absenceClearingRecords->getValue());
              }
              $em->persist($absenceClearing);
              $em->flush();
              $em->persist($absenceClearingRecords);
              $em->flush();
            return new JsonResponse(true);
            }
            else {
              return new JsonResponse(false);
            }
      }
        return new JsonResponse(false);
    }
}
