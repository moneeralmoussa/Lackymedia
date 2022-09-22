<?php

namespace VehicleBundle\Controller;

use VehicleBundle\Entity\Vehicle;
use VehicleBundle\Entity\Vehicletype;
use VehicleBundle\Entity\Vehicletypetype;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $vehicles = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findAll();

        return $this->render('VehicleBundle:Default:index.html.twig', array(
            'vehicles' => $vehicles,
            'base_date' => time(),
        ));
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
            'base_date' => empty($date)?$activity_dates[0]["time"]:(new \DateTime($date)),
            'vehicle' => $vehicle,
            'activity_dates' => $activity_dates,
            'locations' => $locations,
        ));
    }

    public function loadActivitiesAction($id, $date)
    {
        $vehicle = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->find($id);

        $traces = array();
        $workdays = array();
        if (!\is_null($vehicle->getTrimbleId())) {
            $traces = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findAllBySourceDate($vehicle->getTrimbleId(), $date, (new \DateTime($date))->modify('midnight +1 day')->format('Y-m-d'));
            $workdays = $this->getDoctrine()->getRepository('ExpenseBundle:Workday')->findBy(array("truck"=>$vehicle->getId(), "date"=>new \DateTime($date)));
        }

        $aty_types = array('10','11','13');
        $driver_ignore_types = array('9', '55', '82', '87', '300', '301', '302');

        $positions = array();
        $drivers = array();
        $activities = array();

        foreach ($traces as $trace) {
            if (!\is_null($trace->getLat()) && !\is_null($trace->getLon())) {
                $positions_cnt = count($positions);

                if ($positions_cnt>0 && $positions[$positions_cnt - 1]['lat'] == $trace->getLat() && $positions[$positions_cnt - 1]['lon'] == $trace->getLon()) {
                    $positions[$positions_cnt - 1]['starttime'] = $trace->getTime();
                } else {
                    $positions[] = array(
                        'lat' => $trace->getLat(),
                        'lon' => $trace->getLon(),
                        'starttime' => $trace->getTime(),
                        'endtime' => $trace->getTime(),
                    );
                }

                $properties = array();
                foreach ($trace->getTracedataproperties() as $property) {
                    $properties[$property->getPropertyKey()] = $property->getPropertyValue();
                }

                if (!in_array($trace->getType(), $driver_ignore_types) && array_key_exists('DID', $properties) && !array_key_exists($properties['DID'], $drivers)){
                    $drivers[$properties['DID']] = array(
                        'startlat' => $trace->getLat(),
                        'startlon' => $trace->getLon(),
                        'starttime' => $trace->getTime(),
                        'endlat' => $trace->getLat(),
                        'endlon' => $trace->getLon(),
                        'endtime' => $trace->getTime(),
                        'status' => 'FAILED',
                        'driver' => $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->findOneBy(array('trimbleId'=>$properties['DID'])),
                    );
                } else if (!in_array($trace->getType(), $driver_ignore_types) && array_key_exists('DID', $properties) && array_key_exists($properties['DID'], $drivers)) {
                    $drivers[$properties['DID']]['starttime'] = $trace->getTime();
                    $drivers[$properties['DID']]['startlat'] = $trace->getLat();
                    $drivers[$properties['DID']]['startlon'] = $trace->getLon();
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
                    } else if (in_array($trace->getType(), array('11'))) {
                        $activities[$properties['ATY']]['lids'][$properties['LID']]['cancelled'] = $trace->getTime();
                    } else if (in_array($trace->getType(), array('13'))) {
                        $activities[$properties['ATY']]['lids'][$properties['LID']]['endtime'] = $trace->getTime();
                        $activities[$properties['ATY']]['lids'][$properties['LID']]['endlat'] = $trace->getLat();
                        $activities[$properties['ATY']]['lids'][$properties['LID']]['endlon'] = $trace->getLon();
                    }
                }
            }
        }

        foreach ($activities as $activity_key => $activity_value) {
            foreach($activities[$activity_key]['lids'] as $lid_key => $lid_value) {
                if (array_key_exists('endtime', $activities[$activity_key]['lids'][$lid_key]) && !array_key_exists('starttime', $activities[$activity_key]['lids'][$lid_key])) {
                    $activities[$activity_key]['lids'][$lid_key]['starttime'] = clone $activities[$activity_key]['lids'][$lid_key]['endtime'];
                    $activities[$activity_key]['lids'][$lid_key]['startlat'] = $activities[$activity_key]['lids'][$lid_key]['endlat'];
                    $activities[$activity_key]['lids'][$lid_key]['startlon'] = $activities[$activity_key]['lids'][$lid_key]['endlon'];
                    $activities[$activity_key]['lids'][$lid_key]['starttime']->modify('midnight');
                } else if (array_key_exists('starttime', $activities[$activity_key]['lids'][$lid_key]) && !array_key_exists('endtime', $activities[$activity_key]['lids'][$lid_key]) && !array_key_exists('cancelled', $activities[$activity_key]['lids'][$lid_key])) {
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

        usort($activities, function($a, $b) {
            if (strtolower ($a['activity']->getName()) == strtolower ($b['activity']->getName())) {
                return 0;
            }
            return (strtolower ($a['activity']->getName()) < strtolower ($b['activity']->getName())) ? -1 : 1;
        });

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
                    $drivers[$workday->getEmployee()->getTrimbleId()] = array(
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

        return $this->render('VehicleBundle:Default:loadActivities.html.twig', array(
            'positions' => $positions,
            'drivers' => $drivers,
            'activities' => $activities,
        ));
    }

    public function loadMonthKmCsvAction($date)
    {
        set_time_limit(0);
        ini_set('memory_limit','-1');

        $vehicles = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findAll();
        $driver_ignore_types = array('9', '55', '82', '87', '300', '301', '302');
        $activity_days = array();


        foreach($vehicles as $vehicle) {
            $traces = array();
            if (!\is_null($vehicle->getTrimbleId())) {
                $traces = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findAllWithDriverBySourceDate($vehicle->getTrimbleId(), $date, (new \DateTime($date))->modify('midnight +1 month')->format('Y-m-d'));
            }

            foreach ($traces as $trace) {
                if (!in_array($trace->getType(), $driver_ignore_types) && !\is_null($trace->getDid()) && !\is_null($trace->getMileage())) {
                    $source = $trace->getSource();
                    $day = $trace->getTime()->format('Y-m-d');

                    if(!array_key_exists($source, $activity_days)){
                        $activity_days[$source] = array();
                    }
                    if(!array_key_exists($day, $activity_days[$source])){
                        $activity_days[$source][$day] = array(
                            'date' => $trace->getTime(),
                            'starttime' => $trace->getTime(),
                            'startkm' => $trace->getMileage()/1000,
                            'endtime' => $trace->getTime(),
                            'endkm' => $trace->getMileage()/1000,
                            'difftime' => 0,
                            'diffkm' => 0,
                        );
                    } else {
                        $activity_days[$source][$day]['endtime'] = $trace->getTime();
                        $activity_days[$source][$day]['endkm'] = $trace->getMileage()/1000;
                    }
                }
            }

            foreach($activity_days as $source=>$sval) {
                foreach($activity_days[$source] as $day=>$dval) {
                    $activity_days[$source][$day]['difftime'] = date_diff($activity_days[$source][$day]['starttime'], $activity_days[$source][$day]['endtime']);
                    $activity_days[$source][$day]['diffkm'] = $activity_days[$source][$day]['endkm'] - $activity_days[$source][$day]['startkm'];
                }
            }
        }

        $response = $this->render('VehicleBundle:Default:loadMonthKm.csv.twig', array(
            'activity_days' => $activity_days,
            'base_date' => $date,
        ));
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="Fahrzeugkm_'.$date.'.csv"');

        return $response;
    }

    public function importVehiclesXlsxAction(Request $request) {
        if (!is_null($request->files->get('excelfile'))) {
            set_time_limit(0);
            ini_set('memory_limit','-1');

            $em = $this->getDoctrine()->getManager();

            $uploadService = $this->get('app.upload_service');
            $fileName = $uploadService->upload($request->files->get('excelfile'));

            $myXlsLoader = $uploadService->xlsLoader($fileName);

            $inserted = 0;

            $lColumnNames = $myXlsLoader[0];
            $objPHPExcel = $myXlsLoader[1];

            $lColumnMap = array(
                'ID' => 'Vehicle_komalogId',
                'KST' => 'Vehicle_trimbleId',
                'NAME' => 'Vehicle_name',
                'FAHRZ_HERSTELLER' => 'Vehicle_comment',
                'FAHRZTYP' => 'Vehicletype_komalogId',
                'FAHRZ_TYPBEZEICHNUNG' => 'Vehicletype_name',
                'FAHRZARTNAME' => 'Vehicletypetype_name',
            );

            $lColumnMap1 = array(
                'Vehicle' => array ('Vehicle_komalogId', 'Vehicle_trimbleId', 'Vehicle_name', 'Vehicle_comment'),
                'Vehicletype' => array ('Vehicletype_komalogId', 'Vehicletype_name'),
                'Vehicletypetype' => array ('Vehicletypetype_name'),
            );

            /*$lColumnMap2 = array(
                'Vehicle_komalogId' => array('Vehicle', 'komalogId'),
                'Vehicle_trimbleId' => array('Vehicle', 'trimbleId'),
                'Vehicle_name' => array('Vehicle', 'name'),
                'Vehicle_comment' => array('Vehicle', 'comment'),
                'Vehicletype_komalogId' => array('Vehicletype', 'komalogId'),
                'Vehicletype_name' => array('Vehicletype', 'name'),
                'Vehicletypetype_name' => array('Vehicletypetype', 'name'),
            );*/

            $iColumnID = array_search("ID", $lColumnNames);
            $pRow = 2;

            while ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($iColumnID, $pRow)->getValue() != "") {
                $lRowValues = array();

                for($pColumn=0;$pColumn<count($lColumnNames);$pColumn++) {
                    if(array_key_exists($lColumnNames[$pColumn],$lColumnMap)){
                        $cellValue = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($pColumn, $pRow)->getValue();
                        $lRowValues[$lColumnMap[$lColumnNames[$pColumn]]] = ($cellValue==""?null:$cellValue);
                    }
                }

                $vehicletypetype = $this->getDoctrine()->getRepository('VehicleBundle:Vehicletypetype')->findOneBy(array('name'=>$lRowValues[$lColumnMap1['Vehicletypetype'][0]]));
                if (is_null($vehicletypetype)) {
                    $vehicletypetype = new Vehicletypetype;
                    $vehicletypetype->setName($lRowValues[$lColumnMap1['Vehicletypetype'][0]]);
                }
                $em->persist($vehicletypetype);

                $vehicletype = $this->getDoctrine()->getRepository('VehicleBundle:Vehicletype')->findOneBy(array('komalogId'=>$lRowValues[$lColumnMap1['Vehicletype'][0]]));
                if (is_null($vehicletype)) {
                    $vehicletype = new Vehicletype;
                    $vehicletype->setKomalogId($lRowValues[$lColumnMap1['Vehicletype'][0]]);
                }
                $vehicletype->setName($lRowValues[$lColumnMap1['Vehicletype'][1]]);
                $vehicletype->setVehicletypetype($vehicletypetype);
                $em->persist($vehicletype);

                $vehicle = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findOneBy(array('komalogId'=>$lRowValues[$lColumnMap1['Vehicle'][0]]));
                if (is_null($vehicle)) {
                    $vehicle = new Vehicle;
                    $vehicle->setKomalogId($lRowValues[$lColumnMap1['Vehicle'][0]]);
                }
                $vehicle->setTrimbleId($lRowValues[$lColumnMap1['Vehicle'][1]]);
                $vehicle->setName($lRowValues[$lColumnMap1['Vehicle'][2]]);
                $vehicle->setComment($lRowValues[$lColumnMap1['Vehicle'][3]]);
                $vehicle->setVehicletype($vehicletype);
                $em->persist($vehicle);

                $em->flush();
                $inserted += 1;

                $pRow++;
            }

            unlink($fileName);

            return $this->render('VehicleBundle:Default:import.html.twig', array(
                'step' => 2,
                'inserted' => $inserted,
            ));
        }

        return $this->render('VehicleBundle:Default:import.html.twig', array(
            'step' => 1,
            'inserted' => 0,
        ));
    }

    public function loadPkwsJsonAction(){
        $vehicles = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findAllByVehicletypetype([6]);

        $arrayCollection = array();
        foreach($vehicles as $vehicle) {
            $curLog = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->findLatestByVehicle($vehicle);
            if(!empty($curLog) && !empty($curLog->getVehicleLogEndPosition())) {
                $curPosition = $curLog->getVehicleLogEndPosition();
            } elseif(!empty($curLog)) {
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
}
