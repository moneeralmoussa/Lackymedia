<?php

namespace EmployeeBundle\Controller;

use EmployeeBundle\Entity\WorkingHours;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * WorkingHours controller.
 *
 */
class WorkingHoursController extends Controller
{
    public function editAction(Request $Request)
    {
      $em = $this->getDoctrine()->getManager();
      $workingHours =   new WorkingHours ();
      $id = $Request->query->get('id');
      $workingHours = $em->getRepository('EmployeeBundle:WorkingHours')->find($id);
      $workingHours->setWorkBegin(($Request->query->get('WorkBegin') != '')?(new \DateTime($Request->query->get('WorkBegin'))):null);
      $workingHours->setWorkEnd(($Request->query->get('WorkEnd') != '')?(new \DateTime($Request->query->get('WorkEnd'))):null);
      $workingHours->setAutoBreak(($Request->query->get('AutoPause')== 'true')?1:0);
      $workingHours->setSchool(($Request->query->get('School') == 'true')?1:0);
      $workingHours->setAllowOvertime(($Request->query->get('AllowOvertime') == 'true')?1:0);
      $workingHours->setOvertimeHourlyRate($Request->query->get('OvertimeHourlyRate'));
      $workingHours->setHourlyRate($Request->query->get('HourlyRate'));
      $workingHours->setOvertime($Request->query->get('Overtime'));
      $workingHours->setBreakBegin(($Request->query->get('PauseBegin') != '')?(new \DateTime($Request->query->get('PauseBegin'))):null);
      $workingHours->setBreakEnd(($Request->query->get('PauseEnd') != '')?(new \DateTime($Request->query->get('PauseEnd'))):null);
      if( !empty($workingHours->getWorkBegin()) and  !empty($workingHours->getWorkEnd()) and !empty($workingHours->getBreakBegin()) and !empty($workingHours->getBreakEnd()))
      {
        $workingHours->setOvertime( round(($workingHours->getWorkEnd()->getTimestamp() - $workingHours->getWorkBegin()->getTimestamp()) / 3600, 2) - round(($workingHours->getBreakEnd()->getTimestamp() - $workingHours->getBreakBegin()->getTimestamp()) / 3600, 2) );
      }
      $em->persist($workingHours);
      $em->flush();
      return new JsonResponse([
          'status' 		=> 'true',
          'Overtime' => $workingHours->getOvertime(),
      ]);
    }
    public function deleteAction($id)
    {
      $em = $this->getDoctrine()->getManager();
      $workingHours = $em->getRepository('EmployeeBundle:WorkingHours')->find($id);
      if(empty($workingHours->getDeletedAt()))
      {
          $workingHours->setDeletedAt(new \DateTime());
          $em->persist($workingHours);
          $em->flush();
          return new JsonResponse([
              'status' 		=> 'activated'
          ]);
      }
      else {
          $workingHours->setDeletedAt(null);
          $em->persist($workingHours);
          $em->flush();
          return new JsonResponse([
              'status' 		=> 'deactivated'
          ]);
      }
    }
    public function getByEmployeeAction($id)
    {
      $workingHours =  $this->getDoctrine()->getManager()->getRepository('EmployeeBundle:WorkingHours')->findByEmployeeId($id);
      $result= [] ;
      foreach ($workingHours as $key => $value) {
        $result[$key] = array("dayOfWeek"=>$value->getDayOfWeek(9),
                              "deletedAt"=>$value->getDeletedAt()
                            );
      }
      return new JsonResponse([
        $result
      ]);
    }
}
