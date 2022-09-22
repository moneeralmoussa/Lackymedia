<?php

namespace EmployeeBundle\Controller;

use EmployeeBundle\Entity\Workingtime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Workingtime controller.
 *
 */
class WorkingtimeController extends Controller
{
    /**
     * Lists all workingtime entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $workingtimes = $em->getRepository('EmployeeBundle:Workingtime')->findAll();

        return $this->render('EmployeeBundle:Workingtime:index.html.twig', array(
            'workingtimes' => $workingtimes,
        ));
    }

    /**
     * Creates a new workingtime entity.
     *
     */
    public function newAction($contract_id, Request $request)
    {
        $workingtime = new Workingtime();
        $form = $this->createForm('EmployeeBundle\Form\WorkingtimeType', $workingtime);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($workingtime);
            $em->flush();

            return $this->redirectToRoute('contract_workingtime_show', array('id' => $workingtime->getId()));
        }

        if (!$form->isSubmitted()) {
            $contract = $this->getDoctrine()->getRepository('EmployeeBundle:Contract')->find($contract_id);
            $form->get('contract')->setData($contract);
        }

        return $this->render('EmployeeBundle:Workingtime:new.html.twig', array(
            'workingtime' => $workingtime,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a workingtime entity.
     *
     */
    public function showAction(Workingtime $workingtime)
    {
        $deleteForm = $this->createDeleteForm($workingtime);

        return $this->render('EmployeeBundle:Workingtime:show.html.twig', array(
            'workingtime' => $workingtime,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing workingtime entity.
     *
     */
    public function editAction(Request $request, Workingtime $workingtime)
    {
        $deleteForm = $this->createDeleteForm($workingtime);
        $editForm = $this->createForm('EmployeeBundle\Form\WorkingtimeType', $workingtime);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('contract_workingtime_edit', array('id' => $workingtime->getId()));
        }

        return $this->render('EmployeeBundle:Workingtime:edit.html.twig', array(
            'workingtime' => $workingtime,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a workingtime entity.
     *
     */
    public function deleteAction(Request $request, Workingtime $workingtime)
    {
        $form = $this->createDeleteForm($workingtime);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($workingtime);
            $em->flush();
        }

        return $this->redirectToRoute('contract_workingtime_index');
    }

    /**
     * Creates a form to delete a workingtime entity.
     *
     * @param Workingtime $workingtime The workingtime entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Workingtime $workingtime)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('contract_workingtime_delete', array('id' => $workingtime->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
