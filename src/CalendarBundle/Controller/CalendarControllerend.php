<?php

namespace CalendarBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use EmployeeBundle\Entity\Employee;
use AbsenceBundle\Entity\Absence;
use AbsenceBundle\Entity\PublicHoliday;
use AbsenceBundle\Entity\Vacationlock;
use AbsenceBundle\Entity\AbsenceDetailClearing;

class CalendarController extends Controller
{
    /**
   * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PERSONAL') or is_granted('ROLE_HOLIDAY')")
   */
    public function indexAction(Request $request)
    {
        $absence = new Absence();
        $form = $this->createForm('CalendarBundle\Form\CalendarModalAbsenceAddType', $absence, [
            'user' => $this->getUser()
        ]);

        return $this->render('CalendarBundle:Calendar:index.html.twig', [
              'form' => $form->createView(),
        ]);
    }

    public function showAction(Employee $employee)
    {
        $usr = $this->getUser();
        if (!is_null($employee)) {
            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL') && !$this->get('security.authorization_checker')->isGranted('ROLE_HOLIDAY')) {
                if ($employee->getId() != $usr->getEmployee()->getId()) {
                    throw new AccessDeniedException();
                }
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
            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL') && !$this->get('security.authorization_checker')->isGranted('ROLE_HOLIDAY')) {
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
        if ($request->isXmlHttpRequest()) {
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
            ////////
            if($reason->getId() == '2' ){
                if($status->getId() == '1' or $status->getId() == '3')
                     {  $repoEmployee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee');
                        $employee = $repoEmployee->find($request->get('employee'));
                        $em = $this->getDoctrine()->getManager();
                        $workingdays =  $em->getRepository('AbsenceBundle:Absence')->findBy(array('employee' => $employee, 'reason' => 5));
                        
                        $publicHolidays = $em->getRepository('AbsenceBundle:PublicHoliday')->findBy(array('type' => 1));
                        // $absences = $em->getRepository('AbsenceBundle:Absence')->findAllAbsencesByYear($employee, 1, $year);
                        // $publicHolidays = $em->getRepository('AbsenceBundle:PublicHoliday')->findByYear($year);
                        
                        $arrPublicHolidays = [];
                       
                        foreach ($publicHolidays as $ph) {
                            $arrPublicHolidays[] = $ph->getStart()->format('Y-m-d');
                        }
                
                        foreach ($workingdays as $wd) {
                            $arrPublicHolidays[] = $wd->getFromDate()->format('Y-m-d');
                        }
                
                        
                            $period = CarbonPeriod::create($absence->getFromDate(), $absence->getToDate()->modify('-1 Day'));
                
                            $days = Carbon::instance($absence->getFromDate())->startOfDay()->diffInDaysFiltered(function (Carbon $date) use ($arrPublicHolidays) {
                                return !$date->isWeekend() && !in_array($date->format('Y-m-d'), $arrPublicHolidays);
                            }, Carbon::instance($absence->getToDate())->endOfDay());
                
                            foreach ($period as $date) {
                                if (in_array($date->format('Y-m-d'), $arrPublicHolidays) || $date->isWeekend()) {
                                    continue;
                                }
                
                                $absenceDetailClearing = new AbsenceDetailClearing();
                
                                $absenceDetailClearing->setEmployee($employee);
                
                                $absenceDetailClearing->setReason($absence->getReason());
                
                                $absenceDetailClearing->setUseAsHolidays($absence->getReason()->getUseAsHolidays());
                
                                $absenceDetailClearing->setDate($date);
                
                                $absenceDetailClearing->setDayDetail(1);
                
                                if ((abs(floatval($absence->getDay()) - $days) == 0.5) && $date->format('Y-m-d') == $period->getEndDate()->format('Y-m-d')) {
                                    $absenceDetailClearing->setDayDetail(0.5);
                                }
                
                                $absenceDetailClearing->setAbsence($absence);
                                $em->persist($absenceDetailClearing);
                                $em->flush();
    
                                $absenceclearingservice = $this->container->get('app.absence_clearing_service', $this->getDoctrine());
                              $absenceclearingservice->updateClearing($employee, $absence);
                
                       }   }          
             }
             ///////////
        }
        return $this->redirectToRoute('calendar_index');
    }

    public function jsonPublicHolidaysbyYearAction(Request $request)
    {
        $year = $request->get('year');
        $publicHolidayService = $this->get('app.public_holiday_service');

        if (!$publicHolidayService->checkIfYearIsSaved($year)) {
            $publicHolidayService->getPublicHolidaysFromAPIandSave($year);
            $publicHolidayService->getVacationsFromAPIandSave($year);
        }
        return new JsonResponse();
    }

    public function jsonPublicHolidaysAction()
    {
        $publicHolidays = $this->getDoctrine()->getRepository('AbsenceBundle:PublicHoliday')->findAll();

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
}
