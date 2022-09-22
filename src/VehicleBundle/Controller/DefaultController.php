<?php

namespace VehicleBundle\Controller;

use VehicleBundle\Entity\Vehicle;
use VehicleBundle\Entity\Vehicletype;
use VehicleBundle\Entity\Vehicletypetype;
use VehicleBundle\Entity\Tour;
use VehicleBundle\Entity\TourDetail;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public static $expensetest = null;

    public function indexAction()
    {
        $vehicles = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findAll();

        $em = $this->getDoctrine()->getManager();
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');
        $deletedVehicles = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->getAllSoftDeleted();
        $filters->enable('softdeleteable');

        return $this->render('VehicleBundle:Default:index1.html.twig', array(
            'vehicles' => $vehicles,
            'deletedVehicles' => $deletedVehicles,
            'base_date' => time(),
        ));
    }

    public function indexToursAction($date=null)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_DISPOSITION')) {
            throw new AccessDeniedException();
        }

        if (!empty($date)) {
            $base_date = new \DateTime($date);
        } else {
            $base_date = new \DateTime();
            $base_date->modify('- 1 month');
            $date = $base_date->format('Y-m-d');
        }

        $vehicle = null;
        $tours = $this->getDoctrine()->getRepository('VehicleBundle:Tour')->findByDateVehicle($date, (new \DateTime($date))->modify('+1 month +1 day')->format('Y-m-d'), $vehicle);

        return $this->render('VehicleBundle:Default:indexTours.html.twig', array(
            'tours' => $tours,
            'base_date' => $base_date,
        ));
    }

    public function consumptionStatisticsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $consumptions = $em->getRepository('VehicleBundle:Consumption')->findAllLatest();
        $em->getFilters()->enable('softdeleteable');
        $usr = $this->container->get('security.context')->getToken()->getUser();
        $reportDates = $em->getRepository('VehicleBundle:Consumption')->findAllReportDates();

        return $this->render('VehicleBundle:Default:consumptionStatistics.html.twig', array(
            'consumptions' => $consumptions,
            'usr' => $usr,
            'reportDates' => $reportDates,
        ));
    }

    public function driverConsumptionStatisticAction($driver)
    {
        $usr = $this->container->get('security.context')->getToken()->getUser();
        if ($driver != $usr->getEmployee()->getId() && !in_array('ROLE_ADMIN', $usr->getRoles())) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $consumptions = $em->getRepository('VehicleBundle:Consumption')->findByDriver($driver);
        $reportDates = $em->getRepository('VehicleBundle:Consumption')->findAllReportDates();
        $avgConsumptions = $em->getRepository('VehicleBundle:Consumption')->getAllAverageConsumptions($driver);

        return $this->render('VehicleBundle:Default:driverConsumptionStatistic.html.twig', array(
            'consumptions' => $consumptions,
            'usr' => $usr,
            'reportDates' => $reportDates,
            'avgConsumptions' => $avgConsumptions
        ));
    }

    public function allDriverConsumptionStatisticsAction($driver)
    {
        $usr = $this->container->get('security.context')->getToken()->getUser();
        if ($driver != $usr->getEmployee()->getId() && !in_array('ROLE_ADMIN', $usr->getRoles())) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $consumptions = $em->getRepository('VehicleBundle:Consumption')->findAllByDriver($driver);
        $reportDates = $em->getRepository('VehicleBundle:Consumption')->findAllReportDates();
        $avgConsumptions = $em->getRepository('VehicleBundle:Consumption')->getAllAverageConsumptions($driver);

        return $this->render('VehicleBundle:Default:allDriverConsumptionStatistics.html.twig', array(
            'consumptions' => $consumptions,
            'usr' => $usr,
            'reportDates' => $reportDates,
            'avgConsumptions' => $avgConsumptions
        ));
    }

    public function ajaxConsumptionStatisticsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $consumptions = $em->getRepository('VehicleBundle:Consumption')->findAllByDate($request->get('startdate'), $request->get('enddate'));

        $usr = $this->container->get('security.context')->getToken()->getUser();

        return new JsonResponse($this->render('VehicleBundle:Default:partials/consumption_loop.html.twig', array(
            'consumptions' => $consumptions,
            'usr' => $usr,
        ))->getContent());
    }

    public function viewAction($id, $date=null)
    {
        $vehicle = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->find($id);
        $locations = $this->getDoctrine()->getRepository('LocationBundle:Location')->findByType(1);

        $activity_dates = array();
        /*if (!\is_null($vehicle->getTrimbleId())) {
            $activity_dates = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findAllActivityDatesBySource($vehicle->getTrimbleId());
        }*/

        return $this->render('VehicleBundle:Default:view.html.twig', array(
            'base_date' => new \DateTime($date), //empty($date)?$activity_dates[0]["time"]:(new \DateTime($date)),
            'vehicle' => $vehicle,
            'activity_dates' => $activity_dates,
            'locations' => $locations,
        ));
    }

    private function activityResourceIdByActivitytype($type)
    {
        $matchinglist = [
            'DR'   => 4,
            'TB'   => 5,
            'TE'   => 6,
            'TSO'  => 7,
            'TSOL' => 8,
            'UN'   => 9,
        ];
        return (array_key_exists($type, $matchinglist))?$matchinglist[$type]:10;
    }

    public function deleteAction(Request $request)
    {
        $vehicle = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->find($request->get('id'));
        $em = $this->getDoctrine()->getManager();
        $em->remove($vehicle);
        $em->flush();
        return new JsonResponse('success');
    }

    public function restoreAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');
        $vehicle = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->find($request->get('id'));
        $vehicle->setDeletedAt(null);
        $em->persist($vehicle);
        $em->flush();
        $filters->enable('softdeleteable');
        return new JsonResponse('success');
    }

    public function loadActivitiesAction(Request $request, $id, $date=null)
    {
        $vehicle = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->find($id);

        $traces = array();
        $workdays = array();
        if (!\is_null($vehicle->getTrimbleId())) {
            if (\is_null($date)) {
                $date = $request->get('start');
                $dateend = $request->get('end');
            } else {
                $dateend = (new \DateTime($date))->modify('midnight +1 day')->format('Y-m-d');
            }
            $traces = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findAllBySourceDate($vehicle->getTrimbleId(), $date, $dateend);
            $workdays = $this->getDoctrine()->getRepository('ExpenseBundle:Workday')->findByDateTruck($vehicle->getId(), $date, $dateend);
        }

        $aty_types = array('10','11','13');
        $driver_ignore_types = array('9', '55', '82', '87', '300', '301', '302');

        $positions = array();
        $drivers = array();
        $activities = array();

        foreach ($traces as $trace) {
            if (!\is_null($trace->getLat()) && !\is_null($trace->getLon())) {
                $positions_cnt = count($positions);

                if ($positions_cnt>0 && $positions[$positions_cnt - 1]['lat'] == $trace->getLat() && $positions[$positions_cnt - 1]['lng'] == $trace->getLon()) {
                    $positions[$positions_cnt - 1]['starttime'] = $trace->getTime();
                } else {
                    $positions[] = array(
                        'lat' => (float)$trace->getLat(),
                        'lng' => (float)$trace->getLon(),
                        'starttime' => $trace->getTime(),
                        'endtime' => $trace->getTime(),
                    );
                }

                $properties = array();
                foreach ($trace->getTracedataproperties() as $property) {
                    $properties[$property->getPropertyKey()] = $property->getPropertyValue();
                }

                if (!in_array($trace->getType(), $driver_ignore_types) && array_key_exists('DID', $properties) && !array_key_exists($properties['DID'], $drivers)) {
                    $drivers[$properties['DID']] = [];
                }
                if (!in_array($trace->getType(), $driver_ignore_types) && array_key_exists('DID', $properties) && array_key_exists($properties['DID'], $drivers) && !array_key_exists($trace->getTime()->format('Y-m-d'), $drivers[$properties['DID']])) {
                    $drivers[$properties['DID']][$trace->getTime()->format('Y-m-d')] = array(
                        'startlat' => $trace->getLat(),
                        'startlon' => $trace->getLon(),
                        'starttime' => $trace->getTime(),
                        'endlat' => $trace->getLat(),
                        'endlon' => $trace->getLon(),
                        'endtime' => $trace->getTime(),
                        'status' => 'FAILED',
                        'driver' => $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->findOneBy(array('trimbleId'=>$properties['DID'])),
                    );
                } elseif (!in_array($trace->getType(), $driver_ignore_types) && array_key_exists('DID', $properties) && array_key_exists($properties['DID'], $drivers) && array_key_exists($trace->getTime()->format('Y-m-d'), $drivers[$properties['DID']])) {
                    $drivers[$properties['DID']][$trace->getTime()->format('Y-m-d')]['starttime'] = $trace->getTime();
                    $drivers[$properties['DID']][$trace->getTime()->format('Y-m-d')]['startlat'] = $trace->getLat();
                    $drivers[$properties['DID']][$trace->getTime()->format('Y-m-d')]['startlon'] = $trace->getLon();
                }

                if (in_array($trace->getType(), $aty_types)) {
                    if (array_key_exists('ATY', $properties) && !array_key_exists($properties['ATY'], $activities)) {
                        $activities[$properties['ATY']] = array('lids'=>array());
                    }
                    if (array_key_exists('LID', $properties) && !array_key_exists($properties['LID'], $activities[$properties['ATY']]['lids'])) {
                        $activities[$properties['ATY']]['lids'][$properties['LID']] = array();
                    }
                    if (in_array($trace->getType(), array('10'))) {
                        $activities[$properties['ATY']]['lids'][$properties['LID']]['starttime'] = $trace->getTime();
                        $activities[$properties['ATY']]['lids'][$properties['LID']]['startlat'] = $trace->getLat();
                        $activities[$properties['ATY']]['lids'][$properties['LID']]['startlon'] = $trace->getLon();
                    } elseif (in_array($trace->getType(), array('11'))) {
                        $activities[$properties['ATY']]['lids'][$properties['LID']]['cancelled'] = $trace->getTime();
                    } elseif (in_array($trace->getType(), array('13'))) {
                        $activities[$properties['ATY']]['lids'][$properties['LID']]['endtime'] = $trace->getTime();
                        $activities[$properties['ATY']]['lids'][$properties['LID']]['endlat'] = $trace->getLat();
                        $activities[$properties['ATY']]['lids'][$properties['LID']]['endlon'] = $trace->getLon();
                    }
                }
            }
        }

        foreach ($activities as $activity_key => $activity_value) {
            foreach ($activities[$activity_key]['lids'] as $lid_key => $lid_value) {
                if (array_key_exists('endtime', $activities[$activity_key]['lids'][$lid_key]) && !array_key_exists('starttime', $activities[$activity_key]['lids'][$lid_key])) {
                    $activities[$activity_key]['lids'][$lid_key]['starttime'] = clone $activities[$activity_key]['lids'][$lid_key]['endtime'];
                    $activities[$activity_key]['lids'][$lid_key]['startlat'] = $activities[$activity_key]['lids'][$lid_key]['endlat'];
                    $activities[$activity_key]['lids'][$lid_key]['startlon'] = $activities[$activity_key]['lids'][$lid_key]['endlon'];
                    $activities[$activity_key]['lids'][$lid_key]['starttime']->modify('midnight');
                } elseif (array_key_exists('starttime', $activities[$activity_key]['lids'][$lid_key]) && !array_key_exists('endtime', $activities[$activity_key]['lids'][$lid_key]) && !array_key_exists('cancelled', $activities[$activity_key]['lids'][$lid_key])) {
                    $activities[$activity_key]['lids'][$lid_key]['endtime'] = clone $activities[$activity_key]['lids'][$lid_key]['starttime'];
                    $activities[$activity_key]['lids'][$lid_key]['endlat'] = $activities[$activity_key]['lids'][$lid_key]['startlat'];
                    $activities[$activity_key]['lids'][$lid_key]['endlon'] = $activities[$activity_key]['lids'][$lid_key]['startlon'];
                    $activities[$activity_key]['lids'][$lid_key]['endtime']->modify('midnight +1 day');
                }
                if (array_key_exists('cancelled', $activities[$activity_key]['lids'][$lid_key])) {
                    unset($activities[$activity_key]['lids'][$lid_key]);
                }
            }
            if (count($activities[$activity_key]['lids']) == 0) {
                unset($activities[$activity_key]);
            }
            if (isset($activities[$activity_key])) {
                $activities[$activity_key]['activity'] = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Trimbleactivity')->findOneBy(array('activityKey'=>$activity_key));
            }
        }

        /*usort($activities, function($a, $b) {
            if (strtolower ($a['activity']->getName()) == strtolower ($b['activity']->getName())) {
                return 0;
            }
            return (strtolower ($a['activity']->getName()) < strtolower ($b['activity']->getName())) ? -1 : 1;
        });*/

        /**
         * Todo: Es soll auch möglich sein den Übernachtungsstatus aus der Fahrzeugansicht heraus anzupassen
         *
        $expenseWorkdayService = $this->container->get('app.expense_workday_service', $this->getDoctrine());
        foreach ($drivers as $driver_key => $driver_value) {
            $startsAtHome = $expenseWorkdayService->startsAtHome($driver_value->driver, array('workdayBeginLat' => $driver_value['startlat'],'workdayBeginLon' => $driver_value['startlon']));
            $finishesAtHome = $expenseWorkdayService->finishesAtHome($driver_value->driver, array('workdayEndLat' => $driver_value['endlat'],'workdayEndLon' => $driver_value['endlon']));
            $startsAtCompany = $expenseWorkdayService->startsAtCompany(array('workdayBeginLat' => $driver_value['startlat'],'workdayBeginLon' => $driver_value['startlon']));
            $finishesAtCompany = $expenseWorkdayService->finishesAtCompany(array('workdayEndLat' => $driver_value['endlat'],'workdayEndLon' => $driver_value['endlon']));
        }
        */

        foreach ($workdays as $workday) {
            $drivers[$workday->getEmployee()->getTrimbleId()][$workday->getDate()->format('Y-m-d')] = array(
                'startlat' => $workday->getWorkdayBeginLat(),
                'startlon' => $workday->getWorkdayBeginLon(),
                'starttime' => $workday->getWorkdayBeginTime(),
                'starthome' => $workday->getWorkdayBeginHome(),
                'endlat' => $workday->getWorkdayEndLat(),
                'endlon' => $workday->getWorkdayEndLon(),
                'endtime' => $workday->getWorkdayEndTime(),
                'endhome' => $workday->getWorkdayEndHome(),
                'status' => 'RUNNING',
                'driver' => $workday->getEmployee(),
            );
        }

        /*return $this->render('VehicleBundle:Default:loadActivities.html.twig', array(
            'positions' => $positions,
            'drivers' => $drivers,
            'activities' => $activities,
        ));*/

        $ret = [];
        $positions_taken=false;

        foreach ($drivers as $trimbleId => $driver_date) {
            foreach ($driver_date as $key => $driver) {
                $id = "employee_".$driver['driver']->getId();
                $temp = [
                    'id'               => $id,
                    'resourceId'       => 1,
                    'start'            => $driver['starttime']->format('Y-m-d H:i:s'),
                    'end'              => $driver['endtime']->format('Y-m-d H:i:s'),
                    'name'             => (string)$driver['driver'],
                    'title'            => (string)$driver['driver']." (".$trimbleId.")",
                    'backgroundColor'  => $driver['status']=='RUNNING'?'#8DB87C':'#CC5750',
                    'positions' => false,
                ];
                if (!$positions_taken) {
                    $positions_taken = true;
                    $temp['positions'] = $positions;
                }
                $ret[] = $temp;
            }
        }

        $expenseWorkdayService = $this->container->get('app.expense_workday_service', $this->getDoctrine());
        foreach ($activities as $activity_key => $activity_value) {
            foreach ($activities[$activity_key]['lids'] as $lid_key => $lid_value) {
                $id = "activity_".$lid_key;
                $temp = [
                    'id'               => $id,
                    'resourceId'       => $this->activityResourceIdByActivitytype($activity_key),
                    'start'            => $activities[$activity_key]['lids'][$lid_key]['starttime']->format('Y-m-d H:i:s'),
                    'end'              => $activities[$activity_key]['lids'][$lid_key]['endtime']->format('Y-m-d H:i:s'),
                    'name'             => $activities[$activity_key]['activity']->getName(),
                    'title'            => $activities[$activity_key]['activity']->getName()." (".$activity_key.")",
                    'backgroundColor'  => '#AAD0CD',
                    'positions' => false,
                    'startPoint' => [
                        'lat' =>  (float)$activities[$activity_key]['lids'][$lid_key]['startlat'],
                        'lng' =>  (float)$activities[$activity_key]['lids'][$lid_key]['startlon'],
                    ],
                    'endPoint' => [
                        'lat' =>  (float)$activities[$activity_key]['lids'][$lid_key]['endlat'],
                        'lng' =>  (float)$activities[$activity_key]['lids'][$lid_key]['endlon'],
                    ],
                ];
                if (!$positions_taken) {
                    $positions_taken = true;
                    $temp['positions'] = $positions;
                }
                if ($activity_key == 'DR') {
                    $temp['startPoint']['town'] = $expenseWorkdayService->reverseGeocoderOSM($temp['startPoint']['lat'], $temp['startPoint']['lng']);
                    $temp['endPoint']['town'] = $expenseWorkdayService->reverseGeocoderOSM($temp['endPoint']['lat'], $temp['endPoint']['lng']);
                    $temp['title'] = $temp['startPoint']['town'] ." - ". $temp['endPoint']['town'];
                }
                $ret[] = $temp;
            }
        }

        return new JsonResponse($ret);
    }

    public function loadMonthKmCsvAction($date)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $process = new Process("python ../python/monthkm.py '".$date."' '".(new \DateTime($date))->modify('midnight +1 month')->format('Y-m-d')."'");
        $process->setTimeout(null);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $activity_days = array();
        // return the output, don't use if you used NullOutput()
        $content = $process->getOutput();
        $temp_expenses = json_decode($content, true);
        if (!empty($temp_expenses)) {
            $activity_days = $temp_expenses;
        }


        $response = $this->render('VehicleBundle:Default:loadMonthKm.csv.twig', array(
            'activity_days' => $activity_days,
            'base_date' => $date,
        ));
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="Fahrzeugkm_'.$date.'.csv"');

        return $response;
    }

    public function importVehiclesXlsxAction(Request $request)
    {
        if (!is_null($request->files->get('excelfile'))) {
            set_time_limit(0);
            ini_set('memory_limit', '-1');

            $em = $this->getDoctrine()->getManager();

            $uploadService = $this->get('app.upload_service');
            $fileName = $uploadService->upload($request->files->get('excelfile'));
            
            $myXlsLoader = $uploadService->xlsLoader($fileName);
            
            
            $inserted = 0;
            $inserted_vehicles = [];
            $deleted = 0;
            $deleted_vehicles = [];

            $lColumnNames = $myXlsLoader[0];
            $objPHPExcel = $myXlsLoader[1];
            
            $lColumnMap = array(
                'ID' => 'Vehicle_komalogId',
                'KST' => 'Vehicle_trimbleId',
                'NAME' => 'Vehicle_name',
                'FAHRZ_HERSTELLER' => 'Vehicle_comment',
                'FAHRZTYP' => 'Vehicletype_komalogId',
                'FAHRZ_TYPBEZEICHNUNG' => 'Vehicletype_name',
                'ART_BESCHREIBUNG' => 'Vehicletype_description',
                'FAHRZARTNAME' => 'Vehicletypetype_name',
                'EIGENTUEMERMATCHCODE' => 'Vehicle_matchcode',
                'FAHRGESTNR' => 'Vehicle_serialnumber',
                'DISPO_OK' => 'Vehicle_deleted'
            );

            $lColumnMap1 = array(
                'Vehicle' => array('Vehicle_komalogId', 'Vehicle_trimbleId', 'Vehicle_name', 'Vehicle_comment','Vehicle_serialnumber', 'Vehicle_deleted'),
                'Vehicletype' => array('Vehicletype_komalogId', 'Vehicletype_name', 'Vehicletype_description'),
                'Vehicletypetype' => array('Vehicletypetype_name'),
            );

            $iColumnID = array_search("id", $lColumnNames);
            $pRow = 2;
            $vehicles = [];

            while (!empty($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($iColumnID, $pRow)->getValue())) {
                $lRowValues = array();
                $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($iColumnID, $pRow)->getValue();
                for ($pColumn=0;$pColumn<count($lColumnNames);$pColumn++) {
                    if (array_key_exists($lColumnNames[$pColumn], $lColumnMap)) {
                        $cellValue = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($pColumn, $pRow)->getValue();
                        $lRowValues[$lColumnMap[$lColumnNames[$pColumn]]] = ($cellValue==""?null:$cellValue);
                    }
                }

                $vehicletypetype = $this->getDoctrine()->getRepository('VehicleBundle:Vehicletypetype')->findOneBy([
                    'name' => $lRowValues[$lColumnMap1['Vehicletypetype'][0]]
                ]);

                if (is_null($vehicletypetype)) {
                    $vehicletypetype = new Vehicletypetype;
                    $vehicletypetype->setName($lRowValues[$lColumnMap1['Vehicletypetype'][0]]);
                    $em->persist($vehicletypetype);
                    $em->flush();
                }

                $vehicletype = $this->getDoctrine()->getRepository('VehicleBundle:Vehicletype')->findOneBy([
                    'komalogId' => $lRowValues[$lColumnMap1['Vehicletype'][0]]
                ]);

                if (is_null($vehicletype)) {
                    $vehicletype = new Vehicletype;
                    $vehicletype->setKomalogId($lRowValues[$lColumnMap1['Vehicletype'][0]]);
                    $vehicletype->setName($lRowValues[$lColumnMap1['Vehicletype'][1]] ?? $lRowValues[$lColumnMap1['Vehicletype'][2]]);
                    $vehicletype->setVehicletypetype($vehicletypetype);
                    $em->persist($vehicletype);
                    $em->flush();
                }

                // Fahrzeug anhand der komalogId finden
                $vehicle = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findOneBy([
                    'komalogId' => $lRowValues[$lColumnMap1['Vehicle'][0]]
                ]);

                // Wenn DISPO_OK auf 0 gesetzt ist Fahrzeug löschen
                if (!is_null($vehicle)
                && ($lRowValues[$lColumnMap1['Vehicle'][5]] == 0
                || empty($lRowValues[$lColumnMap1['Vehicle'][4]])
                || $lRowValues[$lColumnMap1['Vehicletype'][1]] == 'ohne'
                || $lRowValues[$lColumnMap1['Vehicletype'][2]] == 'ohne')) {
                    $em->remove($vehicle);
                    $em->flush();
                    $deleted++;
                    array_push($deleted_vehicles, $vehicle);
                } elseif (!is_null($vehicle)) {
                    $vehicle->setVehicletype($vehicletype);
                    $vehicle->setTrimbleId($lRowValues[$lColumnMap1['Vehicle'][1]]);
                    $vehicle->setName($lRowValues[$lColumnMap1['Vehicle'][2]]);
                    $vehicle->setComment($lRowValues[$lColumnMap1['Vehicle'][3]]);
                    $vehicle->setSerialNumber($lRowValues[$lColumnMap1['Vehicle'][4]]);
                    $em->persist($vehicle);
                    $em->flush();
                }

                // Kennzeichen aus dem NAME Feld filtern
                preg_match('/(?<![0-9])[0-9]{3,4}(?![0-9])/', $lRowValues[$lColumnMap1['Vehicle'][2]], $name);

                // Wenn kein Fahrzeug exestiert und das Fahrzeug nicht gelöscht ist -> hinzufügen
                // && $lRowValues[$lColumnMap1['Vehicletype'][1] == strtolower('pkw')
                if (
                    is_null($vehicle) &&									// Das Fahrzeug darf nicht existieren
                    $lRowValues[$lColumnMap1['Vehicle'][5]] == 1 &&			// Gelöschte Fahrzeuge nicht importieren
                    !empty($lRowValues[$lColumnMap1['Vehicle'][4]]) &&		// Fahrgestellnummer muss angegeben sein
                    (
                        strtolower($lRowValues[$lColumnMap1['Vehicletypetype'][0]]) == 'pkw' ||
                        strtolower($lRowValues[$lColumnMap1['Vehicletypetype'][0]]) == 'zugmaschine' ||
                        strtolower($lRowValues[$lColumnMap1['Vehicletypetype'][0]]) == 'motorwagen'
                    )
                ) {
                    if (!empty($lRowValues[$lColumnMap1['Vehicle'][1]]) || !empty($name[0])) {
                        try {
                            $vehicle = new Vehicle;
                            $vehicle->setKomalogId($lRowValues[$lColumnMap1['Vehicle'][0]]);
                            $vehicle->setPin(0);
                            $vehicle->setTrimbleId($lRowValues[$lColumnMap1['Vehicle'][1]] ?? $name[0]);
                            $vehicle->setName($lRowValues[$lColumnMap1['Vehicle'][2]]);
                            $vehicle->setComment($lRowValues[$lColumnMap1['Vehicle'][3]]);
                            $vehicle->setSerialNumber($lRowValues[$lColumnMap1['Vehicle'][4]] ?? '');
                            $vehicle->setVehicletype($vehicletype);
                            $em->persist($vehicle);
                            $em->flush();
                            $inserted++;
                            array_push($inserted_vehicles, $vehicle);
                        } catch (\Exception $e) {
                            echo $e;
                        }
                    }
                }
                $pRow++;
            }

            $em = $this->getDoctrine()->getManager();
            $consumptions = $em->getRepository('VehicleBundle:Consumption')->findAllWhereTrimbleIdIssetAndVehicleNull();
            foreach ($consumptions as $consumption) {
                $vehicle = $em->getRepository('VehicleBundle:Vehicle')->findByTrimbleId($consumption->getTrimbleId());
                $consumption->setVehicle($vehicle);
                $em->persist($consumption);
                $em->flush();
            }

            unlink($fileName);

            return $this->render('VehicleBundle:Default:import.html.twig', array(
                'step' => 2,
                'inserted' => $inserted,
                'inserted_vehicles' => $inserted_vehicles,
                'deleted' => $deleted,
                'deleted_vehicles' => $deleted_vehicles
            ));
        }

        return $this->render('VehicleBundle:Default:import.html.twig', array(
            'step' => 1,
            'inserted' => 0,
        ));
    }

    public function loadPkwsJsonAction()
    {
        $vehicles = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findAllByVehicletypetype([6]);

        $arrayCollection = array();
        foreach ($vehicles as $vehicle) {
            $curLog = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->findLatestByVehicle($vehicle);
            if (!empty($curLog) && !empty($curLog->getVehicleLogEndPosition())) {
                $curPosition = $curLog->getVehicleLogEndPosition();
            } elseif (!empty($curLog)) {
                $curPosition = $curLog->getVehicleLogBeginPosition();
            } else {
                $curPosition = null;
            }

            if (!empty($curPosition) && !empty($curLog) && empty($curLog->getVehicleLogEndPosition())) {
                $status = 'Unterwegs';
                $mileage = $curPosition->getMileage();
            } elseif (!empty($curPosition) && !empty($curPosition->getName())) {
                $status = $curPosition->getName();
                $mileage = $curPosition->getMileage();
            } elseif (!empty($curPosition)) {
                $status = '{"lat":'.$curPosition->getLat().', "lng":'.$curPosition->getLon().'}';
                $mileage = $curPosition->getMileage();
            } else {
                $status = '';
                $mileage = '';
            }
            //var_dump($curLog);die();
            $arrayCollection[] = array(
                'id'       => $vehicle->getId(),
                'pNumber'  => $vehicle->getName(),
                'brand'    => $vehicle->getComment(),
                'position' => $status,
                'mileage'  => $mileage,
                'pin'      => $vehicle->getPin(),
                'comment'  => (!empty($curLog)?$curLog->getComment():null),
                'clean'    => (!empty($curLog)&&!is_null($curLog->getVehicleClean())?($curLog->getVehicleClean()?'Ja':'Nein'):null),
            );
        }
        return new JsonResponse($arrayCollection);
    }

    public function jsonVehicleAction()
    {
        $vehicles = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findBy([], ['name'=>'ASC']);


        $arrayCollection = array();
        $departments = array();
        foreach ($vehicles as $vehicle) {
            $department = (is_null($vehicle->getVehicletype()) || is_null($vehicle->getVehicletype()->getVehicletypetype())) ? 'nicht Zugeordnet' : $vehicle->getVehicletype()->getVehicletypetype()->getName();

            $arrayCollection[] =
              array(
                'id'          => $vehicle->getId(),
                'name'        => $vehicle->getName(),
                'department'  => $department,
                'comment'     => $vehicle->getComment(),
               );
            if (!in_array($department, $departments)) {
                $departments[] = $department;
            }
        }

        return new JsonResponse($arrayCollection);
    }

    public function pinUpdateAction(Request $request)
    {
        try {
            $vehicle = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->find($request->get('id'));
            $vehicle->setPin($request->get('pin'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($vehicle);
            $em->flush();
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage());
        }

        return new JsonResponse('updated');
    }
    public function loadToursAction(Request $request, $vehicle=null)
    {
        $tours = $this->getDoctrine()->getRepository('VehicleBundle:Tour')->findByDateVehicle($request->get('start'), (new \DateTime($request->get('end')))->modify('midnight +1 day')->format('Y-m-d'), $vehicle);

        $tasks = array();

        foreach ($tours as $tour) {
            $id = "tour_".$tour->getId();
            $temp = [
                'id'               => $id,
                'resourceId'       => !empty($vehicle)?2:(!empty($tour->getVehicle())?$tour->getVehicle()->getId():null),
                'start'            => $tour->getOriginalDetail()->getBeginn()->format('Y-m-d H:i:s'),
                'end'              => $tour->getOriginalDetail()->getEnde()->format('Y-m-d H:i:s'),
                'originalend'      => $tour->getOriginalDetail()->getEnde()->format('Y-m-d H:i:s'),
                'name'             => $tour->getTournummer(),
                'title'            => $tour->getTournummer()." (".$tour->getBeladeort()." - ".$tour->getEntladeort().")",
                'backgroundColor'  => '#CC5750',
                'leerkm' => $tour->getOriginalDetail()->getLeerkm(),
                'lastkm' => $tour->getOriginalDetail()->getLastkm(),
                'gesamtkm' => $tour->getOriginalDetail()->getGesamtkm(),
                'leerzeit' => $tour->getOriginalDetail()->getLeerzeit(),
                'lastzeit' => $tour->getOriginalDetail()->getLastzeit(),
                'gesamtzeit' => $tour->getOriginalDetail()->getGesamtzeit(),
                'leerkosten' => $tour->getOriginalDetail()->getLeerkosten(),
                'lastkosten' => $tour->getOriginalDetail()->getLastkosten(),
                'gesamtkosten' => $tour->getOriginalDetail()->getGesamtkosten(),
                'erloes' => $tour->getOriginalDetail()->getErloes(),
            ];
            if (!empty($tour->getCurrentDetail()) && $tour->getCurrentDetail()->getId() != $tour->getOriginalDetail()->getId()) {
                $temp['start'] = $tour->getCurrentDetail()->getBeginn()->format('Y-m-d H:i:s');
                $temp['end'] = $tour->getCurrentDetail()->getEnde()->format('Y-m-d H:i:s');
                $temp['originalend'] = $tour->getCurrentDetail()->getEnde()->format('Y-m-d H:i:s');
                $temp['backgroundColor'] = '#8DB87C';
                $temp['leerkm'] = $tour->getCurrentDetail()->getLeerkm();
                $temp['lastkm'] = $tour->getCurrentDetail()->getLastkm();
                $temp['gesamtkm'] = $tour->getCurrentDetail()->getGesamtkm();
                $temp['leerzeit'] = $tour->getCurrentDetail()->getLeerzeit();
                $temp['lastzeit'] = $tour->getCurrentDetail()->getLastzeit();
                $temp['gesamtzeit'] = $tour->getCurrentDetail()->getGesamtzeit();
                $temp['leerkosten'] = $tour->getCurrentDetail()->getLeerkosten();
                $temp['lastkosten'] = $tour->getCurrentDetail()->getLastkosten();
                $temp['gesamtkosten'] = $tour->getCurrentDetail()->getGesamtkosten();
                $temp['erloes'] = $tour->getCurrentDetail()->getErloes();
            } else {
                $beginn = $tour->getOriginalDetail()->getBeginn();
                $ende = $tour->getOriginalDetail()->getEnde();
                $minende = clone $tour->getOriginalDetail()->getBeginn();
                $minende->modify('+15 minutes');
                if ($ende < $minende) {
                    $temp['end'] = $minende->format('Y-m-d H:i:s');
                    $temp['backgroundColor'] = '#A46A1F';
                }
            }
            $tasks[] = $temp;
        }

        return new JsonResponse($tasks);
    }

    public function importToursAction()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $process = new Process("python ../python/tourimport.py"); // '".$date."' '".(new \DateTime($date))->modify('midnight +1 month')->format('Y-m-d')."'");
        $process->setTimeout(null);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // return the output, don't use if you used NullOutput()
        $content = $process->getOutput();
        $temp_tours = json_decode($content, true);
        if (!empty($temp_tours)) {
            $re = '/\d+/m';
            $em = $this->getDoctrine()->getManager();
            foreach ($temp_tours as $temp_tour) {
                preg_match($re, $temp_tour['LKW'], $matches);
                if (!empty($matches[0])) {
                    $vehicle = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findOneBy(array('trimbleId'=>$matches[0]));
                } else {
                    $vehicle = null;
                }

                $tour = $this->getDoctrine()->getRepository('VehicleBundle:Tour')->findOneBy(array('tourid'=>$temp_tour['Tour']));
                if (empty($tour)) {
                    $tour = new Tour;
                }
                $tour->setTourid($temp_tour['Tour']);
                $tour->setTournummer($temp_tour['TOURNUMMER']);
                $tour->setGewicht(empty($temp_tour['GEWICHT'])?0:str_replace(',', '.', $temp_tour['GEWICHT']));
                $tour->setRechnungsempfaenger($temp_tour['RECHNUNGSEMPFAENGER']);
                $tour->setBeladeort($temp_tour['BELADEORT']);
                $tour->setEntladeort($temp_tour['ENTLADEORT']);
                $tour->setEmpfangsorte($temp_tour['EMPFANGSORTE']);
                $tour->setAnzahlauftraege($temp_tour['ANZAHLAUFTRAEGE']);
                $tour->setGeprueft($temp_tour['GEPRUEFT']);
                $tour->setBeschreibung($temp_tour['BESCHREIBUNG']);
                $tour->setVehicle($vehicle);

                $tourdetail = $tour->getOriginalDetail();
                if (empty($tourdetail)) {
                    $tourdetail = new Tourdetail;
                }
                $tourdetail->setBeginn(new \DateTime($temp_tour['TOURBEGINN']));
                $tourdetail->setEnde(new \DateTime($temp_tour['TOURENDE']));
                $tourdetail->setLeerkm(empty($temp_tour['LEERKM'])?0:str_replace(',', '.', $temp_tour['LEERKM']));
                $tourdetail->setLastkm(empty($temp_tour['LASTKM'])?0:str_replace(',', '.', $temp_tour['LASTKM']));
                $tourdetail->setGesamtkm(empty($temp_tour['GESAMTKM'])?0:str_replace(',', '.', $temp_tour['GESAMTKM']));
                $tourdetail->setLeerzeit(empty($temp_tour['LEERZEIT'])?0:str_replace(',', '.', $temp_tour['LEERZEIT']));
                $tourdetail->setLastzeit(empty($temp_tour['LASTZEIT'])?0:str_replace(',', '.', $temp_tour['LASTZEIT']));
                $tourdetail->setGesamtzeit(empty($temp_tour['GESAMTZEIT'])?0:str_replace(',', '.', $temp_tour['GESAMTZEIT']));
                $tourdetail->setLeerkosten(empty($temp_tour['LEERKOSTEN'])?0:str_replace(',', '.', $temp_tour['LEERKOSTEN']));
                $tourdetail->setLastkosten(empty($temp_tour['LASTKOSTEN'])?0:str_replace(',', '.', $temp_tour['LASTKOSTEN']));
                $tourdetail->setGesamtkosten(empty($temp_tour['GESAMTKOSTEN'])?0:str_replace(',', '.', $temp_tour['GESAMTKOSTEN']));
                $tourdetail->setErloes(empty($temp_tour['ERLOES'])?0:str_replace(',', '.', $temp_tour['ERLOES']));
                $em->persist($tourdetail);
                $tour->setOriginalDetail($tourdetail);
                $em->persist($tour);
                $em->flush();
                //$activity_days[] = $tour;
            }
        }
        return new JsonResponse(true);
    }

    public function updateTourAction(Request $request)
    {
        $tour = $this->getDoctrine()->getRepository('VehicleBundle:Tour')->find(explode('_', $request->get('id'))[1]);
        $temp_tour = [];
        $temp_tour['TOURBEGINN'] = $request->get('beginn');
        $startmileage = 0;
        $start = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findOneBySourceExactdate($tour->getVehicle()->getTrimbleId(), $request->get('beginn'));
        if (!empty($start)) {
            $startmileage = $start->getMileage();
        } else {
            $start = [
                $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findSmalleroneBySourceExactdate($tour->getVehicle()->getTrimbleId(), $request->get('beginn')),
                $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findBiggeroneBySourceExactdate($tour->getVehicle()->getTrimbleId(), $request->get('beginn')),
            ];
            if (!empty($start)) {
                $startmileage = ($start[0]->getMileage()+$start[1]->getMileage())/2;
            }
        }

        $temp_tour['TOURENDE'] = $request->get('ende');
        $endmileage = 0;
        $end = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findOneBySourceExactdate($tour->getVehicle()->getTrimbleId(), $request->get('ende'));
        if (!empty($end)) {
            $endmileage = $end->getMileage();
        } else {
            $end = [
                $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findSmalleroneBySourceExactdate($tour->getVehicle()->getTrimbleId(), $request->get('ende')),
                $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findBiggeroneBySourceExactdate($tour->getVehicle()->getTrimbleId(), $request->get('ende')),
            ];
            if (!empty($end)) {
                $endmileage = ($end[0]->getMileage()+$end[1]->getMileage())/2;
            }
        }

        $temp_tour['GESAMTKM'] = ($endmileage - $startmileage) / 1000;
        if ($request->get('entladezeit')) {
            $lastmileage = 0;
            $last = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findOneBySourceExactdate($tour->getVehicle()->getTrimbleId(), $request->get('entladezeit'));
            if (!empty($last)) {
                $lastmileage = $last->getMileage();
            } else {
                $last = [
                    $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findSmalleroneBySourceExactdate($tour->getVehicle()->getTrimbleId(), $request->get('entladezeit')),
                    $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findBiggeroneBySourceExactdate($tour->getVehicle()->getTrimbleId(), $request->get('entladezeit')),
                ];
                if (!empty($last)) {
                    $lastmileage = ($last[0]->getMileage()+$last[1]->getMileage())/2;
                }
            }
        }
        if ($lastmileage) {
            $temp_tour['LEERKM'] = ($endmileage - $lastmileage) / 1000;
            $temp_tour['LASTKM'] = ($lastmileage - $startmileage) / 1000;
        } else {
            $temp_tour['LEERKM'] = 0;
            $temp_tour['LASTKM'] = $temp_tour['GESAMTKM'];
        }

        $temp_tour['LASTZEIT'] = $request->get('lastzeit');
        $temp_tour['LEERZEIT'] = $request->get('leerzeit');
        $temp_tour['GESAMTZEIT'] = $temp_tour['LASTZEIT'] + $temp_tour['LEERZEIT'];

        $temp_tour['LEERKOSTEN'] = $temp_tour['LEERKM']*0.8 + $temp_tour['LEERZEIT'] * 40;
        $temp_tour['LASTKOSTEN'] = $temp_tour['LASTKM']*0.8 + $temp_tour['LASTZEIT'] * 40;
        $temp_tour['GESAMTKOSTEN'] = $temp_tour['GESAMTKM']*0.8 + $temp_tour['GESAMTZEIT'] * 40;

        $tourdetail = $tour->getCurrentDetail();
        if (empty($tourdetail)) {
            $tourdetail = new Tourdetail;
        }
        $tourdetail->setBeginn(new \DateTime($temp_tour['TOURBEGINN']));
        $tourdetail->setEnde(new \DateTime($temp_tour['TOURENDE']));
        $tourdetail->setLeerkm(empty($temp_tour['LEERKM'])?0:str_replace(',', '.', $temp_tour['LEERKM']));
        $tourdetail->setLastkm(empty($temp_tour['LASTKM'])?0:str_replace(',', '.', $temp_tour['LASTKM']));
        $tourdetail->setGesamtkm(empty($temp_tour['GESAMTKM'])?0:str_replace(',', '.', $temp_tour['GESAMTKM']));
        $tourdetail->setLeerzeit(empty($temp_tour['LEERZEIT'])?0:str_replace(',', '.', $temp_tour['LEERZEIT']));
        $tourdetail->setLastzeit(empty($temp_tour['LASTZEIT'])?0:str_replace(',', '.', $temp_tour['LASTZEIT']));
        $tourdetail->setGesamtzeit(empty($temp_tour['GESAMTZEIT'])?0:str_replace(',', '.', $temp_tour['GESAMTZEIT']));
        $tourdetail->setLeerkosten(empty($temp_tour['LEERKOSTEN'])?0:str_replace(',', '.', $temp_tour['LEERKOSTEN']));
        $tourdetail->setLastkosten(empty($temp_tour['LASTKOSTEN'])?0:str_replace(',', '.', $temp_tour['LASTKOSTEN']));
        $tourdetail->setGesamtkosten(empty($temp_tour['GESAMTKOSTEN'])?0:str_replace(',', '.', $temp_tour['GESAMTKOSTEN']));
        $tourdetail->setErloes($tour->getOriginalDetail()->getErloes());

        $em = $this->getDoctrine()->getManager();
        $em->persist($tourdetail);
        $tour->setCurrentDetail($tourdetail);
        $em->persist($tour);
        $em->flush();

        return new JsonResponse(true);
    }
    public function consumptionStatisticsNewAction( Request $request)
    {  

       /* $em = $this->getDoctrine()->getManager();
        //$em->getFilters()->disable('softdeleteable');
        $consumptions = $em->getRepository('VehicleBundle:Consumption')->findAll();
        foreach($consumptions as $consumption) 
        {  dump($consumption);
            $oldKraftstoffverbrauch= $consumption->getDetailByLabel('Kraftstoffverbrauch beim Fahren');
            $oldLeerlauf= $consumption->getDetailByLabel('Zeit Motor im Leerlauf');
            $oldStreckeKM= $consumption->getDetailByLabel('Strecke-KM');
            $oldzuschnellkm= $consumption->getDetailByLabel('zu schnell km');
            if(empty($oldKraftstoffverbrauch ) or empty($oldLeerlauf) or empty($oldStreckeKM ) or empty($oldzuschnellkm)) 
            {
            
                die();
            }

        }
        
        die();*/

        set_time_limit(0);
            ini_set('memory_limit', '-1');
        $teile = explode(" ",$request->get('date'));
        if($request->get('date') != NULL){
            $em = $this->getDoctrine()->getManager();
            $em->getFilters()->disable('softdeleteable');
            $consumptions = $em->getRepository('VehicleBundle:Consumption')->findAllByDate(date("Y-m-d",strtotime($teile[0])).' 00:00:00',date("Y-m-d",strtotime($teile[2])).' 23:59:59');
            $beginTime = $consumptions[(0)]->getConsumptionBeginTime()->format("Y-m-d");
            $EndTime = $consumptions[count($consumptions) -1]->getConsumptionEndTime()->format("Y-m-d");
            if($consumptions == NULL) 
             {$consumptions = $em->getRepository('VehicleBundle:Consumption')->findAllLatest();}
            $em->getFilters()->enable('softdeleteable');
            $usr = $this->container->get('security.context')->getToken()->getUser();
            $reportDates = $em->getRepository('VehicleBundle:Consumption')->findAllReportDates();
           
        }
      else if($request->get('begintime') != NULL and $request->get('endtime') != NULL ){
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $consumptions = $em->getRepository('VehicleBundle:Consumption')->findAllByDate($request->get('begintime'),$request->get('endtime'));
        $beginTime = $consumptions[(0)]->getConsumptionBeginTime()->format("Y-m-d");
        $EndTime = $consumptions[count($consumptions) -1]->getConsumptionEndTime()->format("Y-m-d");
         if($consumptions == NULL)  {$consumptions = $em->getRepository('VehicleBundle:Consumption')->findAllLatest();
            $beginTime = $consumptions[(0)]->getConsumptionBeginTime()->format("Y-m-d");
            $EndTime = $consumptions[count($consumptions) -1]->getConsumptionEndTime()->format("Y-m-d");}
        $em->getFilters()->enable('softdeleteable');
        $usr = $this->container->get('security.context')->getToken()->getUser();
        $reportDates = $em->getRepository('VehicleBundle:Consumption')->findAllReportDates();

    }
    else{
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $consumptions = $em->getRepository('VehicleBundle:Consumption')->findAllLatest();
        $beginTime = $consumptions[(0)]->getConsumptionBeginTime()->format("Y-m-d");
        $EndTime = $consumptions[count($consumptions) -1]->getConsumptionEndTime()->format("Y-m-d");
        $em->getFilters()->enable('softdeleteable');
        $usr = $this->container->get('security.context')->getToken()->getUser();
        $reportDates = $em->getRepository('VehicleBundle:Consumption')->findAllReportDates();
        }
        $LKVtypes = array();
        
        foreach ($consumptions as $consumption)
        {   
         if($consumption->getVehicle()!= NULL){
             // LKW L258 , L779 => type name is : MTW / Solo / Kran    => Hlaakmann
            if($consumption->getVehicle()->getId() == '283' or $consumption->getVehicle()->getId() == '403')
               {
                $consumption->getVehicle()->getvehicletype()->setname('MTW / Solo / Kran');
               }
             if ($consumption->getDetailByLabel('Kraftstoffverbrauch beim Fahren')
              && ($consumption->getDetailByLabel('Strecke-KM'))
              && ($consumption->getDetailByLabel('Strecke-KM')->getValue() > 5 && $consumption->getDetailByLabel('Kraftstoffverbrauch beim Fahren')->getValue() > 0)) {
             if($consumption->getVehicle()->getvehicletype()->getname() == 'MTW / Kran' or $consumption->getVehicle()->getvehicletype()->getname() == 'Sattelzugmaschine')
             {
                if (!in_array($consumption->getVehicle()->getvehicletype()->getname().':'.$consumption->getVehicle()->getManufactur(), $LKVtypes)){
                $LKVtypes[$consumption->getVehicle()->getvehicletype()->getname().':'.$consumption->getVehicle()->getManufactur()][]= $consumption->getDetailByLabel('Kraftstoffverbrauch beim Fahren')->getValue() ;
              }
            }
            else {
                if (!in_array($consumption->getVehicle()->getvehicletype()->getname(), $LKVtypes)){
                    $LKVtypes[$consumption->getVehicle()->getvehicletype()->getname()][]= $consumption->getDetailByLabel('Kraftstoffverbrauch beim Fahren')->getValue() ;
                } 
                } 
          }
           }
           
           $oldconsumption = $em->getRepository('VehicleBundle:Consumption')->getOldConsumption($consumption->getId(),$consumption->getDriver()->getId());
           $consumption->oldconsumption = $oldconsumption ;
        }
        foreach($LKVtypes as $key => $value ) 
        {  $knopresult[$key]=array_sum($value)/count($value); }
        ksort($knopresult);
      return $this->render('VehicleBundle:Default:consumptionStatisticsNew.html.twig', array(
            'beginTime' => $beginTime,
            'endTime' => $EndTime,
            'consumptions' => $consumptions,
            'usr' => $usr,
            'reportDates' => $reportDates,
            'knopresults' => $knopresult,
        ));
    }

    public function consumptionStatisticsByDriverJsonAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        //for AllDriv -->>  

        $consumptions = $em->getRepository('VehicleBundle:Consumption')->findAllByDriver($id);
        dump($consumptions );
        
        foreach ($consumptions  as $consumption => $key) {
            dump($key  , $consumption);
            die();
            $consumption->getOldDetailByConsumptionIdAndLabel($consumption->getId(),'old');
            $consumption->getOldDetailByConsumptionIdAndLabel($consumption->getId(),'old');
            $consumption->getOldDetailByConsumptionIdAndLabel($consumption->getId(),'old');
            $consumption->getOldDetailByConsumptionIdAndLabel($consumption->getId(),'old');
            $consumption->getOldDetailByConsumptionIdAndLabel($consumption->getId(),'old');
        }
        die();
        $usr = $this->container->get('security.context')->getToken()->getUser();
        return $this->render('VehicleBundle:Default:charJsDriver.html.twig', array(
            'consumptions' => $consumptions,
            'usr' => $usr,
        ));
        return 0 ;
    }

    public function kilometersDrivenAction($date1,$date2)
    {   
        $date1 = (new \Datetime($date1))->modify('first day of this month')->format('Y-m-d');
        $date2 = (new \Datetime($date2))->modify('first day of this month')->format('Y-m-d');
        $did= $this->getUser()->getEmployee()->getTrimbleId();
        //SELECT * from vehicle LEFT JOIN (SELECT * FROM `expenseview` GROUP by `source`)AS expenseview on expenseview.`source` = vehicle.trimble_id 
        //Where time > '' and time < '' 
        //SELECT `source`,min(`time`)AS BeginDate ,max(`time`)as Enddate, min(`mileage`) AS BeginnKM, max(`mileage`) As EndeKM, max(`mileage`)-min(`mileage`) AS SUMME FROM `tracedata` ORDER BY `time`  DESC 
        $em = $this->getDoctrine()->getManager(); 
        $connection = $em->getConnection();
            $statement = $connection->prepare("
            SELECT ResultInfo.* ,vehicletype.name AS vehicletypename FROM vehicletype RIGHT JOIN ( SELECT * from vehicle RIGHT JOIN (SELECT source,min(time)AS BeginDate ,max(time)as Enddate, min(mileage) AS BeginnKM, max(mileage) AS EndeKM, max(mileage)-min(mileage) AS SUMME FROM expenseview where did != 0 and mileage !='999999999' 
            AND time >='".$date1."' AND time < '".$date2."' AND mileage !=0 And source != '' GROUP BY source)AS KMRESULT
            ON KMRESULT.source = vehicle.trimble_id)AS ResultInfo
            ON ResultInfo.vehicletype_id = vehicletype.id
            ");
        $statement->execute();
        $reportDates = $statement->fetchAll();
        $date1 = (new \Datetime($date1))->format('M. Y');
        $date2 = (new \Datetime($date2))->format('M. Y');
        return $this->render('VehicleBundle:Default:kilometersDriven.html.twig', array(
            'reportDates' => $reportDates,
            'date1' => $date1,
            'date2' => $date2,
        ));
    }
    public function kilometersJearDrivenAction()
    {   $date1 =(new \Datetime('2019-01-01'))->modify('first day of this month')->format('M. Y');
        $date2 =(new \Datetime())->format('M. Y');
      
      //  die();
        $did= $this->getUser()->getEmployee()->getTrimbleId();
        //SELECT * from vehicle LEFT JOIN (SELECT * FROM `expenseview` GROUP by `source`)AS expenseview on expenseview.`source` = vehicle.trimble_id 
        //Where time > '' and time < '' 
        //SELECT `source`,min(`time`)AS BeginDate ,max(`time`)as Enddate, min(`mileage`) AS BeginnKM, max(`mileage`) As EndeKM, max(`mileage`)-min(`mileage`) AS SUMME FROM `tracedata` ORDER BY `time`  DESC 
        $em = $this->getDoctrine()->getManager(); 
        $connection = $em->getConnection();
            $statement = $connection->prepare("
            SELECT ResultInfo.* ,vehicletype.name AS vehicletypename FROM vehicletype RIGHT JOIN ( SELECT * from vehicle RIGHT JOIN (SELECT source,min(time)AS BeginDate ,max(time)as Enddate, min(mileage) AS BeginnKM, max(mileage) AS EndeKM, max(mileage)-min(mileage) AS SUMME FROM expenseview where did != 0 and mileage !='999999999' and mileage !=0 And source != '' GROUP BY source)AS KMRESULT
            ON KMRESULT.source = vehicle.trimble_id)AS ResultInfo
            ON ResultInfo.vehicletype_id = vehicletype.id
            ");
        $statement->execute();
        $reportDates = $statement->fetchAll();
        return $this->render('VehicleBundle:Default:kilometersDriven.html.twig', array(
            'reportDates' => $reportDates,
            'date1' => $date1,
            'date2' => $date2,
        ));
    }

    public function importScaniaXlsxAction(Request $request)
    { 
        if (!is_null($request->files->get('excelfile'))) {
            $inserted_vehicles = [];
            $Row= 5;
            $inserted=0;
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            $em = $this->getDoctrine()->getManager();
            $uploadService = $this->get('app.upload_service');
            $fileName = $uploadService->upload($request->files->get('excelfile'));
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($fileName);
            
    
            $A2=explode(":",$objPHPExcel->getActiveSheet()->getCell('A2')->getValue())[0];
            if($A2 == 'Wochenübersicht')
             {  $language = 'DE';}
            else if($A2 == 'Weekly overview')
            {$language = 'EN';}
            $dateString= explode('-',explode(":",$objPHPExcel->getActiveSheet()->getCell('A2')->getValue())[1]);
            $date1= explode(' ',$dateString[0]);
            $monthNameDe = array('Januar' => '01', 'Februar' => '02' , 'März' => '03',  'April' => '04'  , 'Mai' => '05' , 'Juni' => '06', 'Juli' =>'07' ,  'August'=>'08'  ,'September'=> '09' , 'Oktober'=>'10'  , 'November'=> '11' ,  'Dezember'=> '12' );
            $monthNameEn = array('January' => '01', 'February' => '02' , 'March' => '03',  'April' => '04'  , 'May' => '05' , 'June' => '06', 'July' =>'07' ,  'August'=>'08'  ,'September'=> '09' , 'October'=>'10'  , 'November'=> '11' ,  'December'=> '12' );
            $date2=explode(' ',$dateString[1]);
            
            if($language == 'DE')
            {
                $datebegin = array ( 
                'd' => explode('.',$date1[1])[0],
                'm' => $monthNameDe[$date1[2]],
                'y' => $date1[3],
                 );
            /*   $dateend =  array (
                'd' => explode('.',$date2[2] )[0],
                'm' => $monthNameDe[$date2[3]],
                'y' => $date2[4],
                ); */
            }
            else if($language == 'EN')
            {
                $datebegin = array ( 
                    'd' => $date1[1],
                    'm' => $monthNameEn[$date1[2]],
                    'y' => $date1[3],
                     );
            }
            $date =(new \Datetime($datebegin['y'].'-'.$datebegin['m'].'-'.$datebegin['d']))->modify('+2 day');
            while (!empty($objPHPExcel->getActiveSheet()->getCell('A'.$Row)->getValue())) {
                $vehicle = $em->getRepository('VehicleBundle:Vehicle')->findByTrimbleId(explode(' ',$objPHPExcel->getActiveSheet()->getCell('A'.$Row)->getValue())[1]);
                $Consumptions = $em->getRepository('VehicleBundle:Consumption')->findByVehicleDate($vehicle->getId() , $date->format('Y-m-d'));
            if(!empty($Consumptions) && in_array($vehicle->getTrimbleId(), array("532","533","535","538","540") ))
                { foreach ($Consumptions as $Consumption) 
                    {
                    $consumptionDetails =$Consumption->getConsumptionDetails();
                    $Kraftstoffverbrauch = true;
                    foreach ($consumptionDetails as $consumptionDetail)
                    {
                           if($consumptionDetail->getLabel() == 'Kraftstoffverbrauch beim Fahren') {
                                $Kraftstoffverbrauch =false;
                                $consumptionDetail->setConsumption($Consumption);
                                $consumptionDetail->setLabel('Kraftstoffverbrauch beim Fahren');
                                $consumptionDetail->setValue($objPHPExcel->getActiveSheet()->getCell('K'.$Row)->getValue());
                                $consumptionDetail->setUnit('Kg /100km');
                                $consumptionDetail->setComment('From Code');
                                $em->persist($consumptionDetail);$em->flush();
                                array_push($inserted_vehicles, $vehicle);
                                $inserted++;
                            }
                    }
                    if($Kraftstoffverbrauch){
                        $item = new \VehicleBundle\Entity\ConsumptionDetail();
                        $item->setConsumption($Consumption);
                        $item->setLabel('Kraftstoffverbrauch beim Fahren');
                        $item->setValue($objPHPExcel->getActiveSheet()->getCell('K'.$Row)->getValue());
                        $item->setUnit('Kg /100km');
                        $item->setComment('From Code');
                        $em->persist($item);$em->flush();
                        array_push($inserted_vehicles, $vehicle);
                        $inserted++;
                    }
                   }
                 }
             $Row++; 
            }
            unlink($fileName);
            return $this->render('VehicleBundle:Default:importScania.html.twig', array(
                'step' => 2,
                'inserted_vehicles' => $inserted_vehicles,
                'inserted' => $inserted,
            ));
        }
        return $this->render('VehicleBundle:Default:importScania.html.twig', array(
            'step' => 1,
            'inserted' => 0,
        ));
    }
}
