<?php

namespace TrimbleSoapBundle\Service;

class TrimbleSoapService
{
    private $doctrine;
    private $mark = null;
    private $polls = 0;
    private $tz_soap;
    private $tz_app;
    private $fmsreport=[];

    public function __construct($doctrine) {
        $this->doctrine = $doctrine;
        $this->polls = 0;
        $this->tz_soap = new \DateTimeZone('UTC');
        $this->tz_app = new \DateTimeZone('Europe/Berlin');

        $this->getCurrentMark();
    }

    public function getCurrentMark() {
        $repository = $this->doctrine->getRepository('TrimbleSoapBundle:Tracepolldata');
        $result = $repository->createQueryBuilder('a')->orderBy('a.id', 'DESC')->getQuery()->setMaxResults(1)->getOneOrNullResult();

        if ($result && ((time() - strtotime($result->getMark())) > 7800)) {
            $this->mark = $result->getMark();
        } else {
            $this->mark = null;
        }
    }

    public function getFunctions($url) {
        $options = array('login' => "361gradmedien@gl", 'password' => "5yywebcA");

        $client = new \SoapClient($url, $options);
        $result = $client->__getFunctions();
        var_dump($url, $result);
    }

    public function pollTraces($mark=null) {
        $url = "https://soap.box.trimbletl.com/fleet-service/Tracking?wsdl";
        $options = array('login' => "361gradmedien@gl", 'password' => "5yywebcA");

        if (!is_null($mark)) {
            $this->mark = $mark;
        }

        if (is_null($this->mark)) {
            return 0;
        }

        $client = new \SoapClient($url, $options);
        $result = $client->pollTraces(
          array(
            "customer" => "114152-80",
            "mark" => $this->mark,
          )
        );

        $this->insertIntoDb($result);

        $this->polls++;
        //var_dump($this->polls);

        if ($this->polls < 48) {
            sleep(1);
            $this->getCurrentMark();
            $this->pollTraces();
        }
    }

    public function insertIntoDb($xml)
    {
        //var_dump($xml);
        if (isset($xml->return))
        {
            $em = $this->doctrine->getManager();

            $tracepolldata = new \TrimbleSoapBundle\Entity\Tracepolldata();
            $tracepolldata->setMark($xml->return->mark);
            $tracepolldata->setMore($xml->return->more);
            $tracepolldata->setActive(false);
            $em->persist($tracepolldata);

            if (isset($xml->return->traces)) {
                foreach ($xml->return->traces as $key => $trace) {
                    $tracedata = new \TrimbleSoapBundle\Entity\Tracedata();
                    $tracedata->setTracepolldata($tracepolldata);
                    $tracedata->setType($trace->type);
                    $tracedata->setSource($trace->source);
                    $tracedata->setTime((new \DateTime($trace->time, $this->tz_soap))->setTimezone($this->tz_app));
                    if (isset($trace->coordinate)) {
                        $tracedata->setLat($trace->coordinate->latitude);
                        $tracedata->setLon($trace->coordinate->longitude);
                    }
                    if (isset($trace->mileage)) {
                        $tracedata->setMileage($trace->mileage);
                    }
                    $tracedata->setHeading($trace->heading);
                    $tracedata->setSpeed($trace->speed);
                    $em->persist($tracedata);

                    if (isset($trace->property)) {
                        if (isset($trace->property->key)) {
                            $property = $trace->property;
                            $tracedataproperty = new \TrimbleSoapBundle\Entity\Tracedataproperty();
                            $tracedataproperty->setTracedata($tracedata);
                            $tracedataproperty->setPropertyKey($property->key);
                            $tracedataproperty->setPropertyValue($property->value);
                            $em->persist($tracedataproperty);
                            if ($property->key == 'DID') {
                                $tracedata->setDid($property->value);
                            }
                        } else {
                            foreach ($trace->property as $property) {
                                $tracedataproperty = new \TrimbleSoapBundle\Entity\Tracedataproperty();
                                $tracedataproperty->setTracedata($tracedata);
                                $tracedataproperty->setPropertyKey($property->key);
                                $tracedataproperty->setPropertyValue($property->value);
                                $em->persist($tracedataproperty);
                                if ($property->key == 'DID') {
                                    $tracedata->setDid($property->value);
                                }
                            }
                        }
                    }
                }
            }

         $em->flush();
        }
    }

    private function insertConsumptionIntoDb($consumptionframe, $driver=null) {
        $em = $this->doctrine->getManager();
        $fmsreport = [];
        $validfms =[
            'Ausrollentfernung'=>'m/km',
            'Ausrollzeit'=>'%',
            'Beschleunigung > 1 m/s²'=>'/100km',
            'Beschleunigung > 1,3 m/s²'=>'/100km',
            'Beschleunigung > 1,6 m/s²'=>'/100km',
            'Bremsen'=>'/100km',
            'Drehzahl Nebenantrieb'=>'rpm',
            'Durchschnittsgeschwindigkeit'=>'km/h',
            'Echtzeit laufender Motor'=>'%',
            'Fahrzeit'=>'%',
            'Gesamtzeit Kraftstoffverbrauch'=>'l/h',
            'Kraftstoffverbrauch '=>'l/100km',
            'Kraftstoffverbrauch beim Fahren'=>'l/100km',
            'Kraftstoffverbrauch Gesamtentfernung'=>'l/100km',
            'Kraftstoffverbrauch im Stand'=>'l/h',
            'Kraftstoffverbrauch kalter Motor'=>'l/h',
            'Kraftstoffverbrauch stationärer '=>'l/h',
            'Kühlmittel > 90°C'=>'%',
            'Max. U/min-Zeit'=>'',
            'Maximaldrehzahl bei kaltem Motor'=>'',
            'Maximaldrehzahl bei Nebenantrieb'=>'',
            'Motorstunden'=>'',
            'Stillstandzeit Nebenantrieb'=>'%',
            'Tempomat U/min'=>'rpm',
            'Tempomat-Zeit'=>'%',
            'Verlangsamung > 1 m/s²'=>'/100km',
            'Verlangsamung > 1,3 m/s²'=>'/100km',
            'Verlangsamung > 1,6 m/s²'=>'/100km',
            'Zeit Motor im Leerlauf'=>'%',
        ];

        $consumption = new \VehicleBundle\Entity\Consumption();
        if (!empty($driver)) {
            $consumption->setDriver($driver);
            if(array_key_exists($driver->getTrimbleId(), $this->fmsreport)){
                $fmsreport = $this->fmsreport[$driver->getTrimbleId()];
            }
        }

        $consumption->setVehicle($consumptionframe['vehicle']);
        $consumption->setConsumptionBeginTime(new \DateTime($consumptionframe['begin']));
        $consumption->setConsumptionEndTime((new \DateTime($consumptionframe['end']))->modify('-1 second'));
        $consumption->setTrimbleId($consumptionframe['source']);

        foreach($fmsreport as $key => $value) {
            if(array_key_exists($key, $validfms) && is_array($value) && !empty($value['wert'])){
                if($validfms[$key] == '%' || $validfms[$key] == 'l/100km'){
                    $value['wert'] *= 100;
                }
                $item = new \VehicleBundle\Entity\ConsumptionDetail();
                $item->setConsumption($consumption);
                $item->setLabel($key);
                $item->setValue($value['wert']);
                $item->setUnit($validfms[$key]);
                $item->setComment(json_encode($value));
                $em->persist($item);
            }
        }

        // Strecke
        $streckeKm = new \VehicleBundle\Entity\ConsumptionDetail();
        $streckeKm->setConsumption($consumption);
        $streckeKm->setLabel('Strecke-KM');
        $streckeKm->setValue($consumptionframe['distance']);
        $streckeKm->setUnit('km');
        $em->persist($streckeKm);
        $consumption->addConsumptionDetail($streckeKm);

        $distance = $consumptionframe['distance']/100 != 0?$consumptionframe['distance']/100:1;

        /*// starkes Bremsen '/ 100 km'
        $decelerationLimitViolations = $consumptionframe["consumptionDetails"]["decelerationLimitViolations"];

        $decelerationLimitViolation = new \VehicleBundle\Entity\ConsumptionDetail();
        $decelerationLimitViolation->setConsumption($consumption);
        $decelerationLimitViolation->setLabel('starkes Bremsen');
        $decelerationLimitViolation->setValue(count($decelerationLimitViolations));// / $distance);
        $decelerationLimitViolation->setUnit('/ 100 km');
        $em->persist($decelerationLimitViolation);
        $consumption->addConsumptionDetail($decelerationLimitViolation);

        // starkes Beschleunigen '/ 100 km'
        $accelerationLimitViolations = $consumptionframe["consumptionDetails"]["accelerationLimitViolations"];

        $accelerationLimitViolation = new \VehicleBundle\Entity\ConsumptionDetail();
        $accelerationLimitViolation->setConsumption($consumption);
        $accelerationLimitViolation->setLabel('starkes Beschleunigen');
        $accelerationLimitViolation->setValue(count($accelerationLimitViolations));// / $distance);
        $accelerationLimitViolation->setUnit('/ 100 km');
        $em->persist($accelerationLimitViolation);
        $consumption->addConsumptionDetail($accelerationLimitViolation);

        // Kraftstoffverbrauch 'l / 100 km'
        $gasoilConsumption = new \VehicleBundle\Entity\ConsumptionDetail();
        $gasoilConsumption->setConsumption($consumption);
        $gasoilConsumption->setLabel('Kraftstoffverbrauch');
        $gasoilConsumption->setValue($consumptionframe['gasoilConsumption'] / $distance);
        $gasoilConsumption->setUnit('l / 100 km');
        $em->persist($gasoilConsumption);
        $consumption->addConsumptionDetail($gasoilConsumption);*/

        // zu schnell '/ 100 km'

        $toFasts =  (isset($consumptionframe["consumptionDetails"]["toFasts"]))?$consumptionframe["consumptionDetails"]["toFasts"]:'0';

        $toFast = new \VehicleBundle\Entity\ConsumptionDetail();
        $toFast->setConsumption($consumption);
        $toFast->setLabel('zu schnell');
        $toFast->setValue(count($toFasts) / $distance);
        $toFast->setUnit('/ 100 km');
        $em->persist($toFast);
        $consumption->addConsumptionDetail($toFast);

        // zu schnell '/ 100 km'
       //$toFastdistances = $consumptionframe["consumptionDetails"]["toFastdistances"];
        $toFastdistances = (isset($consumptionframe["consumptionDetails"]["toFastdistances"]))?$consumptionframe["consumptionDetails"]["toFastdistances"]:'';
        $temp = [];
        $comments = [];
        $comment = [];
        $i = -1;

        if(is_array($toFastdistances)) {
            foreach($toFastdistances as $item) {
                if ((int)$item->getType() == 50) {
                    $i++;
                    $spdl = 0;
                    foreach($item->getTracedataproperties() as $property){
                        if($property->getPropertyKey()){
                            $spdl = $property->getPropertyValue();
                        }
                    }
                    $temp[$i] = ['startmeters'=>$item->getMileage(), 'endmeters'=>$item->getMileage(), 'curSpeed'=>$item->getSpeed(), 'maxSpeed'=>$spdl];
                } elseif ((int)$item->getType() == 65 and $i>0) {
                    $temp[$i]['endmeters'] = $item->getMileage();
                    $i++;
                    $temp[$i] = ['startmeters'=>$item->getMileage(), 'endmeters'=>$item->getMileage(), 'curSpeed'=>$item->getSpeed(), 'maxSpeed'=>$spdl];
                } elseif( $i>0) {
                    $temp[$i]['endmeters'] = $item->getMileage();
                }
            }
        }
        $meters = 0;
        usort($temp, function($a,$b){
            if($a['maxSpeed'] == $b['maxSpeed']){
                if($a['curSpeed'] == $b['curSpeed']) {
                    return 0;
                } else if($a['curSpeed'] > $b['curSpeed']) {
                    return -1;
                } else {
                    return 1;
                }
            } else if($a['maxSpeed'] > $b['maxSpeed']){
                return -1;
            } else {
                return 1;
            }
        });

        foreach($temp as $item) {
            if( $item['maxSpeed'] == 66 OR $item['maxSpeed'] == 65 OR $item['maxSpeed'] == 87 OR $item['maxSpeed'] == 85 )
            {
                $meters = $meters + (int)$item['endmeters'] - (int)$item['startmeters'];
                $commentKey = $item['curSpeed'].' km/h bei '.$item['maxSpeed'].' km/h';
                if(!array_key_exists($commentKey, $comments)){
                    $comments[$commentKey] = 0;
                }
                $comments[$commentKey] += (((int)$item['endmeters'] - (int)$item['startmeters'])/1000);
            }
        }

        foreach($comments as $key => $value){
            $comment[] = $key.': '.number_format($value, 3,',','.').' km';
        }

        $km = $meters / 1000;

        $toFastdistance = new \VehicleBundle\Entity\ConsumptionDetail();
        $toFastdistance->setConsumption($consumption);
        $toFastdistance->setLabel('zu schnell km');
        $toFastdistance->setValue($km / $distance);
        $toFastdistance->setUnit('km/100 km');
        $toFastdistance->setComment(implode("\n",$comment));
        $em->persist($toFastdistance);
        $consumption->addConsumptionDetail($toFastdistance);
        $em->persist($consumption);
        $em->flush();
    }

    private function extractConsumptionByLoginLogout($begin, $end, $consumptionframe) {
        $loginLogouts = $this->doctrine->getRepository('TrimbleSoapBundle:Tracedata')->findLoginLogoutMode($begin, $end, $consumptionframe['source']);
        $loginLogoutGrouped = [];
        $lastLoginLogout = null;

        foreach($loginLogouts as $loginLogout) {
            if(!array_key_exists($loginLogout["driver"], $loginLogoutGrouped)) {
                $loginLogoutGrouped[$loginLogout["driver"]] = [];
            }
            if($loginLogout["type"] == 1 && $loginLogout["propertyValue"] == 'D'){
                $lastLoginLogout = [
                    "driver" => $loginLogout["driver"],
                    "begin" => $loginLogout["mileage"],
                    "end" => $consumptionframe["endkm"],
                ];
            } elseif($loginLogout["type"] == 2 && !empty($lastLoginLogout) && $lastLoginLogout["driver"] == $loginLogout["driver"]){
                $loginLogoutGrouped[$loginLogout["driver"]][] = [
                    "begin" => $lastLoginLogout["begin"],
                    "end" => $loginLogout["mileage"],
                ];
            } elseif($loginLogout["type"] == 2 && empty($lastLoginLogout)){
                $loginLogoutGrouped[$loginLogout["driver"]][] = [
                    "begin" => $consumptionframe["startkm"],
                    "end" => $loginLogout["mileage"],
                ];
            }
        }

        unset($loginLogouts);

        foreach($loginLogoutGrouped as $driverframe => $loginLogouts){
            $driver = $this->doctrine->getRepository('EmployeeBundle:Employee')->findByTrimbleId($driverframe);
            if(!empty($driver)) {
                $consumptionframe['distance'] = 0;
                $consumptionframe['gasoilConsumption'] = 0;
                $consumptionframe["consumptionDetails"] = [];

                foreach($loginLogouts as $loginLogout) {
                    $temp_consumptionframe = $this->doctrine->getRepository('TrimbleSoapBundle:Tracedata')->findConsumptionMinMaxBySourceMileage($loginLogout["begin"], $loginLogout["end"], $consumptionframe['source']);
                    if(!empty($temp_consumptionframe)){
                        $consumptionframe['distance'] += (($temp_consumptionframe['endkm'] - $temp_consumptionframe['startkm']) / 1000);
                        $consumptionframe['gasoilConsumption'] += ($temp_consumptionframe['endconsumption'] - $temp_consumptionframe['startconsumption']);
                        $temp_consumptionDetails = $this->extractConsumptionDetails($consumptionframe['source'], $loginLogout["begin"], $loginLogout["end"]);

                        foreach($temp_consumptionDetails as $key => $value){
                            if(array_key_exists($key, $consumptionframe["consumptionDetails"])){
                                $consumptionframe["consumptionDetails"][$key] = array_merge($consumptionframe["consumptionDetails"][$key], $value);
                            } else {
                                $consumptionframe["consumptionDetails"][$key] = $value;
                            }
                        }
                    }
                }

                $this->insertConsumptionIntoDb($consumptionframe, $driver);
            }
        }
    }

    private function extractConsumptionDetails($source, $begin, $end){
        return [
            //'decelerationLimitViolations' => $this->doctrine->getRepository('TrimbleSoapBundle:Tracedata')->findAllWithTypeBySourceMileage($source, $begin, $end, [54]),
            //'accelerationLimitViolations' => $this->doctrine->getRepository('TrimbleSoapBundle:Tracedata')->findAllWithTypeBySourceMileage($source, $begin, $end, [53]),
            'toFasts' => $this->doctrine->getRepository('TrimbleSoapBundle:Tracedata')->findAllWithTypeBySourceMileage($source, $begin, $end, [50]),
            'toFastdistances' => $this->doctrine->getRepository('TrimbleSoapBundle:Tracedata')->findAllWithTypeBySourceMileage($source, $begin, $end, [50,65,60], 'mileage'),
        ];
    }

    public function extractConsumption($begin, $end, $fmsreport=[]) {
        $this->fmsreport = $fmsreport;
        $consumptionframes = $this->doctrine->getRepository('TrimbleSoapBundle:Tracedata')->findConsumptionMinMaxByDate($begin, $end);

        foreach($consumptionframes as $consumptionframe) {
            if (empty($consumptionframe['source'])) { // || (int)$consumptionframe['source'] != 604) {
                continue;
            }

            $consumptionframe['vehicle'] = $this->doctrine->getRepository('VehicleBundle:Vehicle')->findByTrimbleId($consumptionframe['source']);
            $consumptionframe['begin'] = $begin;
            $consumptionframe['end'] = $end;
            $consumptionframe['distance'] = ($consumptionframe['endkm'] - $consumptionframe['startkm']) / 1000;
            $consumptionframe['gasoilConsumption'] = $consumptionframe['endconsumption'] - $consumptionframe['startconsumption'];

            $drivers = explode(',', $consumptionframe['driver']);

            if (!empty($drivers) && count($drivers) > 1) {
                $this->extractConsumptionByLoginLogout($begin, $end, $consumptionframe);
            } elseif (!empty($drivers)) {
                foreach($drivers as $driverframe) {
                    $driver = $this->doctrine->getRepository('EmployeeBundle:Employee')->findByTrimbleId($driverframe);

                    if(!empty($driver)) {
                        $consumptionframe["consumptionDetails"] = $this->extractConsumptionDetails($consumptionframe['source'], $consumptionframe['startkm'], $consumptionframe['endkm']);
                        $this->insertConsumptionIntoDb($consumptionframe, $driver);
                    }
                }
            } else {
                $consumptionframe["consumptionDetails"] = $this->extractConsumptionDetails($consumptionframe['source'], $consumptionframe['startkm'], $consumptionframe['endkm']);
                $this->insertConsumptionIntoDb($consumptionframe);
            }
        }
    }
}
