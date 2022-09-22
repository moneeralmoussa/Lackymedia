<?php

namespace EmployeeBundle\Controller;

use EmployeeBundle\Entity\WorkingHoursPause;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


/**
 * WorkingHoursPause controller.
 *
 */
class WorkingHoursPauseController extends Controller
{
  public function showAction()
  {
    $em = $this->getDoctrine()->getManager();
    $WorkingHoursPause = $em->getRepository('EmployeeBundle:WorkingHoursPause')->findAll();
    return $this->render('EmployeeBundle:WorkingHoursPause:index.html.twig', array(
        'WorkingHoursPause' => $WorkingHoursPause,
    ));
  }

  public function newAction(Request $request)
  {
      $workingHoursPause = new WorkingHoursPause();
      $form = $this->createForm('EmployeeBundle\Form\WorkingHoursPauseType', $workingHoursPause);
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $em->persist($workingHoursPause);
          $em->flush();
          return $this->redirectToRoute('WorkingHoursShow');
      }
      return $this->render('EmployeeBundle:WorkingHoursPause:new.html.twig', array(
          'workingHoursPause' => $workingHoursPause,
          'form' => $form->createView(),
      ));
  }
}
