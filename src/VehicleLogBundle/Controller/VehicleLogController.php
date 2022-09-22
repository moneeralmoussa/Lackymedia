<?php

namespace VehicleLogBundle\Controller;

use VehicleLogBundle\Entity\VehicleLog;
use VehicleLogBundle\Entity\VehicleLogPosition;
use VehicleLogBundle\Entity\VehicleReservation;
use VehicleLogBundle\Entity\VehicleReservationPosition;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class VehicleLogController extends Controller
{
	public function checkForBlockedUser() {
		$currentUserId = $this->getUser()->getEmployee()->getId();
		if (!empty($this->getDoctrine()
		->getRepository('EmployeeBundle:Employee')
		->findOneById($currentUserId)
		->getVehicleLogBlocked())) {
			return true;
		}
		return false;

	}

    public function indexAction()
    {
		if($this->checkForBlockedUser()){
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

		$vehicles = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findAllByVehicletypetype([6]);
		$currentUserId = $this->getUser()->getId();

		$tmpVehicles = array();
		foreach ($vehicles as $car) {
			if (($car->getVehicleVehicleLogs()->last())
			&& (!$car->getVehicleVehicleLogs()->last()->getVehicleLogEndPosition())) {
				if ($car->getVehicleVehicleLogs()->last()->getEmployee()->getUser()->getId() === $currentUserId) {
					$tmpVehicles[] = $car;
				}
			}
		}

		// If user currently drives a vehicle, only show that to him
		if(!empty($tmpVehicles)) $vehicles = $tmpVehicles;

        return $this->render('VehicleLogBundle:VehicleLog:index.html.twig', array(
            'vehicles' => $vehicles,
            'base_date' => time(),
			'download_date' => (new \DateTime())->modify('-1 month'),
			'current_user_id' => $currentUserId,
        ));
	}

	public function blockEmployeeAction() {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

		$employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllAvailable();
		$blockedEmployees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllAvailableVehicleLogBlocked();

		return $this->render('VehicleLogBundle:VehicleLog:blockEmployee.html.twig', array(
			'employees' => $employees,
			'blockedEmployees' => $blockedEmployees,
		));
	}

	public function setBlockEmployeeAction($employee_id) {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

		$em = $this->getDoctrine()->getManager();
		$employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
		$employee->setVehicleLogBlocked((new \DateTime()));

		$em->persist($employee);
		$em->flush();

		return $this->redirectToRoute('vehicle_log_block_employee');
	}

	public function unblockEmployeeAction($employee_id) {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

		$em = $this->getDoctrine()->getManager();
		$employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
		$employee->setVehicleLogBlocked(null);

		$em->persist($employee);
		$em->flush();

		return $this->redirectToRoute('vehicle_log_block_employee');
	}

	public function messageForBlockedAction() {
		return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
	}

    public function showVehicleAction($vehicle_id)
    {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

        $vehicle = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->find($vehicle_id);

        return $this->render('VehicleLogBundle:VehicleLog:showVehicle.html.twig', array(
            'vehicle' => $vehicle,
        ));
    }

    public function notloggedoutmailAction()
    {
        $non_finished_logs = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->findByNotFinalizedSince((new \DateTime())->modify('-8 hours')->format('Y-m-d H:i:s'));
        if (count($non_finished_logs)>0) {
            $to = array(
                'klaakmann@giesker-laakmann.de',
            );

            $message = (new \Swift_Message(count($non_finished_logs).' nicht abgeschlossene PKW-Fahrten'))
                ->setFrom('gl@361gradmedien.de')
                ->setBcc('gl@361gradmedien.de')
                ->setTo($to)
                ->setBody(
                    $this->renderView(
                        'VehicleLogBundle:VehicleLog:email.html.twig',
                        array(
                            'non_finished_logs' => $non_finished_logs,
                        )
                    ),
                    'text/html'
                );

            $this->get('mailer')->send($message);
        }
        return $this->render('VehicleLogBundle:VehicleLog:email.html.twig', array(
            'non_finished_logs' => $non_finished_logs,
        ));
    }

    public function createAction($vehicle_id, Request $request)
    {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

        $vehicle = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->find($vehicle_id);

        $latest_log = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->findLatestByVehicle($vehicle);
        if( !empty($latest_log) and $latest_log->getvehicleLogEndTime() == NULL )
        {
         //   dump($latest_log);die();
            return $this->render('VehicleLogBundle:VehicleLog:messageForFormBlocked.html.twig');
        }
        $begin_position = null;
        if (!empty($latest_log)) {
            $begin_position = $latest_log->getVehicleLogEndPosition();
        }

        $vehicle_log = new VehicleLog();
        $form = $this->createForm('VehicleLogBundle\Form\VehicleLogCreateType', $vehicle_log);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $validator = $this->get('validator');

            $updateLatestLog = false;
            $errorMailContent = false;
            if (\is_null($begin_position) && !empty($latest_log)) {
                $updateLatestLog = true;
                $errorMailContent = "Achtung, bei <a href=\"https://management.giesker-laakmann.de/vehicleLogs/".$vehicle->getId()."\">".$vehicle->getName()
                        ."</a> wurde die vorhergehende Fahrt vom ".$latest_log->getVehicleLogBeginTime()->format('d.m.Y H:i')
                        ." nicht vom Fahrer ".$latest_log->getEmployee()." abgeschlossen.<br>\n"
                        ."Diese Fahrt wurde jetzt automatisch abgeschlossen.";
            } elseif (!empty($begin_position) && ((int)$begin_position->getMileage() !== (int)$form->get('beginPositionMileage')->getData())) {
                $errorMailContent = "Achtung, bei <a href=\"https://management.giesker-laakmann.de/vehicleLogs/".$vehicle->getId()."\">".$vehicle->getName()
                        ."</a> stimmt der Kilometerstand nicht mit der vorherigen Fahrt überein:<br>\nkm alt: "
                        .$begin_position->getMileage()."<br>\nkm neu: ".$form->get('beginPositionMileage')->getData()
                        ."<br>\nDiff.: ".((int)$form->get('beginPositionMileage')->getData()-(int)$begin_position->getMileage());
            } elseif (!$vehicle_log->getVehicleClean()) {
                $errorMailContent = "Achtung, bei <a href=\"https://management.giesker-laakmann.de/vehicleLogs/".$vehicle->getId()."\">".$vehicle->getName()
                        ."</a> wurde das Fahrzeug vom aktuellen Fahrer (".$this->getUser()->getEmployee().") als nicht sauber markiert.";
            } elseif ($vehicle_log->getComment()) {
                $errorMailContent = "Achtung, bei <a href=\"https://management.giesker-laakmann.de/vehicleLogs/".$vehicle->getId()."\">".$vehicle->getName()
                        ."</a> wurde vom aktuellen Fahrer (".$this->getUser()->getEmployee().") ein Kommentar eingestellt:<br>\n\""
                        .$vehicle_log->getComment()."\"";
            }

            //gibt es km schon für Fahrzeug?
            //sonst
            if (\is_null($begin_position)
                    || ((int)$begin_position->getMileage() !== (int)$form->get('beginPositionMileage')->getData())
                    || ((int)$begin_position->getName() !== (int)$form->get('beginPosition')->getData())) {
                $begin_position = new VehicleLogPosition();
                $begin_position->setVehicle($vehicle);
                $begin_position->setName($form->get('beginPosition')->getData());
                $begin_position->setLat($form->get('beginPositionLat')->getData());
                $begin_position->setLon($form->get('beginPositionLon')->getData());
                $begin_position->setMileage($form->get('beginPositionMileage')->getData());
            }

            if (empty($form->get('beginPositionLat')->getData()) || empty($form->get('beginPositionLon')->getData())) {
                $gAddress = str_replace(' ', '+', $form->get('beginPosition')->getData());
                $gUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=".$gAddress."&key=AIzaSyDRT-dvbz9V3wxObDtziSUesCxXGMN6E2M";
                $gContents = json_decode(file_get_contents($gUrl));
                if ($gContents->status === 'OK') {
                    $begin_position->setLat($gContents->results[0]->geometry->location->lat);
                    $begin_position->setLon($gContents->results[0]->geometry->location->lng);
                } else {
                    $begin_position->setLat(51.9286432);
                    $begin_position->setLon( 7.3548623);
                }
            }

            // ende
            $errors_begin = $validator->validate($begin_position);

            if (count($errors_begin) > 0) {
                $zuordnung = array(
                    "name"=>'beginPosition',
                    "lat"=>'beginPositionLat',
                    "lon"=>'beginPositionLon',
                    "mileage"=>'beginPositionMileage',
                );
                foreach ($errors_begin as $error) {
                    $form->get($zuordnung[$error->getPropertyPath()])->addError(
                        new \Symfony\Component\Form\FormError($error->getMessage())
                    );
                }
            } else {
                $em->persist($begin_position);

                $vehicle_log->setVehicle($vehicle);
                $vehicle_log->setEmployee($this->getUser()->getEmployee());
                $vehicle_log->setVehicleLogBeginPosition($begin_position);

                $em->persist($vehicle_log);

                if ($updateLatestLog) {
                    $latest_log->setVehicleLogEndPosition($begin_position);
                    $em->persist($latest_log);
                }
                $em->flush();

                if ($errorMailContent) {
                    $to = array(
                        'klaakmann@giesker-laakmann.de',
                    );

                    $message = (new \Swift_Message('Abweichung im Fahrtenbuch für '.$vehicle->getName()))
                        ->setFrom('gl@361gradmedien.de')
                        ->setBcc('gl@361gradmedien.de')
                        ->setTo($to)
                        ->setBody($errorMailContent, 'text/html');

                    $this->get('mailer')->send($message);
                }

                return $this->redirectToRoute('vehicle_log_showVehicle', array('vehicle_id' => $vehicle->getId()));
            }
        } elseif($form->isSubmitted()) {
        } else {
            if(!is_null($begin_position)) {
                $form->get('beginPositionMileage')->setData($begin_position->getMileage());
            }

            //$form->get('employee')->setData($this->getUser()->getEmployee());
        }

        return $this->render('VehicleLogBundle:VehicleLog:create.html.twig', array(
            'vehicle' => $vehicle,
            'vehicle_log' => $vehicle_log,
            'begin_position' => $begin_position,
            'form' => $form->createView(),
        ));
    }

    public function finalizeAction($id, Request $request)
    {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

        $vehicle_log = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->find($id);
        $vehicle = $vehicle_log->getVehicle();

        $form = $this->createForm('VehicleLogBundle\Form\VehicleLogFinalizeType', $vehicle_log);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $validator = $this->get('validator');

            $end_position = new VehicleLogPosition();
            $end_position->setVehicle($vehicle);
            $end_position->setName($form->get('endPosition')->getData());
            $end_position->setLat($form->get('endPositionLat')->getData());
            $end_position->setLon($form->get('endPositionLon')->getData());
            $end_position->setMileage($form->get('endPositionMileage')->getData());

            $endMileageValid = $form->get('endPositionMileage')->getData() > $vehicle_log->getVehicleLogBeginPosition()->getMileage();
            $errors = $validator->validate($end_position);

            if (count($errors) > 0 || !$endMileageValid) {
                if (count($errors) > 0) {
                    $zuordnung = array(
                        "name"=>'endPosition',
                        "lat"=>'endPositionLat',
                        "lon"=>'endPositionLon',
                        "mileage"=>'endPositionMileage',
                    );
                    foreach ($errors as $error) {
                        $form->get($zuordnung[$error->getPropertyPath()])->addError(
                            new \Symfony\Component\Form\FormError($error->getMessage())
                        );
                    }
                }
                if (!$endMileageValid) {
                    $form->get('endPositionMileage')->addError(
                            new \Symfony\Component\Form\FormError('end position mileage must be greater than begin position mileage')
                        );
                }
            } else {
                $em->persist($end_position);

                $vehicle_log->setVehicleLogEndPosition($end_position);
                $em->persist($vehicle_log);

                $em->flush();

                return $this->redirectToRoute('vehicle_log_showVehicle', array('vehicle_id' => $vehicle->getId()));
            }
        }

        return $this->render('VehicleLogBundle:VehicleLog:finalize.html.twig', array(
            'vehicle' => $vehicle,
            'vehicle_log' => $vehicle_log,
            'form' => $form->createView(),
        ));
    }

    public function showAction($id, Request $request)
    {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

        $vehicle_log = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->find($id);

        return $this->render('VehicleLogBundle:VehicleLog:show.html.twig', array(
            'vehicle_log' => $vehicle_log,
        ));
    }

    public function deleteVehiclelogAction($id, Request $request, $ajax=false)
    {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

        $vehicle_log = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->find($id);
        $em = $this->getDoctrine()->getManager();

        //$vehicle_log->delete(); //setDeletedAt(new \DateTime());

        $em->remove($vehicle_log);
        $em->flush();

        return $this->redirectToRoute('vehicle_log_showVehicle', array('vehicle_id' => $vehicle_log->getVehicle()->getId()));
    }

    public function editAction($id, Request $request)
    {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

        $vehicle_log = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->find($id);
        $vehicle = $vehicle_log->getVehicle();

        $begin_position = $vehicle_log->getVehicleLogBeginPosition();
        $end_position = $vehicle_log->getVehicleLogEndPosition();

        $form = $this->createForm('VehicleLogBundle\Form\VehicleLogType', $vehicle_log);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $validator = $this->get('validator');

            $begin_position->setVehicle($vehicle_log->getVehicle());
            $begin_position->setName($form->get('beginPosition')->getData());
            $begin_position->setLat($form->get('beginPositionLat')->getData());
            $begin_position->setLon($form->get('beginPositionLon')->getData());
            $begin_position->setMileage($form->get('beginPositionMileage')->getData());

            $end_position->setVehicle($vehicle_log->getVehicle());
            $end_position->setName($form->get('endPosition')->getData());
            $end_position->setLat($form->get('endPositionLat')->getData());
            $end_position->setLon($form->get('endPositionLon')->getData());
            $end_position->setMileage($form->get('endPositionMileage')->getData());

            $endMileageValid = $end_position->getMileage() > $begin_position->getMileage();
            $errors_begin = $validator->validate($begin_position);
            $errors_end = $validator->validate($end_position);

            if(count($errors_begin) > 0 || count($errors_end) > 0 || !$endMileageValid) {
                if (count($errors_end) > 0) {
                    $zuordnung = array(
                        "name"=>'endPosition',
                        "lat"=>'endPositionLat',
                        "lon"=>'endPositionLon',
                        "mileage"=>'endPositionMileage',
                    );
                    foreach ($errors_end as $error) {
                        $form->get($zuordnung[$error->getPropertyPath()])->addError(
                            new \Symfony\Component\Form\FormError($error->getMessage())
                        );
                    }
                }
                if (count($errors_begin) > 0) {
                    $zuordnung = array(
                        "name"=>'beginPosition',
                        "lat"=>'beginPositionLat',
                        "lon"=>'beginPositionLon',
                        "mileage"=>'beginPositionMileage',
                    );
                    foreach ($errors_begin as $error) {
                        $form->get($zuordnung[$error->getPropertyPath()])->addError(
                            new \Symfony\Component\Form\FormError($error->getMessage())
                        );
                    }
                }
                if (!$endMileageValid) {
                    $form->get('endPositionMileage')->addError(
                            new \Symfony\Component\Form\FormError('end position mileage must be greater than begin position mileage')
                        );
                }
            } else {
                $em->persist($begin_position);
                $em->persist($end_position);

                $vehicle_log->setVehicleLogBeginPosition($begin_position);
                $vehicle_log->setVehicleLogEndPosition($end_position);
                $em->persist($vehicle_log);

                $em->flush();

                return $this->redirectToRoute('vehicle_log_showVehicle', array('vehicle_id' => $vehicle->getId()));
            }
        } else {
            if (!\is_null($begin_position)) {
                $form->get('beginPosition')->setData($begin_position->getName());
                $form->get('beginPositionLat')->setData($begin_position->getLat());
                $form->get('beginPositionLon')->setData($begin_position->getLon());
                $form->get('beginPositionMileage')->setData($begin_position->getMileage());
            }

            if (!\is_null($end_position)) {
                $form->get('endPosition')->setData($end_position->getName());
                $form->get('endPositionLat')->setData($end_position->getLat());
                $form->get('endPositionLon')->setData($end_position->getLon());
                $form->get('endPositionMileage')->setData($end_position->getMileage());
            }
        }

        return $this->render('VehicleLogBundle:VehicleLog:edit.html.twig', array(
            'vehicle' => $vehicle,
            'vehicle_log' => $vehicle_log,
            'form' => $form->createView(),
        ));
    }

    public function createReservationAction(Request $request, $ajax=false, $vehicle_id=false)
    {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

        $vehicle_reservation = new VehicleReservation();

        $form = $this->createForm('VehicleLogBundle\Form\VehicleReservationType', $vehicle_reservation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $validator = $this->get('validator');

            $begin_position = new VehicleReservationPosition();
            $begin_position->setVehicle($vehicle_reservation->getVehicle());
            $begin_position->setName($form->get('beginPosition')->getData());
            $begin_position->setLat($form->get('beginPositionLat')->getData());
            $begin_position->setLon($form->get('beginPositionLon')->getData());

            $end_position = new VehicleReservationPosition();
            $end_position->setVehicle($vehicle_reservation->getVehicle());
            $end_position->setName($form->get('endPosition')->getData());
            $end_position->setLat($form->get('endPositionLat')->getData());
            $end_position->setLon($form->get('endPositionLon')->getData());

            // ende
            $errors_begin = $validator->validate($begin_position);
            $errors_end = $validator->validate($end_position);

            if(count($errors_begin) > 0 || count($errors_end) > 0) {
                if (count($errors_end) > 0) {
                    $zuordnung = array(
                        "name"=>'endPosition',
                        "lat"=>'endPositionLat',
                        "lon"=>'endPositionLon',
                        "mileage"=>'endPositionMileage',
                    );
                    foreach ($errors_end as $error) {
                        $form->get($zuordnung[$error->getPropertyPath()])->addError(
                            new \Symfony\Component\Form\FormError($error->getMessage())
                        );
                    }
                }
                if (count($errors_begin) > 0) {
                    $zuordnung = array(
                        "name"=>'beginPosition',
                        "lat"=>'beginPositionLat',
                        "lon"=>'beginPositionLon',
                        "mileage"=>'beginPositionMileage',
                    );
                    foreach ($errors_begin as $error) {
                        $form->get($zuordnung[$error->getPropertyPath()])->addError(
                            new \Symfony\Component\Form\FormError($error->getMessage())
                        );
                    }
                }
            } else {
                $em->persist($begin_position);
                $em->persist($end_position);

                $vehicle_reservation->setVehicleReservationBeginPosition($begin_position);
                $vehicle_reservation->setVehicleReservationEndPosition($end_position);

                $em->persist($vehicle_reservation);
                $em->flush();

                return $this->redirectToRoute('vehicle_log_showVehicle', array('vehicle_id' => $vehicle_reservation->getVehicle()->getId()));
            }
        } elseif($form->isSubmitted()) {
        } else {
            if ($vehicle_id !== false) {
                $form->get('vehicle')->setData($this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->find($vehicle_id));
            }
        }

        $template = 'VehicleLogBundle:VehicleLog:reservationForm.html.twig';
        if ($ajax) {
            $template = 'VehicleLogBundle:VehicleLog:reservationFormAjax.html.twig';
        }

        return $this->render($template, array(
            'form' => $form->createView(),
            'action' => 'create',
        ));
    }

    public function editReservationAction($id, Request $request, $ajax=false)
    {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

        $vehicle_reservation = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleReservation')->find($id);

        $begin_position = $vehicle_reservation->getVehicleReservationBeginPosition();
        $end_position = $vehicle_reservation->getVehicleReservationEndPosition();

        $form = $this->createForm('VehicleLogBundle\Form\VehicleReservationType', $vehicle_reservation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $validator = $this->get('validator');

            $begin_position->setVehicle($vehicle_reservation->getVehicle());
            $begin_position->setName($form->get('beginPosition')->getData());
            $begin_position->setLat($form->get('beginPositionLat')->getData());
            $begin_position->setLon($form->get('beginPositionLon')->getData());

            $end_position->setVehicle($vehicle_reservation->getVehicle());
            $end_position->setName($form->get('endPosition')->getData());
            $end_position->setLat($form->get('endPositionLat')->getData());
            $end_position->setLon($form->get('endPositionLon')->getData());

            // ende
            $errors_begin = $validator->validate($begin_position);
            $errors_end = $validator->validate($end_position);

            if(count($errors_begin) > 0 || count($errors_end) > 0) {
                if (count($errors_end) > 0) {
                    $zuordnung = array(
                        "name"=>'endPosition',
                        "lat"=>'endPositionLat',
                        "lon"=>'endPositionLon',
                        "mileage"=>'endPositionMileage',
                    );
                    foreach ($errors_end as $error) {
                        $form->get($zuordnung[$error->getPropertyPath()])->addError(
                            new \Symfony\Component\Form\FormError($error->getMessage())
                        );
                    }
                }
                if (count($errors_begin) > 0) {
                    $zuordnung = array(
                        "name"=>'beginPosition',
                        "lat"=>'beginPositionLat',
                        "lon"=>'beginPositionLon',
                        "mileage"=>'beginPositionMileage',
                    );
                    foreach ($errors_begin as $error) {
                        $form->get($zuordnung[$error->getPropertyPath()])->addError(
                            new \Symfony\Component\Form\FormError($error->getMessage())
                        );
                    }
                }
            } else {
                $em->persist($begin_position);
                $em->persist($end_position);

                $vehicle_reservation->setVehicleReservationBeginPosition($begin_position);
                $vehicle_reservation->setVehicleReservationEndPosition($end_position);

                $em->persist($vehicle_reservation);
                $em->flush();

                return $this->redirectToRoute('vehicle_log_showVehicle', array('vehicle_id' => $vehicle_reservation->getVehicle()->getId()));
            }
        } elseif($form->isSubmitted()) {
        } else {
            $form->get('beginPosition')->setData($begin_position->getName());
            $form->get('beginPositionLat')->setData($begin_position->getLat());
            $form->get('beginPositionLon')->setData($begin_position->getLon());

            $form->get('endPosition')->setData($end_position->getName());
            $form->get('endPositionLat')->setData($end_position->getLat());
            $form->get('endPositionLon')->setData($end_position->getLon());
        }

        $template = 'VehicleLogBundle:VehicleLog:reservationForm.html.twig';
        if ($ajax) {
            $template = 'VehicleLogBundle:VehicleLog:reservationFormAjax.html.twig';
        }

        return $this->render($template, array(
            'form' => $form->createView(),
            'action' => 'edit',
            'itemid' => $id,
        ));
    }

    public function deleteReservationAction($id, Request $request, $ajax=false)
    {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

        $vehicle_reservation = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleReservation')->find($id);

        $form = $this->createForm('VehicleLogBundle\Form\VehicleReservationType', $vehicle_reservation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $vehicle_reservation->setDeletedAt(new \DateTime());

            $em->persist($vehicle_reservation);
            $em->flush();
        }
        return $this->redirectToRoute('vehicle_log_showVehicle', array('vehicle_id' => $vehicle_reservation->getVehicle()->getId()));
    }

    public function loadReservationsByMonthAction(Request $request)
    {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

        $vehicle_logs = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->findByMonthNoEmployee($request->get('start'), (new \DateTime($request->get('end')))->modify('midnight +1 day')->format('Y-m-d'));
        $vehicle_reservations = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleReservation')->findByMonthNoEmployee($request->get('start'), (new \DateTime($request->get('end')))->modify('midnight +1 day')->format('Y-m-d'));

        $tasks = array();

        foreach($vehicle_logs as $vehicle_log) {
            $id = "vehicle_log_".$vehicle_log->getId();
            $temp = [
                    'id'               => $id,
                    'resourceId'       => $vehicle_log->getVehicle()->getId(),
                    'start'            => $vehicle_log->getVehicleLogBeginTime()->format('Y-m-d H:i:s'),
                    'end'              => (new \DateTime())->format('Y-m-d H:i:s'),
                    'name'            => $vehicle_log->getVehicle()->getName(),
                    'title'            => $vehicle_log->getVehicle()->getName()." (".$vehicle_log->getVehicleLogBeginPosition()->getName().")",
                    'backgroundColor'  => '#CC5750',
                    'driver'         => (!empty($vehicle_log->getEmployee()))?array('id'=>$vehicle_log->getEmployee()->getId(),'name'=>$vehicle_log->getEmployee()->getName()):null,
                    'beginPosition'    => [
                        'lat'=>$vehicle_log->getVehicleLogBeginPosition()->getLat(),
                        'lng'=>$vehicle_log->getVehicleLogBeginPosition()->getLon(),
                        'name'=>$vehicle_log->getVehicleLogBeginPosition()->getName(),
                    ],
                    'endPosition'      => [],
                    'type'             => 'in_use',
                ];
            if (!empty($vehicle_log->getVehicleLogEndTime())) {
                $temp['end'] = $vehicle_log->getVehicleLogEndTime()->format('Y-m-d H:i:s');
                $temp['backgroundColor'] = '#8DB87C';
                $temp['type'] = 'available';
                $temp['endPosition'] = [
                    'lat'=>$vehicle_log->getVehicleLogEndPosition()->getLat(),
                    'lng'=>$vehicle_log->getVehicleLogEndPosition()->getLon(),
                    'name'=>$vehicle_log->getVehicleLogEndPosition()->getName()
                ];
                $temp['title'] = $vehicle_log->getVehicle()->getName()." (".$vehicle_log->getVehicleLogBeginPosition()->getName()." - ".$vehicle_log->getVehicleLogEndPosition()->getName().")";
            }
            $tasks[] = $temp;
        }

        foreach($vehicle_reservations as $vehicle_reservation) {
            $id = "vehicle_reservation_".$vehicle_reservation->getId();
            $tasks[] = [
                'id' => $id,
                'resourceId'       => $vehicle_reservation->getVehicle()->getId(),
                'start'            => $vehicle_reservation->getVehicleReservationBeginTime()->format('Y-m-d H:i:s'),
                'end'              => $vehicle_reservation->getVehicleReservationEndTime()->format('Y-m-d H:i:s'),
                'title'            => $vehicle_reservation->getVehicle()->getName(),
                'backgroundColor'  => '#AAD0CD',
                'driver'         => array('id'=>$vehicle_reservation->getEmployee()->getId(),'name'=>$vehicle_reservation->getEmployee()->getName()),
                'beginPosition'    => [
                    'lat'=>$vehicle_reservation->getVehicleReservationBeginPosition()->getLat(),
                    'lng'=>$vehicle_reservation->getVehicleReservationBeginPosition()->getLon(),
                    'name'=>$vehicle_reservation->getVehicleReservationBeginPosition()->getName(),
                ],
                'endPosition'    => [
                    'lat'=>$vehicle_reservation->getVehicleReservationEndPosition()->getLat(),
                    'lng'=>$vehicle_reservation->getVehicleReservationEndPosition()->getLon(),
                    'name'=>$vehicle_reservation->getVehicleReservationEndPosition()->getName(),
                ],
                'type'             => 'reserved',
            ];
        }

        return new JsonResponse($tasks);
    }

    public function loadMonthCarusageCsvAction($date)
    {
		if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
        }
    

        set_time_limit(0);
        ini_set('memory_limit','-1');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        //$vehicles = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findAllByVehicletypetype([6]);
        $vehicle_logs = $em->getRepository('VehicleLogBundle:VehicleLog')->findByMonthNoEmployee($date, (new \DateTime($date))->modify('midnight +1 month')->format('Y-m-d'));

        $response = $this->render('VehicleLogBundle:VehicleLog:loadMonthCarusage.csv.twig', array(
            'vehicle_logs' => $vehicle_logs,
            'base_date' => $date,
        ));
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="PKW-Nutzung_'.$date.'.csv"');

        return $response;
    }

    public function jsonCarAction()
    {
        $vehicles = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findAllByVehicletypetype([6]);
        $currentUserId = $this->getUser()->getId();
        $tmpVehicles = array();
        foreach ($vehicles as $car) {
          if (($car->getVehicleVehicleLogs()->last())
          && (!$car->getVehicleVehicleLogs()->last()->getVehicleLogEndPosition())) {
            if ($car->getVehicleVehicleLogs()->last()->getEmployee()->getUser()->getId() === $currentUserId) {
              $tmpVehicles[] = $car;
            }
          }
        }
        if(!empty($tmpVehicles)) 
        {
        $vehicles = $tmpVehicles;
          $carunterway = 'true';
          return new JsonResponse(array('carunterway' => 'true'));
       }
       else
       {
        $carunterway = 'false';
        return new JsonResponse(array('carunterway' => 'false'));
       }
       // Use renderText to return a json encoded array !
    }

    public function VehicleReportKmAction()
    {
	  	if($this->checkForBlockedUser()) {
			return $this->render('VehicleLogBundle:VehicleLog:messageForBlocked.html.twig');
		}

        $vehicles = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findAllByVehicletypetype([6]);
         $em = $this->getDoctrine()->getManager(); 
            $connection = $em->getConnection();
            $statement = $connection->prepare("SELECT employee.name,department.name AS department,vehicle_log_reason.name As reason ,vehicle.name AS vehicle,vehicle_logs.* , vehicle_log_position.name AS fromcity, vehicle_log_position.mileage AS beginmileage , vehicle_log_position_new.name AS tocity , vehicle_log_position_new.mileage AS endmileage , (vehicle_log_position_new.mileage-vehicle_log_position.mileage) AS Distanc FROM vehicle_log AS vehicle_logs
            LEFT JOIN vehicle_log_position
            ON vehicle_log_position.id=vehicle_logs.begin_position_id
            LEFT JOIN (SELECT * FROM vehicle_log_position )AS vehicle_log_position_new
            ON vehicle_log_position_new.id=vehicle_logs.end_position_id   
            LEFT JOIN employee ON vehicle_logs.employee_id = employee.id
            LEFT JOIN vehicle_log_reason ON vehicle_logs.reason_id = vehicle_log_reason.id
            LEFT JOIN department ON department.id=employee.department_id
            LEFT JOIN vehicle ON vehicle.id=vehicle_logs.vehicle_id
            WHERE ((vehicle_log_position_new.mileage-vehicle_log_position.mileage) > 100 And vehicle_logs.created_at > '2019-09-00 17:37:00') OR (vehicle_log_position_new.mileage < vehicle_log_position.mileage AND vehicle_logs.created_at > '2019-09-00 17:37:00' )
            order by vehicle_logs.id DESC
        ");

            $statement->execute();
            $Distanc = $statement->fetchAll();
           
            $statement = $connection->prepare("SELECT employee.name,department.name AS department,vehicle_log_reason.name As reason , vehicle.name AS vehicle, vehicle_logs.* , vehicle_log_position.name AS fromcity, vehicle_log_position.mileage AS beginmileage , vehicle_log_position_new.name AS tocity , vehicle_log_position_new.mileage AS endmileage , TIMEDIFF( vehicle_log_end_time , vehicle_log_begin_time) AS timevehicle_log FROM vehicle_log AS vehicle_logs
            LEFT JOIN vehicle_log_position
            ON vehicle_log_position.id=vehicle_logs.begin_position_id
            LEFT JOIN (SELECT * FROM vehicle_log_position )AS vehicle_log_position_new
            ON vehicle_log_position_new.id=vehicle_logs.end_position_id
            LEFT JOIN employee ON vehicle_logs.employee_id = employee.id
            LEFT JOIN vehicle_log_reason ON vehicle_logs.reason_id = vehicle_log_reason.id
            LEFT JOIN department ON department.id=employee.department_id
            LEFT JOIN vehicle ON vehicle.id=vehicle_logs.vehicle_id
            WHERE TIMEDIFF( vehicle_log_end_time , vehicle_log_begin_time) > '26:00:00' And vehicle_logs.created_at > '2019-09-01 17:37:00'
            order by vehicle_logs.id DESC
        ");

            $statement->execute();
            $Timelog = $statement->fetchAll();



            $statement = $connection->prepare("SELECT ALT_vehicle_logs.begin_position_id,ALT_vehicle_logs.end_position_id,(
                SELECT vehicle_log_position_new.mileage AS lastendmileage  FROM vehicle_log AS vehicle_logss
                LEFT JOIN (SELECT * FROM vehicle_log_position )AS vehicle_log_position_new
                ON vehicle_log_position_new.id=vehicle_logss.end_position_id
                    where vehicle_logss.id< vehicle_logs.id and vehicle_logss.vehicle_id = vehicle_logs.vehicle_id  order by vehicle_logss.id  DESC limit 1
                )as LastKM, ALT_vehicle_logs.name As name1, employee.name,department.name AS department,vehicle_log_reason.name As reason , vehicle.name AS vehicle, vehicle_logs.* , vehicle_log_position.name AS fromcity, vehicle_log_position.mileage AS beginmileage , vehicle_log_position_new.name AS tocity , vehicle_log_position_new.mileage AS endmileage FROM vehicle_log AS vehicle_logs
                LEFT JOIN vehicle_log_position
                ON vehicle_log_position.id=vehicle_logs.begin_position_id
                LEFT JOIN (SELECT * FROM vehicle_log_position )AS vehicle_log_position_new
                ON vehicle_log_position_new.id=vehicle_logs.end_position_id
                LEFT JOIN employee ON vehicle_logs.employee_id = employee.id
                LEFT JOIN vehicle_log_reason ON vehicle_logs.reason_id = vehicle_log_reason.id
                LEFT JOIN department ON department.id=employee.department_id
                LEFT JOIN vehicle ON vehicle.id=vehicle_logs.vehicle_id
                LEFT JOIN  (
                    SELECT employee.name,department.name AS department,vehicle_log_reason.name As reason , vehicle.name AS vehicle, vehicle_logs.* , vehicle_log_position.name AS fromcity, vehicle_log_position.mileage AS beginmileage , vehicle_log_position_new.name AS tocity , vehicle_log_position_new.mileage AS endmileage  FROM vehicle_log AS vehicle_logs
                LEFT JOIN vehicle_log_position
                ON vehicle_log_position.id=vehicle_logs.begin_position_id
                LEFT JOIN (SELECT * FROM vehicle_log_position )AS vehicle_log_position_new
                ON vehicle_log_position_new.id=vehicle_logs.end_position_id
                LEFT JOIN employee ON vehicle_logs.employee_id = employee.id
                LEFT JOIN vehicle_log_reason ON vehicle_logs.reason_id = vehicle_log_reason.id
                LEFT JOIN department ON department.id=employee.department_id
                LEFT JOIN vehicle ON vehicle.id=vehicle_logs.vehicle_id
                ) AS ALT_vehicle_logs 
                ON ALT_vehicle_logs.endmileage = vehicle_log_position.mileage
                WHERE  vehicle_logs.created_at > '2019-10-01 17:37:00' and ALT_vehicle_logs.end_position_id is null");

            $statement->execute();
            $errorsmileage = $statement->fetchAll();

            foreach ($errorsmileage as $vehicle ){
               

            }
          
            
         return $this->render('VehicleLogBundle:VehicleLog:showVehicleReports.html.twig', array(
            'vehiclesDistance' => $Distanc,
            'vehiclesTimelog' => $Timelog,
            'errorsmileage' => $errorsmileage,
        ));

    }


}
