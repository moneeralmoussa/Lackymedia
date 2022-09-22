<?php

namespace MessageBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MessageBundle\Entity\Messages;
use Symfony\Component\HttpFoundation\JsonResponse;
class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
     public function indexAction(Request $request)
     {
       $em = $this->getDoctrine()->getManager();
       $type =$request->get("type");
       $employee =$this->getUser()->getEmployee();
       $messages = $em->getRepository('MessageBundle:Messages')->findByEmployeeAndType($employee->getId(),$type);
       return $this->render('MessageBundle:Default:index.html.twig', array(
           'messages' => $messages,
       ));
     }
    public function  newAction(Request $request)
    {
       $message = new Messages();
       $form = $this->createForm('MessageBundle\Form\MessageType', $message);
       $form->handleRequest($request);
       if ($form->isSubmitted() && $form->isValid()) {
         $type = $request->get("Messagebundle_Message")["type"];
         $messagetext = $request->get("Messagebundle_Message")["message"];
         $group =$request->get("Messagebundle_Message")["group"];
         if($group == 1) $departments = $request->get("Messagebundle_Message")["departments"];
         if($group == 2) $employees =$request->get("Messagebundle_Message")["employee"];
         $em = $this->getDoctrine()->getManager();
         if($group == 1)//department
         {
           foreach($departments as $department_id )
           {
             $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->findByDepartment($department_id);
             if(!empty($employees)){
                 foreach ($employees as $employee) {
                   $message = new Messages();
                   $message->setEmployee($employee);
                   $message->setMessage($messagetext);
                   $message->setType($type);
                   $em->persist($message);
                   $em->flush();
                 }
             }
           }
         }
         elseif($group == 2 and !empty($employees))//employee
         {
           foreach ($employees as $employee) {
             $message = new Messages();
             $message->setEmployee($this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee));
             $message->setMessage($messagetext);
             $message->setType($type);
             $em->persist($message);
             $em->flush();
           }
         }
         return $this->redirectToRoute('app');
       }
       return $this->render('MessageBundle:Default:messageCreate.html.twig', array(
        'form' => $form->createView(),
    ));
   }

   public function deleteAction(Request $request)
   {
     $em = $this->getDoctrine()->getManager();
     $messages = $em->getRepository('MessageBundle:Messages')->find($request->get("id"));
     $messages->setDeletedAt(new \DateTime());
     $em->persist($messages);
     $em->flush();
     return new JsonResponse([
         'status'            => true
     ]);
   }
}
