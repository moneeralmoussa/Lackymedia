<?php

namespace AbsenceBundle\Controller;

use AbsenceBundle\Entity\Reason;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Reason controller.
 *
 */
class ReasonController extends Controller
{
    /**
     * Lists all reason entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $reasons = $em->getRepository('AbsenceBundle:Reason')->findAll();

        return $this->render('AbsenceBundle:Reason:index.html.twig', array(
            'reasons' => $reasons,
        ));
    }

    /**
     * Creates a new reason entity.
     *
     */
    public function newAction(Request $request)
    {
        $reason = new Reason();
        $form = $this->createForm('AbsenceBundle\Form\ReasonType', $reason);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($reason);
            $em->flush();

            return $this->redirectToRoute('reason_index');
        }

        return $this->render('AbsenceBundle:Reason:new.html.twig', array(
            'reason' => $reason,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a reason entity.
     *
     */
    public function showAction(Reason $reason)
    {
        $deleteForm = $this->createDeleteForm($reason);

        return $this->render('AbsenceBundle:Reason:show.html.twig', array(
            'reason' => $reason,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing reason entity.
     *
     */
    public function editAction(Request $request, Reason $reason)
    {
        $deleteForm = $this->createDeleteForm($reason);
        $editForm = $this->createForm('AbsenceBundle\Form\ReasonType', $reason);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('reason_edit', array('id' => $reason->getId()));
        }

        return $this->render('AbsenceBundle:Reason:edit.html.twig', array(
            'reason' => $reason,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a reason entity.
     *
     */
    public function deleteAction(Request $request, Reason $reason)
    {
        $form = $this->createDeleteForm($reason);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($reason);
            $em->flush();
        }

        return $this->redirectToRoute('reason_index');
    }

    /**
     * Creates a form to delete a reason entity.
     *
     * @param Reason $reason The reason entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Reason $reason)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('reason_delete', array('id' => $reason->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
