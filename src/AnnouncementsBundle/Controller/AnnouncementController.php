<?php

namespace AnnouncementsBundle\Controller;

use AnnouncementsBundle\Entity\Announcement;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Announcement controller.
 *
 */
class AnnouncementController extends Controller
{
    /**
     * Lists all announcement entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $announcements = $em->getRepository('AnnouncementsBundle:Announcement')->findAll();

        $announcementsActive = $em->getRepository('AnnouncementsBundle:Announcement')->getAllNotExpired();
        $announcementsExpired = $em->getRepository('AnnouncementsBundle:Announcement')->getAllExpired();

        $deleteForms = array();

        foreach ($announcements as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteForm($entity)->createView();
        }

        return $this->render('AnnouncementsBundle:Announcement:index.html.twig', array(
            'announcements' => $announcements,
            'announcementsActive' => $announcementsActive,
            'announcementsExpired' => $announcementsExpired,
            'delete_forms' => $deleteForms,
        ));
    }


    /**
     * Creates a new announcement entity.
     *
     */
    public function newAction(Request $request)
    {
        $announcement = new Announcement();
        $form = $this->createForm('AnnouncementsBundle\Form\AnnouncementType', $announcement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $announcement->setAuthorID($this->getUser()->getEmployee()->getId());
            $em->persist($announcement);
            $em->flush();

            return $this->redirectToRoute('announcement_show', array('id' => $announcement->getId()));
        }

        return $this->render('AnnouncementsBundle:Announcement:new.html.twig', array(
            'announcement' => $announcement,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a announcement entity.
     *
     */
    public function showAction(Announcement $announcement)
    {
        $deleteForm = $this->createDeleteForm($announcement);

        return $this->render('AnnouncementsBundle:Announcement:show.html.twig', array(
            'announcement' => $announcement,
            'delete_form' => $deleteForm->createView(),
        ));
    }
    public function berufskraftfahrerAction()
    {
        return $this->render('AnnouncementsBundle:Announcement:berufskraftfahrer.html.twig');
     
    }
    /**
     * Displays a form to edit an existing announcement entity.
     *
     */
    public function editAction(Request $request, Announcement $announcement)
    {
        $deleteForm = $this->createDeleteForm($announcement);
        $editForm = $this->createForm('AnnouncementsBundle\Form\AnnouncementType', $announcement);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $announcement->setAuthorID($this->getUser()->getEmployee()->getId());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('announcement_edit', array('id' => $announcement->getId()));
        }

        return $this->render('AnnouncementsBundle:Announcement:edit.html.twig', array(
            'announcement' => $announcement,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a announcement entity.
     *
     */
    public function deleteAction(Request $request, Announcement $announcement)
    {
        $form = $this->createDeleteForm($announcement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $announcement->setAuthorID($this->getUser()->getEmployee()->getId());
            $em->remove($announcement);
            $em->flush();
        }

        return $this->redirectToRoute('announcement_index');
    }

    /**
     * Creates a form to delete a announcement entity.
     *
     * @param Announcement $announcement The announcement entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Announcement $announcement)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('announcement_delete', array('id' => $announcement->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
