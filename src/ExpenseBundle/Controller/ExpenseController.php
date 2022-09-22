<?php

namespace ExpenseBundle\Controller;

use Carbon\Carbon;
use ExpenseBundle\Entity\Expense;
use ExpenseBundle\Form\ExpenseType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExpenseController extends Controller
{
    public function indexAction()
    {
        $expenses = $this->getDoctrine()->getRepository('ExpenseBundle:Expense')->findAll();

        return $this->render('ExpenseBundle:Expense:index.html.twig', array(
            'expenses' => $expenses
        ));
    }

    public function indexEmployeeAction()
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL')){
            throw new AccessDeniedException();
        }
        $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllAvailable();
        $employees_deleted = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllSoftDeleted();
        $base_date = new \DateTime();
        $base_date->modify('- 1 month');

        $provenexpense = $this->getDoctrine()->getRepository('ExpenseBundle:Provenexpense');

        return $this->render('ExpenseBundle:Expense:indexEmployee.html.twig', array(
            'employees' => $employees,
            'employees_deleted' => $employees_deleted,
            'base_date' => $base_date,
            'provenexpense' => $provenexpense
        ));
    }

    public function showEmployeeAction($employee_id, $date)
    {
        $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
        $usr = $this->container->get('security.context')->getToken()->getUser();
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL')){
            $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
            if ($employee->getId() != $usr->getEmployee()->getId()) {
                throw new AccessDeniedException();
            }
        }

        $employees = array_merge(
            $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllAvailable(),
            $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllSoftDeleted()
        );
        $countries = $this->getDoctrine()->getRepository('ExpenseBundle:Countryspecificexpenses')->findAll();
        $trucks = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findAllByVehicletypetype([3,4]);
        if ($date){
            $base_date = $date;
        } else {
            $base_date = new \DateTime();
            $base_date->modify('- 1 month');
        }

        $pe = $this->getDoctrine()->getRepository('ExpenseBundle:Provenexpense');
        $current = new Carbon($date);
        $monthstart = new Carbon($current->startOfMonth());
        $monthend = new Carbon($current->endOfMonth());
        $provenexpense = $pe->findOneBy(array(
          'startdate' => $monthstart,
          'enddate' => $monthend,
          'employee' => $employee
        ));

        if($provenexpense){
          $provenexpense = true;
        }

        return $this->render('ExpenseBundle:Expense:showEmployee.html.twig', array(
            'employee' => $employee,
            'employees' => $employees,
            'countries' => $countries,
            'base_date' => $base_date,
            'trucks' => $trucks,
            'provenexpense' => $provenexpense
        ));
    }

    public function editedexpensesmailAction()
    {
        $base_date = new \DateTime();
        $base_date->modify('- 1 month');
        $date = $base_date->format('Y-m').'-01';

        $drivers = [];
        $editedexpenses = $this->getDoctrine()->getRepository('ExpenseBundle:Workday')->findEditedexpenses($date, (new \DateTime($date))->modify('midnight +1 month')->format('Y-m-d'));
        if (count($editedexpenses)>0) {
            foreach ($editedexpenses as $editedexpense) {
                if (!array_key_exists($editedexpense->getEmployee()->getId(), $drivers)) {
                    $drivers[$editedexpense->getEmployee()->getId()] = $editedexpense->getEmployee();
                }
            }
            usort($drivers, function($a, $b) {
                if ($a->__toString() == $b->__toString()) {
                    return 0;
                }
                return ($a->__toString() < $b->__toString()) ? -1 : 1;
            });

            $to = array(
                'sheimken@giesker-laakmann.de',
                'fhoeing@giesker-laakmann.de',
                'mbusch@giesker-laakmann.de',
            );

            $message = (new \Swift_Message(count($drivers).' Mitarbeiter haben den Spesenvorschlag bearbeitet'))
                ->setFrom('gl@361gradmedien.de')
                ->setBcc('gl@361gradmedien.de')
                ->setTo($to)
                ->setBody(
                    $this->renderView(
                        'ExpenseBundle:Expense:email.html.twig',
                        array(
                            'drivers' => $drivers,
                            'date' => $date,
                        )
                    ),
                    'text/html'
                );

            $this->get('mailer')->send($message);
        }
        return $this->render('ExpenseBundle:Expense:email.html.twig', array(
            'drivers' => $drivers,
            'date' => $date,
        ));
    }

    public function createAction() {
        $expense = new Expense();
        $form = $this->createForm(ExpenseType::class, $expense);

        return $this->render('ExpenseBundle:Expense:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function createSubmitAction(Request $request) {
        $expense = new Expense();
        $form = $this->createForm(ExpenseType::class, $expense);

        $form->handleRequest($request);

        if($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($expense);

            $em->flush();

            return $this->redirectToRoute('expenses_index');
        }

        return $this->render('ExpenseBundle:Expense:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function editAction($id) {
        $expense = $this->getDoctrine()->getRepository('ExpenseBundle:Expense')->find($id);
        if(!$expense) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ExpenseType::class, $expense);

        return $this->render('ExpenseBundle:Expense:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function editSubmitAction(Request $request, $id) {
        $expense = $this->getDoctrine()->getRepository('ExpenseBundle:Expense')->find($id);
        if(!$expense) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(ExpenseType::class, $expense);

        $form->handleRequest($request);

        if($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('expenses_index');
        }

        return $this->render('ExpenseBundle:Expense:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function deleteAction($id) {
        $expense = $this->getDoctrine()->getRepository('ExpenseBundle:Expense')->find($id);
        if(!$expense) {
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($expense);

        $em->flush();

        return $this->redirectToRoute('expenses_index');
    }
}
