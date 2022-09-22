<?php

namespace ExpenseBundle\Controller;

use ExpenseBundle\Entity\Countryspecificexpenses;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Countryspecificexpense controller.
 *
 */
class CountryspecificexpensesController extends Controller
{
    /**
     * Lists all countryspecificexpense entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $countryspecificexpenses = $em->getRepository('ExpenseBundle:Countryspecificexpenses')->findAll();

        return $this->render('ExpenseBundle:Countryspecificexpenses:index.html.twig', array(
            'countryspecificexpenses' => $countryspecificexpenses,
        ));
    }

    /**
     * Creates a new countryspecificexpense entity.
     *
     */
    public function newAction(Request $request)
    {
        $countryspecificexpense = new Countryspecificexpenses();
        $form = $this->createForm('ExpenseBundle\Form\CountryspecificexpensesType', $countryspecificexpense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($countryspecificexpense);
            $em->flush();

            return $this->redirectToRoute('expenses_countryspecificexpenses_show', array('id' => $countryspecificexpense->getId()));
        }

        return $this->render('ExpenseBundle:Countryspecificexpenses:new.html.twig', array(
            'countryspecificexpense' => $countryspecificexpense,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a countryspecificexpense entity.
     *
     */
    public function showAction(Countryspecificexpenses $countryspecificexpense)
    {
        $deleteForm = $this->createDeleteForm($countryspecificexpense);

        return $this->render('ExpenseBundle:Countryspecificexpenses:show.html.twig', array(
            'countryspecificexpense' => $countryspecificexpense,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing countryspecificexpense entity.
     *
     */
    public function editAction(Request $request, Countryspecificexpenses $countryspecificexpense)
    {
        $deleteForm = $this->createDeleteForm($countryspecificexpense);
        $editForm = $this->createForm('ExpenseBundle\Form\CountryspecificexpensesType', $countryspecificexpense);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('expenses_countryspecificexpenses_edit', array('id' => $countryspecificexpense->getId()));
        }

        return $this->render('ExpenseBundle:Countryspecificexpenses:edit.html.twig', array(
            'countryspecificexpense' => $countryspecificexpense,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a countryspecificexpense entity.
     *
     */
    public function deleteAction(Request $request, Countryspecificexpenses $countryspecificexpense)
    {
        $form = $this->createDeleteForm($countryspecificexpense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($countryspecificexpense);
            $em->flush();
        }

        return $this->redirectToRoute('expenses_countryspecificexpenses_index');
    }

    /**
     * Creates a form to delete a countryspecificexpense entity.
     *
     * @param Countryspecificexpenses $countryspecificexpense The countryspecificexpense entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Countryspecificexpenses $countryspecificexpense)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('expenses_countryspecificexpenses_delete', array('id' => $countryspecificexpense->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
