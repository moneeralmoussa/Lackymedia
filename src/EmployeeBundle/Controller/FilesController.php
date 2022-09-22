<?php

namespace EmployeeBundle\Controller;

use Carbon\Carbon;
use EmployeeBundle\Entity\Employee;
use EmployeeBundle\Entity\Department;
use EmployeeBundle\Entity\Files;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LocationBundle\Entity\Location;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * User controller.
 *
 */
class FilesController extends Controller
{
    public function indexAction(Request $request)
    {
      $em = $this->getDoctrine()->getManager();
        $type = array("application/pdf", "image/png", "image/jpeg");
        if (!is_null($request->files->get('excelfile')) && in_array($request->files->get('excelfile')->getmimeType() ,  $type)) {
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            $em = $this->getDoctrine()->getManager();
            $uploadService = $this->get('app.upload_service');
            $fileName = $uploadService->uploadPhoto($request->files->get('excelfile'));
            $title= $request->get('title');
            $info= $request->get('info');
            $type = $request->get('optionsRadios');
            $em = $this->getDoctrine()->getManager();
            $file = new Files();
            $file->setEmployee($this->getUser()->getEmployee());
            $file->setTitle($title);
            $file->setInfo($info);
            $file->setType($type);
            $file->setName($fileName);
            $em->persist($file);
            $em->flush();
            if($type == "Abrechnung")
            {
                $to = array(
                    'mbusch@giesker-laakmann.de',
                );
            }
            else if($type == "Krankenschein")
            {
                $to = array(
                    'mbusch@giesker-laakmann.de',
                );
            }
            else if($type == "Spesen")
            {
                $to = array(
                    'mbusch@giesker-laakmann.de',
                );
            }
            else
            {
                $to = array(
                    'fhoeing@giesker-laakmann.de',
                );
            }

            $cc = array(
                'malmoussa@giesker-laakmann.de',
            );
            $message = (new \Swift_Message('Dokumentenupload'))
                    ->setFrom('gl@361gradmedien.de')
                    ->setTo($to)
                    ->setCc($cc)
                    ->setBcc('gl@361gradmedien.de')
                    ->setBody('Hallo,<br> Mitarbeiter '.$this->getUser()->getEmployee()->getFName().' hat ein Dokument ('.$type.') hochgeladen  .<br><br>Titel: '.$title.'<br>Informationen: '.$info .'<br><a href="https://management.giesker-laakmann.de/'.$fileName.'">Download</a>', 'text/html');
                $this->get('mailer')->send($message);
               return $this->render('EmployeeBundle:upload:indexUpload.html.twig', array(
                    'step' => 2,
                ));
            }
        return $this->render('EmployeeBundle:upload:indexUpload.html.twig', array(
            'step' => 1,
            'inserted' => 0,
        ));
     }

       public function myPhotosAction(Request $request)
       {
         $employee= $this->getUser()->getEmployee();
         $em = $this->getDoctrine()->getManager();
         $files = $em->getRepository('EmployeeBundle:Files')->findByEmployee($employee);
         return $this->render('EmployeeBundle:upload:showMyPhotos.html.twig', array(
             'files' => $files,
         ));
       }

       public function showPhotosAction(Request $request)
       {

           $employee= $this->getUser()->getEmployee();
           $em = $this->getDoctrine()->getManager();
           if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL') )
           {
           $files = $em->getRepository('EmployeeBundle:Files')->findAllFiles();
           }else
           {
             $files = $em->getRepository('EmployeeBundle:Files')->findByEmployee($employee);
             return $this->render('EmployeeBundle:upload:showMyPhotos.html.twig', array(
                 'files' => $files,
             ));
           }
           return $this->render('EmployeeBundle:upload:showPhotos.html.twig', array(
               'files' => $files,
           ));
       }
       public function delPhotoAction($name,$id)
       {
         $em = $this->getDoctrine()->getManager();
         $file = $em->getRepository('EmployeeBundle:Files')->find($id);
         $file->setDeletedAt(new \Datetime());
         $em->persist($file);
         $em->flush();
         return $this->redirectToRoute('showPhotos');
       }
}
