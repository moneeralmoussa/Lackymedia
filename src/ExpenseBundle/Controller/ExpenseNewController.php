<?php

namespace ExpenseBundle\Controller;

use Carbon\Carbon;
use ExpenseBundle\Entity\Expense;
use TrimbleSoapBundle\Entity\Expenseview;
use ExpenseBundle\Entity\Expensefinanzamt;
use ExpenseBundle\Form\ExpenseType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExpenseNewController extends Controller
{  
    protected $countryspecificexpenses = null;
    public static $expensetest = null;
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
        foreach($employees as $employee)
        {
            $employee->spesens = $this->getDoctrine()->getRepository('ExpenseBundle:Expensefinanzamt')->findAllByEmployeeId($employee->getId(),$base_date->format('Y'));
        }
        return $this->render('ExpenseBundle:ExpenseNew:indexEmployee2020.html.twig', array(
            'employees' => $employees,
            'employees_deleted' => $employees_deleted,
            'base_date' => $base_date,
            'provenexpense' => $provenexpense
        ));
    }


   public function tracedataCopyByTypeLogout()
    {   // hasa al tabe3 be3mol na2l from datebase ma3 hazf al2eam 
        set_time_limit(0);
        ini_set('memory_limit','-1');
        
        $em = $this->getDoctrine()->getManager();
        // Select * from tracedate where type in (1,2)  // login // logout

        $lastExpenseview = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Expenseview')->findLastTracedataID(); 
        $traces = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findByTypeLoginLogout($lastExpenseview[1]);
        foreach ($traces as $trace)
        {   $expenseview = new Expenseview();
            $expenseview->setTracedataId($trace->getId());
            $expenseview->setType($trace->getType());
            $expenseview->setSource($trace->getSource());
            $expenseview->setTime($trace->getTime());
          if($trace->getLat() == NULL or $trace->getLon() == NULL or $trace->getMileage()== NULL)
            {
                $traceLocation = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findLocation($trace->getId(),$trace->getDid());
            if(!empty($traceLocation))
               {$expenseview->setLat($traceLocation->getLat());
                $expenseview->setLon($traceLocation->getLon());
                $expenseview->setMileage($traceLocation->getMileage());
               }
            else{
                $expenseview->setLat('51.91593');
                $expenseview->setLon('7.38457');
                $expenseview->setMileage('999999999');
                }
            }
          else
            {
                $expenseview->setLat($trace->getLat());
                $expenseview->setLon($trace->getLon());
                $expenseview->setMileage($trace->getMileage());
            }
            $expenseview->setDid($trace->getDid());
            $em->persist($expenseview);
            $em->flush();
        }
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
         //   $base_date->modify('- 1 month');
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
      
        return $this->render('ExpenseBundle:ExpenseNew:showEmployee2020.html.twig', array(
            'employee' => $employee,
            'employees' => $employees,
            'countries' => $countries,
            'base_date' => $base_date,
            'trucks' => $trucks,
            'provenexpense' => $provenexpense
        ));
    }

    public function loadWorkdaysByMonthGetNewAction(Request $request, $employee_id)
    {    $em = $this->getDoctrine()->getManager(); 
        $this->tracedataCopyByTypeLogout();
        $usr = $this->container->get('security.context')->getToken()->getUser();
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL')) {
            $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
            if ($employee->getId() != $usr->getEmployee()->getId()) {
                throw new AccessDeniedException();
            }
        }
        $date = (new \DateTime($request->get("start")))->format('Y-m-d');
        $AktuelleSpesensätze= (new \DateTime("2020-01-01"))->format('Y-m-d');
        
        $connection = $em->getConnection();
                    $statement = $connection->prepare("  
                    select * FROM expensefinanzamt WHERE employee_id ='".$employee_id."' AND date ='".$date."'");
                    $statement->execute();
                    $result=$statement->fetchAll();
                    if(empty($result))
                    { $archivs =$this->loadWorkdaysByMonthAction($employee_id, $date);
                    if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL'))
                        { 
                                //DELETE Expensefinanzamtprimary
                                $statement = $connection->prepare("  
                                DELETE FROM expensefinanzamtprimary WHERE date ='".$date."' AND employee_id='".$employee_id."' AND by_employee_id ='".$usr->getEmployee()->getId()."'");
                                $statement->execute();
                                // SaveNew
                                foreach ($archivs as $archiv)
                                {
                                    $stringVALUES  ="(". $employee_id.", '".$date."', '".$archiv['id']."','".$archiv['itemId']."','".$archiv['start']."', '".$archiv['end']."', '".$archiv['title']."','".$archiv['backgroundColor']."', '".$archiv['icon']."',  '".$archiv['startHome']."', '".$archiv['startPoint']['lat']."', '".$archiv['startPoint']['lng']."','".$archiv['startPoint']['town']."', '".$archiv['endHome']."', '".$archiv['endPoint']['lat']."', '".$archiv['endPoint']['lng']."', '".$archiv['endPoint']['town']."', '".$archiv['location_status']."', '".$archiv['source_status']."', '".$archiv['country_id']."', '".$archiv['country']."', '".$archiv['workingTime']."', '".$archiv['overTime']."', '".$archiv['absenceHome']."', '".$archiv['workdayBeginTime']->format('Y-m-d H:i:s')."', '".$archiv['workdayEndTime']->format('Y-m-d H:i:s')."', '".$archiv['reason']."', '".$archiv['comment']."', '".$archiv['logout']."', '".$archiv['login']."', '".$archiv['vehicle_id']."', '".$archiv['vehicle']."','".$usr->getEmployee()->getId()."')" ;
                                    $statement = $connection->prepare("  
                                    INSERT INTO expensefinanzamtprimary  (employee_id, date, idid,itemId,start,end,title,backgroundColor,icon,startHome,startPointlat, startPointlng, startPointtown, endHome,endPointlat, endPointlng,endPointtown, location_status, source_status,country_id, country, workingTime, overTime,absenceHome, workdayBeginTime, workdayEndTime,reason, comment, logout, login, vehicle_id, vehicle,by_employee_id) 
                                    VALUES ".$stringVALUES);
                                    $statement->execute();
                                }
                            } 
                        }
                   else{
                    $statement = $connection->prepare("  
                    select * from expensefinanzamtprimary WHERE employee_id='".$employee_id."' AND date ='".$date."' AND by_employee_id = '".$result[0]['status']."'");
                    $statement->execute();
                    $results=$statement->fetchAll();
                    if(empty($results))return  new JsonResponse(null);
                    foreach($results  as $result ) 
                    {
                        $archivs[] = array(
                            'id'               => $result['idid'],
                            'itemId'           => $result['itemId'],
                            'start'            => $result['start'],
                            'end'              => $result['end'],
                            'title'            => $result['title'],
                            'backgroundColor'  => $result['backgroundColor'],
                            'icon'             => $result['icon'],
                            'startHome'        => (int)$result['startHome'],
                            'startPoint' => [
                                'lat' => (int)$result['startPointlat'],
                                'lng' => (int)$result['startPointlng'],
                                'town' => $result['startPointtown'],
                            ],
                            'endHome'          => (int)$result['endHome'],
                            'endPoint' => [
                                'lat' => (int)$result['endPointlat'],
                                'lng' => (int)$result['endPointlng'],
                                'town' => $result['startPointtown'],
                            ],
                            'location_status' => $result['location_status'],
                            'source_status' => $result['source_status'],
                            'country_id' => (int)$result['country_id'],
                            'country' => $result['country'],
                            'workingTime' => (int)$result['workingTime'],
                            'overTime' => (int)$result['overTime'],
                            'absenceHome' => (int)$result['absenceHome'],
                            'workdayBeginTime' => (new \Datetime($result['workdayBeginTime'])),
                            'workdayEndTime' => (new \Datetime($result['workdayEndTime'])),
                            'reason' => $result['reason'],
                            'comment' => $result['comment'],
                            'logout' => $result['logout'],
                            'login' => $result['login'],
                            'vehicle_id' => (int)$result['vehicle_id'],
                            'vehicle' => $result['vehicle'],
                    );
                    }
                   }                          
        if($date >= $AktuelleSpesensätze) 
        {return  new JsonResponse($archivs);}
        return  new JsonResponse(null);
    }

    public function loadWorkdaysByMonthAction($employee_id, $date)
    {
        $expenseWorkdayService = $this->container->get('app.expense_workday_service', $this->getDoctrine());
        $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
        $schooldays_temp = $this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getSchooldaysByEmployeeDate($employee_id, (new \DateTime($date))->modify('midnight -1 day')->format('Y-m-d'), (new \DateTime($date))->modify('midnight +1 month +1 day')->format('Y-m-d'));
        $holidays_temp = $this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getHolidaysByEmployeeDate($employee_id, (new \DateTime($date))->modify('midnight -1 day')->format('Y-m-d'), (new \DateTime($date))->modify('midnight +1 month +1 day')->format('Y-m-d'));

        if (!is_null($employee->getContract())) {
            $weeklyHoursOfWork = $employee->getContract()->getWeeklyHoursOfWork();
        } else {
            $weeklyHoursOfWork = $employee->getContract();
        }
        // Workdays
        $workdays = $this->getWorkdaysByEmployeeMonth($employee_id, $date);
        // Traces
        $workdays = $this->getTracesByEmployeeMonth($employee, $date, $workdays);
        // checkBeginEndHome
        $traces =$workdays[1];
       /* foreach ($traces as $trace)
        { dump($trace);
        }
        die();
        $trace = $this->tracecheckBeginEndHome($employee, $expenseWorkdayService, $traces, true);
        dump($tracess);
        */
        $workdays = $workdays[0];

        $workdays = $this->checkBeginEndHome($employee, $expenseWorkdayService, $workdays, true);
        //dump($workdays['2018-10-31']);
        $cars_temp = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->findByMonth($employee_id, $date, (new \DateTime($date))->modify('midnight +1 month')->format('Y-m-d'));
        $cars_temp2 = array();
   
        foreach ($cars_temp as $car) {
            if ($car->getReason()->getId() == 1) {
                $date_temp = "car_".$car->getVehicleLogBeginTime()->format('Y-m-d H:i:s');
                $date_temp2 = $car->getVehicleLogBeginTime()->format('Y-m-d');

                $workdays[$date_temp] = array(
                    'id' => "car_".$car->getId(),
                    'date' => $date_temp2,
                    'workdayBeginTime' => $car->getVehicleLogBeginTime(),
                    'workdayBeginLat' => $car->getVehicleLogBeginPosition()->getLat(),
                    'workdayBeginLon' => $car->getVehicleLogBeginPosition()->getLon(),
                    'workdayEndTime' => $car->getVehicleLogBeginTime(),
                    'workdayEndLat' => $car->getVehicleLogBeginPosition()->getLat(),
                    'workdayEndLon' => $car->getVehicleLogBeginPosition()->getLon(),
                    'workingTime' => 0,
                    'overTime' => 0,
                    'country_id' => 1,
                    'country' => 'DE',
                    'vehicle' => $car->getVehicle(),
                    'location_status' => 'USECAR',
                    'source_status' => 'USECAR',
                    'reason' => $car->getExpensereason(),
                    'comment' => '',
                    'logout' => '',
                    'login' => '',
                );
                if (!empty($car->getVehicleLogEndTime())) {
                    $workdays[$date_temp]['workdayEndTime'] = $car->getVehicleLogEndTime();
                    $workdays[$date_temp]['workdayEndLat'] = $car->getVehicleLogEndPosition()->getLat();
                    $workdays[$date_temp]['workdayEndLon'] = $car->getVehicleLogEndPosition()->getLon();
                }
                $startsAtHome = $expenseWorkdayService->startsAtHome($employee, $workdays[$date_temp]);
                $startsAtCompany = $expenseWorkdayService->startsAtCompany($workdays[$date_temp]);
                $workdays[$date_temp]['workdayBeginHome'] = ($startsAtHome||$startsAtCompany)?true:false;

                $finishesAtHome = $expenseWorkdayService->finishesAtHome($employee, $workdays[$date_temp]);
                $finishesAtCompany = $expenseWorkdayService->finishesAtCompany($workdays[$date_temp]);
                $workdays[$date_temp]['workdayEndHome'] = ($finishesAtHome||$finishesAtCompany)?true:false;

                $workdays[$date_temp]['workdayBeginAddress'] = $expenseWorkdayService->reverseGeocoderOSM($workdays[$date_temp]['workdayBeginLat'], $workdays[$date_temp]['workdayBeginLon']);
                $workdays[$date_temp]['workdayEndAddress'] = $expenseWorkdayService->reverseGeocoderOSM($workdays[$date_temp]['workdayEndLat'], $workdays[$date_temp]['workdayEndLon']);

                if (!array_key_exists($date_temp2, $cars_temp2)) {
                    $cars_temp2[$date_temp2] = array($workdays[$date_temp]);
                } else {
                    $cars_temp2[$date_temp2][] = $workdays[$date_temp];
                }
            }
        }
        foreach ($cars_temp2 as $date_temp => $lCar) {
            if (array_key_exists($date_temp, $workdays)) {
                foreach ($lCar as $car) {
                    if ($car['workdayBeginTime'] < $workdays[$date_temp]['workdayBeginTime']) {
                        $workdays[$date_temp]['workdayBeginHome'] = ($workdays[$date_temp]['workdayBeginHome'] || (empty($workdays[$date_temp]['savedOnce']) && ($car['workdayBeginHome']?true:false))); //True)); //($car['workdayBeginHome']?True:False)));
                        $workdays[$date_temp]['workingTime'] += round(($workdays[$date_temp]['workdayBeginTime']->getTimestamp() - $car['workdayBeginTime']->getTimestamp()) / 3600, 2);
                    }
                    if ($car['workdayEndTime'] > $workdays[$date_temp]['workdayEndTime']) {
                        $workdays[$date_temp]['workdayEndHome'] = ($workdays[$date_temp]['workdayEndHome'] || (empty($workdays[$date_temp]['savedOnce']) && ($car['workdayEndHome']?true:false))); //True)); //($car['workdayEndHome']?True:False)));
                        $workdays[$date_temp]['workingTime'] += round(($car['workdayEndTime']->getTimestamp() - $workdays[$date_temp]['workdayEndTime']->getTimestamp()) / 3600, 2);
                    }
                }
                if (($workdays[$date_temp]['workdayBeginHome']) && ($workdays[$date_temp]['workdayEndHome'])) {
                    $workdays[$date_temp]['location_status'] = 'SLEEPSATHOME';
                } elseif ($workdays[$date_temp]['workdayBeginHome']) {
                    $workdays[$date_temp]['location_status'] = 'STARTSATHOME';
                } elseif ($workdays[$date_temp]['workdayEndHome']) {
                    $workdays[$date_temp]['location_status'] = 'FINISHESATHOME';
                } else {
                    $workdays[$date_temp]['location_status'] = 'SLEEPSINTRUCK';
                }
            }
        }
        //dump($workdays['2018-10-31']);
        //die;
        // checkAbsence
        $checkedAbsence = $this->checkAbsence($employee, $date, $weeklyHoursOfWork, $workdays, $schooldays_temp, $holidays_temp, [], true);
        $workdays = $checkedAbsence[0];
        $nonworkingdays = $checkedAbsence[1];
        // checkLocationstatus
        $workdays = $this->checkLocationstatus($employee, $employee_id, $expenseWorkdayService, $workdays, $nonworkingdays, $cars_temp2, true);
       
        usort($workdays, function ($a, $b) {
            if ($a['date'] == $b['date']) {
                return 0;
            }
            return ($a['date'] < $b['date']) ? -1 : 1;
        });

        $locationStatus = [
            "SUCCEEDED" => "#AAD0CD",
            "FAILED" => "#CC5750",
            "RUNNING" => "#8DB87C",
            "KILLED" => "#E4DBBF",
            "SLEEPSATHOME" => "#8DB87C",
            "STARTSATHOME" => "#AAD0CD",
            "FINISHESATHOME" => "#E4DBBF",
            "SLEEPSINTRUCK" => "#CC5750",
            "MANUAL" => "check-circle-o",
            "AUTOMATIC" => "",
            "USECAR" => "#747081",
            "SCHOOL" => "#95a5a6",
            "SCHOOLANDDRIVE" => "#f39c12",
            "in Bearbeitung" => "#A46A1F",
            "abgelehnt" => "#A46A1F",
        ];
        $sourceStatus = [
            "MANUAL" => "check-circle-o",
            "AUTOMATIC" => "",
            "USECAR" => "",
            "in Bearbeitung" => "",
            "abgelehnt" => "",
        ];

        $arrayCollection = array();

        foreach ($workdays as $key => $item) {
            $temp_arr = array(
                    'id'               => 'workdays_'.$item['id'],
                    'itemId'           => (string)$item['id'],
                    'start'            => $item['workdayBeginTime']->format('Y-m-d H:i:s'),
                    'end'              => $item['workdayEndTime']->format('Y-m-d H:i:s'),
                    'title'            => ((isset($item['source_status'])&&in_array($item['source_status'], array("in Bearbeitung","abgelehnt")))?'('.$item['source_status'].') ':'').$item['workdayBeginAddress'].($item['workdayEndAddress']!=''?' - '.$item['workdayEndAddress']:''),
                    'backgroundColor'  => (isset($item['source_status'])&&in_array($item['source_status'], array("in Bearbeitung","abgelehnt")))?$locationStatus[$item['source_status']]:(isset($item['location_status'])?$locationStatus[$item['location_status']]:'#ff0000'),
                    'icon'             => isset($item['source_status'])?$sourceStatus[$item['source_status']]:'',
                    'startHome'        => $item['workdayBeginHome']?1:0,
                    'startPoint' => [
                        'lat' => (float)$item['workdayBeginLat'],
                        'lng' => (float)$item['workdayBeginLon'],
                    ],

                    'endHome'          => $item['workdayEndHome']?1:0,
                    'endPoint' => [
                        'lat' => (float)$item['workdayEndLat'],
                        'lng' => (float)$item['workdayEndLon'],
                    ],
                    'location_status' => $item['location_status'],
                    'source_status' => $item['source_status'],
                    'country_id' => $item['country_id'],
                    'country' => $item['country'],
                    'workingTime' => $item['workingTime'],
                    'overTime' => !empty($item['overTime'])?$item['overTime']:0,
                    'absenceHome' => !empty($item['absenceHome'])?$item['absenceHome']:0,
                    'workdayBeginTime' => $item['workdayBeginTime'],
                    'workdayEndTime' => $item['workdayEndTime'],
                    'reason' => !empty($item['reason'])?$item['reason']:'',
                    'comment' => $item['comment'],
                    'logout' => $item['logout'],
                    'login' => $item['login'],
            );
        

            if (isset($item['workdayBeginAddress'])) {
                $temp_arr['startPoint']['town'] = $item['workdayBeginAddress'];
            }
            if (isset($item['workdayEndAddress'])) {
                $temp_arr['endPoint']['town'] = $item['workdayEndAddress'];
            }
            if (isset($item['vehicle'])) {
                $truck = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findOneById($item['vehicle']->getId());
                if (isset($truck)){
                 $temp_arr['vehicle_id'] = $item['vehicle']->getId();
                 $temp_arr['vehicle'] = (string)$item['vehicle'];
                 $temp_arr['title'] = $temp_arr['title'] . ' (' .  $temp_arr['vehicle'] . ')';
                 }
                else {
                    $temp_arr['vehicle'] =  $item['vehicle']->getId();
                    $temp_arr['vehicle_id'] = '0000000';
                    $temp_arr['title'] = 'Vehicle nicht gefunden' . $item['vehicle']->getId();
                }
            }
            $arrayCollection[] = $temp_arr;
        }
        return $arrayCollection;
    }

    private function getWorkdaysByEmployeeMonth($employee_id, $date, $workdays=[], $approvedOnly=false)
    {
        $workdays_temp = $this->getDoctrine()->getRepository('ExpenseBundle:Workday')->findByMonth($employee_id, (new \DateTime($date))->modify('midnight -1 day')->format('Y-m-d'), (new \DateTime($date))->modify('midnight +1 month +1 day')->format('Y-m-d'));

        foreach ($workdays_temp as $workday) {
            $status_temp = !is_null($workday->getStatus())&&!is_null($workday->getStatus()->getId())?$workday->getStatus()->getId():1;
            if ($approvedOnly && $status_temp>1) {
                continue;
            }
            $date_temp = $workday->getDate()->format('Y-m-d').($status_temp>2?'_'.  strtolower(str_replace(' ', '-', $workday->getStatus())):'');
            $workdays[$date_temp] = array(
                'id' => $workday->getId().($status_temp>1?'_'.  strtolower(str_replace(' ', '-', $workday->getStatus())):''),
                'date' => $workday->getDate(),
                'workdayBeginHome' => $workday->getWorkdayBeginHome(),
                'workdayBeginAddress' => null,
                'workdayBeginTime' => $workday->getWorkdayBeginTime(),
                'workdayBeginLat' => $workday->getWorkdayBeginLat(),
                'workdayBeginLon' => $workday->getWorkdayBeginLon(),
                'workdayEndHome' => $workday->getWorkdayEndHome(),
                'workdayEndAddress' => null,
                'workdayEndTime' => $workday->getWorkdayEndTime(),
                'workdayEndLat' => $workday->getWorkdayEndLat(),
                'workdayEndLon' => $workday->getWorkdayEndLon(),
                'vehicle' => $workday->getTruck(),
                'country_id' => ($workday->getCountry()?$workday->getCountry()->getId():1),
                'country' => ($workday->getCountry()?$workday->getCountry()->getCountryISO():'DE'),
                'source_status' => $status_temp>1?(string)$workday->getStatus():'MANUAL',
                'savedOnce' => true,
                'comment' => $workday->getComment(),
                'login' => '',
                'logout' => '',
            );
        }

        unset($workdays_temp);


        return $workdays;
    }

    private function getTracesByEmployeeMonth($employee, $date, $workdays=[])
    {  $ALLTrace = array();
        $traces = array();
/*        $traces_temp_Logout = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Expenseview')->findByMonth($employee->getTrimbleIds(), (new \DateTime($date))->modify('midnight')->format('Y-m-d'), (new \DateTime($date))->modify('+1 month first day of this month')->format('Y-m-d'));
        foreach ($traces_temp_Logout as $trace) {
            $traceBegin = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Expenseview')->findBegin($trace->getId(),$trace->getDid());
            $date_temp_login = $traceBegin->getTime()->format('Y-m-d');
            if (!array_key_exists($date_temp_login, $workdays))
                {  $check1= clone ($traceBegin->getTime());
                   $check2 =clone ($trace->getTime());
                   if($check1->format('N') == $check2->format('N'))
                    {
                    $ALLTrace[]=array(
                    'id' => 'trace_'.$trace->getId(),
                    'date' => new \DateTime($date_temp_login),
                    'workdayBeginHome' => null,
                    'workdayBeginAddress' => null,
                    'workdayBeginLat' => $traceBegin->getLat(),
                    'workdayBeginLon' => $traceBegin->getLon(),
                    'workdayBeginTime' => $traceBegin->getTime(),
                    'workdayEndHome' => null,
                    'workdayEndAddress' => null,
                    'workdayEndLat' => $trace->getLat(),
                    'workdayEndLon' => $trace->getLon(),
                    'workdayEndTime' => $trace->getTime(),
                    'vehicle' => $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findOneBy(array("trimbleId"=>$trace->getSource())),
                    'country_id' => 1,
                    'country' => 'DE',
                    'source_status' => 'AUTOMATIC',
                    'comment' => '',
                    'logout' => 'Yes',
                    'login' => 'Yes',
                        );}
                        else{
                            $endTime= clone ($traceBegin->getTime());
                            $ALLTrace[]=array(
                                'id' => 'trace_'.$trace->getId(),
                                'date' => new \DateTime($date_temp_login),
                                'workdayBeginHome' => null,
                                'workdayBeginAddress' => null,
                                'workdayBeginLat' => $traceBegin->getLat(),
                                'workdayBeginLon' => $traceBegin->getLon(),
                                'workdayBeginTime' => $traceBegin->getTime(),
                                'workdayEndHome' => null,
                                'workdayEndAddress' => null,
                                'workdayEndLat' => $trace->getLat(),
                                'workdayEndLon' => $trace->getLon(),
                                'workdayEndTime' => $endTime->modify('midnight +1 days -1 minutes'),
                                'vehicle' => $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findOneBy(array("trimbleId"=>$trace->getSource())),
                                'country_id' => 1,
                                'country' => 'DE',
                                'source_status' => 'AUTOMATIC',
                                'comment' => '',
                                'logout' => 'Yes',
                                'login' => 'Yes',
                            );
                              $beginTime= clone ($trace->getTime());
                              $ALLTrace[]=array(
                                        'id' => 'trace_'.$trace->getId().'_0',
                                        'date' => new \DateTime($date_temp_login),
                                        'workdayBeginHome' => null,
                                        'workdayBeginAddress' => null,
                                        'workdayBeginLat' => $traceBegin->getLat(),
                                        'workdayBeginLon' => $traceBegin->getLon(),
                                        'workdayBeginTime' => $beginTime->modify('midnight +1 minutes'),
                                        'workdayEndHome' => null,
                                        'workdayEndAddress' => null,
                                        'workdayEndLat' => $trace->getLat(),
                                        'workdayEndLon' => $trace->getLon(),
                                        'workdayEndTime' => $trace->getTime(),
                                        'vehicle' => $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findOneBy(array("trimbleId"=>$trace->getSource())),
                                        'country_id' => 1,
                                        'country' => 'DE',
                                        'source_status' => 'AUTOMATIC',
                                        'comment' => '',
                                        'logout' => 'Yes',
                                        'login' => 'Yes',
                                            );          
                            }
                        
                    if(round(($trace->getTime()->getTimestamp() - $traceBegin->getTime()->getTimestamp()) / 3600 ,2 )>1)        
                    { if (!array_key_exists($date_temp_login, $traces)){
                        $traces[$date_temp_login] = array(
                            'id' => 'trace_'.$trace->getId(),
                            'date' => new \DateTime($date_temp_login),
                            'workdayBeginHome' => null,
                            'workdayBeginAddress' => null,
                            'workdayBeginLat' => $traceBegin->getLat(),
                            'workdayBeginLon' => $traceBegin->getLon(),
                            'workdayBeginTime' => $traceBegin->getTime(),
                            'workdayEndHome' => null,
                            'workdayEndAddress' => null,
                            'workdayEndLat' => $trace->getLat(),
                            'workdayEndLon' => $trace->getLon(),
                            'workdayEndTime' => $trace->getTime(),
                            'vehicle' => $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findOneBy(array("trimbleId"=>$trace->getSource())),
                            'country_id' => 1,
                            'country' => 'DE',
                            'source_status' => 'AUTOMATIC',
                            'comment' => '',
                            'logout' => 'Yes',
                            'login' => 'Yes',
                        );
                    }else{
                        $traces[$date_temp_login] = array(
                            'id' => $traces[$date_temp_login]["id"],
                            'date' => $traces[$date_temp_login]["date"],
                            'workdayBeginHome' => $traces[$date_temp_login]["workdayBeginHome"],
                            'workdayBeginAddress' => null,
                            'workdayBeginLat' => $traces[$date_temp_login]["workdayBeginLat"],
                            'workdayBeginLon' => $traces[$date_temp_login]["workdayBeginLon"],
                            'workdayBeginTime' => $traces[$date_temp_login]["workdayBeginTime"],
                            'workdayEndHome' => null,
                            'workdayEndAddress' => null,
                            'workdayEndLat' => $trace->getLat(),
                            'workdayEndLon' => $trace->getLon(),
                            'workdayEndTime' => $trace->getTime(),
                            'vehicle' => $traces[$date_temp_login]["vehicle"],
                            'country_id' => 1,
                            'country' => 'DE',
                            'source_status' => 'AUTOMATIC',
                            'comment' => '',
                            'logout' => 'Yes',
                            'login' => 'Yes',
                        );
                     }
                  }
                }
        }
//check trace login logout > 25_H //
foreach ($traces as $date_temp_login_Key => $check )
        if( round(($check['workdayEndTime']->getTimestamp() - $check['workdayBeginTime']->getTimestamp()) / 3600, 2) > 27)
        {  
            $traces_temp = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findByMonth($employee->getTrimbleIds(), ($check['workdayBeginTime'])->modify('-1 day')->format('Y-m-d'), ($check['workdayEndTime'])->modify('+1 day')->format('Y-m-d'));
            ($check['workdayBeginTime'])->modify('+1 day')->format('Y-m-d');
            ($check['workdayEndTime'])->modify('-1 day')->format('Y-m-d');
            unset($traces[$date_temp_login_Key]);
            $driver_ignore_types = array('9', '55', '82', '87', '300', '301', '302');
            foreach ($traces_temp as $trace) {
                if (!\is_null($trace->getLat()) && !\is_null($trace->getLon())) {
                    $date_temp = $trace->getTime()->format('Y-m-d');
                    if (!array_key_exists($date_temp, $workdays) && !in_array($trace->getType(), $driver_ignore_types) && !array_key_exists($date_temp, $traces)) {
                        $traces[$date_temp] = array(
                            'id' => 'trace_'.$trace->getId(),
                            'date' => new \DateTime($date_temp),
                            'workdayBeginHome' => null,
                            'workdayBeginAddress' => null,
                            'workdayBeginLat' => $trace->getLat(),
                            'workdayBeginLon' => $trace->getLon(),
                            'workdayBeginTime' => $trace->getTime(),
                            'workdayEndHome' => null,
                            'workdayEndAddress' => null,
                            'workdayEndLat' => $trace->getLat(),
                            'workdayEndLon' => $trace->getLon(),
                            'workdayEndTime' => $trace->getTime(),
                            'vehicle' => $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findOneBy(array("trimbleId"=>$trace->getSource())),
                            'country_id' => 1,
                            'country' => 'DE',
                            'source_status' => 'AUTOMATIC',
                            'comment' => '',
                            'logout' => ($check['workdayEndTime']->format('Y-m-d') == $date_temp)?'Yes':'No' ,
                            'login'=> ($check['workdayBeginTime']->format('Y-m-d') == $date_temp)?'Yes':'No' ,
                        );
                    } elseif (!array_key_exists($date_temp, $workdays) && !in_array($trace->getType(), $driver_ignore_types) && array_key_exists($date_temp, $traces)) {
                        $traces[$date_temp]['workdayEndTime'] = $trace->getTime();
                        $traces[$date_temp]['workdayEndLat'] = $trace->getLat();
                        $traces[$date_temp]['workdayEndLon'] = $trace->getLon();
                    }
                }
            }
        }
        //end add trace > 25 stunde did =1550
        // FUnction (end Month without Logout)
        if(empty($date_temp_login_Key))$date_temp_login_Key=$date;
        if (  $date_temp_login_Key <  (new \DateTime($date_temp_login_Key))->modify('last day of this month')->format('Y-m-d') )
        {       $login = 'Yes';
                $traces_temp = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findByMonth($employee->getTrimbleIds(), (new \DateTime($date_temp_login_Key))->modify('+1 day')->format('Y-m-d'), (new \DateTime($date_temp_login_Key))->modify('last day of this month')->modify('+1 day')->format('Y-m-d'));
                $driver_ignore_types = array('9', '55', '82', '87', '300', '301', '302');
                foreach ($traces_temp as $trace) {
                    if (!\is_null($trace->getLat()) && !\is_null($trace->getLon())) {
                        $date_temp = $trace->getTime()->format('Y-m-d');
                        
                        if (!array_key_exists($date_temp, $workdays) && !in_array($trace->getType(), $driver_ignore_types) && !array_key_exists($date_temp, $traces)) {
                            $traces[$date_temp] = array(
                                'id' => 'trace_'.$trace->getId(),
                                'date' => new \DateTime($date_temp),
                                'workdayBeginHome' => null,
                                'workdayBeginAddress' => null,
                                'workdayBeginLat' => $trace->getLat(),
                                'workdayBeginLon' => $trace->getLon(),
                                'workdayBeginTime' => $trace->getTime(),
                                'workdayEndHome' => null,
                                'workdayEndAddress' => null,
                                'workdayEndLat' => $trace->getLat(),
                                'workdayEndLon' => $trace->getLon(),
                                'workdayEndTime' => $trace->getTime(),
                                'vehicle' => $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findOneBy(array("trimbleId"=>$trace->getSource())),
                                'country_id' => 1,
                                'country' => 'DE',
                                'source_status' => 'AUTOMATIC',
                                'comment' => '',
                                'logout' => 'No',
                                'login'=> $login ,
                            );
                            $login = 'No';
                        } elseif (!array_key_exists($date_temp, $workdays) && !in_array($trace->getType(), $driver_ignore_types) && array_key_exists($date_temp, $traces)) {
                            $traces[$date_temp]['workdayEndTime'] = $trace->getTime();
                            $traces[$date_temp]['workdayEndLat'] = $trace->getLat();
                            $traces[$date_temp]['workdayEndLon'] = $trace->getLon();
                        }
                    }
                }
            }
        //ERROR LoginLogaut status => RUF FAHRER AN
        
        $nonworkingdays = array();
        $day =  (new \DateTime($date));
        $endmonth =  ((new \DateTime($date))->modify('last day of this month'));
        while( $day->format('Y-m-d') <= $endmonth->format('Y-m-d') )
        { 
            if($day->format('N') < 6 ){
                if (!array_key_exists($day->format('Y-m-d'), $traces)) {
                    $tomorrow = clone( $day );$tomorrow->modify('+1 day');
                    $traces_temp = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findByMonth($employee->getTrimbleIds(), $day->format('Y-m-d'), $tomorrow->format('Y-m-d'));
                $driver_ignore_types = array('9', '55', '82', '87', '300', '301', '302');
                foreach ($traces_temp as $trace) {
                    if (!\is_null($trace->getLat()) && !\is_null($trace->getLon())) {
                        $date_temp = $trace->getTime()->format('Y-m-d');
                        
                        if (!array_key_exists($date_temp, $workdays) && !in_array($trace->getType(), $driver_ignore_types) && !array_key_exists($date_temp, $traces)) {
                            $traces[$date_temp] = array(
                                'id' => 'trace_'.$trace->getId(),
                                'date' => new \DateTime($date_temp),
                                'workdayBeginHome' => null,
                                'workdayBeginAddress' => null,
                                'workdayBeginLat' => $trace->getLat(),
                                'workdayBeginLon' => $trace->getLon(),
                                'workdayBeginTime' => $trace->getTime(),
                                'workdayEndHome' => null,
                                'workdayEndAddress' => null,
                                'workdayEndLat' => $trace->getLat(),
                                'workdayEndLon' => $trace->getLon(),
                                'workdayEndTime' => $trace->getTime(),
                                'vehicle' => $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findOneBy(array("trimbleId"=>$trace->getSource())),
                                'country_id' => 1,
                                'country' => 'DE',
                                'source_status' => 'AUTOMATIC',
                                'comment' => '',
                                'logout' => 'NON',
                                'login'=> 'NON' ,
                            );
                            $login = 'No';
                        } elseif (!array_key_exists($date_temp, $workdays) && !in_array($trace->getType(), $driver_ignore_types) && array_key_exists($date_temp, $traces)) {
                            $traces[$date_temp]['workdayEndTime'] = $trace->getTime();
                            $traces[$date_temp]['workdayEndLat'] = $trace->getLat();
                            $traces[$date_temp]['workdayEndLon'] = $trace->getLon();
                        }
                    }
                }
              } 
            }
          $day->modify('+1 day');
        }


*/





        $traces_temp = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findByMonth($employee->getTrimbleIds(), (new \DateTime($date))->modify('midnight -1 day')->format('Y-m-d'), (new \DateTime($date))->modify('midnight +1 month +1 day')->format('Y-m-d'));

        $driver_ignore_types = array('9', '55', '82', '87', '300', '301', '302');
        $traces = array();
        foreach ($traces_temp as $trace) {
            if (!\is_null($trace->getLat()) && !\is_null($trace->getLon())) {
                $date_temp = $trace->getTime()->format('Y-m-d');
                if (!array_key_exists($date_temp, $workdays) && !in_array($trace->getType(), $driver_ignore_types) && !array_key_exists($date_temp, $traces)) {
                    $traces[$date_temp] = array(
                        'id' => 'trace_'.$trace->getId(),
                        'date' => new \DateTime($date_temp),
                        'workdayBeginHome' => null,
                        'workdayBeginAddress' => null,
                        'workdayBeginLat' => $trace->getLat(),
                        'workdayBeginLon' => $trace->getLon(),
                        'workdayBeginTime' => $trace->getTime(),
                        'workdayEndHome' => null,
                        'workdayEndAddress' => null,
                        'workdayEndLat' => $trace->getLat(),
                        'workdayEndLon' => $trace->getLon(),
                        'workdayEndTime' => $trace->getTime(),
                        'vehicle' => $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findOneBy(array("trimbleId"=>$trace->getSource())),
                        'country_id' => 1,
                        'country' => 'DE',
                        'source_status' => 'AUTOMATIC',
                        'comment' => '',
                        'login' => empty($this->getDoctrine()->getRepository('TrimbleSoapBundle:Expenseview')->findByType($employee->getTrimbleIds(),$trace->getTime()->format('Y-m-d') , '1'))?'No':'Yes',
                        'logout' => empty($this->getDoctrine()->getRepository('TrimbleSoapBundle:Expenseview')->findByType($employee->getTrimbleIds(),$trace->getTime()->format('Y-m-d') , '2'))?'No':'Yes',
                    );

                } elseif (!array_key_exists($date_temp, $workdays) && !in_array($trace->getType(), $driver_ignore_types) && array_key_exists($date_temp, $traces)) {
                    $traces[$date_temp]['workdayEndTime'] = $trace->getTime();
                    $traces[$date_temp]['workdayEndLat'] = $trace->getLat();
                    $traces[$date_temp]['workdayEndLon'] = $trace->getLon();
                }
            }
        }
        //while()
        //End
        //29115128    
        unset($traces_temp);
        foreach ($traces as $key => $trace) {
            $workdays[$key] = $trace;
        }
        
        return [$workdays,$ALLTrace];
    }

    private function tracecheckBeginEndHome($employee, $expenseWorkdayService, $traces=[], $needGeocoding=false)
    {
        foreach ($traces as $date_temp => $workday) {
            $startsAtHome = $expenseWorkdayService->startsAtHome($employee, $traces[$date_temp], $traces[$date_temp]['workdayBeginHome']);
            $finishesAtHome = $expenseWorkdayService->finishesAtHome($employee, $traces[$date_temp], $traces[$date_temp]['workdayEndHome']);
            $startsAtCompany = $expenseWorkdayService->startsAtCompany($traces[$date_temp]);
            $finishesAtCompany = $expenseWorkdayService->finishesAtCompany($traces[$date_temp]);
            if ($needGeocoding) {
                $traces[$date_temp]['workdayBeginAddress'] = $expenseWorkdayService->reverseGeocoderOSM($workday['workdayBeginLat'], $workday['workdayBeginLon']);
                $traces[$date_temp]['workdayEndAddress'] = $expenseWorkdayService->reverseGeocoderOSM($workday['workdayEndLat'], $workday['workdayEndLon']);
            }

            $workdays[$date_temp]['workingTime'] = round(($workday['workdayEndTime']->getTimestamp() - $workday['workdayBeginTime']->getTimestamp()) / 3600, 2);

            $key_datepart_temp = explode('_', $date_temp);
            $key_datepart = $key_datepart_temp[0];
            $date =(new \DateTime($key_datepart))->format('N');
         
            if ((int)(new \DateTime($key_datepart))->format('N') >= 6) {
                $workdays[$date_temp]['overTime'] = $workdays[$date_temp]['workingTime'];
            }
            if (!empty($this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->checkshoppingvalueBeginAtHome($employee->getId(), $workdays[$date_temp]['workdayBeginTime']->format('Y-m-d H:i:s') ,$workdays[$date_temp]['workdayBeginTime']->format('Y-m-d') )) || $workdays[$date_temp]['workdayBeginHome'] || (empty($workdays[$date_temp]['savedOnce']) && ($startsAtHome || $startsAtCompany))) {
                $workdays[$date_temp]['workdayBeginHome'] = true;
            } else {
                $workdays[$date_temp]['workdayBeginHome'] = false;
            }
           if (!empty($this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->checkshoppingvalueEndzuHome($employee->getId(), $workdays[$date_temp]['workdayBeginTime']->format('Y-m-d H:i:s') ,$workdays[$date_temp]['workdayBeginTime']->format('Y-m-d') )) || $workdays[$date_temp]['workdayEndHome'] || (empty($workdays[$date_temp]['savedOnce']) && ($finishesAtHome || $finishesAtCompany))) {
                $workdays[$date_temp]['workdayEndHome'] = true;
            } else {
                $workdays[$date_temp]['workdayEndHome'] = false;
            }
            if ($workdays[$date_temp]['workdayBeginHome'] && $workdays[$date_temp]['workdayEndHome']) {
                $workdays[$date_temp]['location_status'] = 'SLEEPSATHOME';
            } elseif ($workdays[$date_temp]['workdayBeginHome']) {
                $workdays[$date_temp]['location_status'] = 'STARTSATHOME';
            } elseif ($workdays[$date_temp]['workdayEndHome']) {
                $workdays[$date_temp]['location_status'] = 'FINISHESATHOME';
            } else {
                $workdays[$date_temp]['location_status'] = 'SLEEPSINTRUCK';
            }
        }
      return $workdays;
    }

    private function checkBeginEndHome($employee, $expenseWorkdayService, $workdays=[], $needGeocoding=false)
    {
        foreach ($workdays as $date_temp => $workday) {
            $startsAtHome = $expenseWorkdayService->startsAtHome($employee, $workdays[$date_temp], $workdays[$date_temp]['workdayBeginHome']);
            $finishesAtHome = $expenseWorkdayService->finishesAtHome($employee, $workdays[$date_temp], $workdays[$date_temp]['workdayEndHome']);
            $startsAtCompany = $expenseWorkdayService->startsAtCompany($workdays[$date_temp]);
            $finishesAtCompany = $expenseWorkdayService->finishesAtCompany($workdays[$date_temp]);
            if ($needGeocoding) {
                $workdays[$date_temp]['workdayBeginAddress'] = $expenseWorkdayService->reverseGeocoderOSM($workday['workdayBeginLat'], $workday['workdayBeginLon']);
                $workdays[$date_temp]['workdayEndAddress'] = $expenseWorkdayService->reverseGeocoderOSM($workday['workdayEndLat'], $workday['workdayEndLon']);
            }
            $workdays[$date_temp]['workingTime'] = round(($workday['workdayEndTime']->getTimestamp() - $workday['workdayBeginTime']->getTimestamp()) / 3600, 2);
            $key_datepart_temp = explode('_', $date_temp);
            $key_datepart = $key_datepart_temp[0];
            $date =(new \DateTime($key_datepart))->format('N');
            if ((int)(new \DateTime($key_datepart))->format('N') >= 6) {
                $workdays[$date_temp]['overTime'] = $workdays[$date_temp]['workingTime'];
            }
            if (!empty($this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->checkshoppingvalueBeginAtHome($employee->getId(), $workdays[$date_temp]['workdayBeginTime']->format('Y-m-d H:i:s') ,$workdays[$date_temp]['workdayBeginTime']->format('Y-m-d') )) || $workdays[$date_temp]['workdayBeginHome'] || (empty($workdays[$date_temp]['savedOnce']) && ($startsAtHome || $startsAtCompany))) {
                $workdays[$date_temp]['workdayBeginHome'] = true;
            } else {
                $workdays[$date_temp]['workdayBeginHome'] = false;
            }
           if (!empty($this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->checkshoppingvalueEndzuHome($employee->getId(), $workdays[$date_temp]['workdayBeginTime']->format('Y-m-d H:i:s') ,$workdays[$date_temp]['workdayBeginTime']->format('Y-m-d') )) || $workdays[$date_temp]['workdayEndHome'] || (empty($workdays[$date_temp]['savedOnce']) && ($finishesAtHome || $finishesAtCompany))) {
                $workdays[$date_temp]['workdayEndHome'] = true;
            } else {
                $workdays[$date_temp]['workdayEndHome'] = false;
            }
            if ($workdays[$date_temp]['workdayBeginHome'] && $workdays[$date_temp]['workdayEndHome']) {
                $workdays[$date_temp]['location_status'] = 'SLEEPSATHOME';
            } elseif ($workdays[$date_temp]['workdayBeginHome']) {
                $workdays[$date_temp]['location_status'] = 'STARTSATHOME';
            } elseif ($workdays[$date_temp]['workdayEndHome']) {
                $workdays[$date_temp]['location_status'] = 'FINISHESATHOME';
            } else {
                $workdays[$date_temp]['location_status'] = 'SLEEPSINTRUCK';
            }
        }
        return $workdays;
    }
    private function checkAbsence($employee, $date, $weeklyHoursOfWork, $workdays=[], $schooldays_temp=[], $holidays_temp=[], $illness_temp=[], $needSchooldays=false, $expenses=false)
    {
        $nonworkingdays = array();

        $date_begin_carbon = new Carbon($date);
        $date_temp = new \DateTime($date);
        $date_begin = clone $date_temp;
        $date_begin->modify('midnight -1 day');
        $date_begin_str = $date_begin->format('Y-m-d');
        $date_end = clone $date_temp;
        $date_end->modify('midnight +1 month');
        $date_end_str = $date_end->format('Y-m-d');
        $date_temp->modify('midnight -1 day');
       
        while ($date_temp < $date_end) {
            $date_temp_str = $date_temp->format('Y-m-d');

            foreach ($schooldays_temp as $schoolday) {
                if ($schoolday->getFromDate() <= $date_temp && $date_temp < $schoolday->getToDate() && $date_temp->format('N') < 6) {
                    $date_temp_str_school = $date_temp_str;
                    if (array_key_exists($date_temp_str, $workdays)) {
                        $workdays[$date_temp_str]['workingTime'] += 8;
                    }
                    if ($needSchooldays) {
                        $date_temp_str_school = 'school_'.$date_temp_str;
                        $school_begin_temp = clone $date_temp;
                        $school_end_temp = clone $date_temp;
                        $workdays[$date_temp_str_school] = [
                            'id' => $date_temp_str_school,
                            'date' => $date_temp,
                            'workdayBeginHome' => true,
                            'workdayBeginAddress' => 'Schule',
                            'workdayBeginLat' => $employee->getLat(),
                            'workdayBeginLon' => $employee->getLon(),
                            'workdayBeginTime' => $school_begin_temp->modify('+8 hours'),
                            'workdayEndHome' => true,
                            'workdayEndAddress' => '',
                            'workdayEndLat' => $employee->getLat(),
                            'workdayEndLon' => $employee->getLon(),
                            'workdayEndTime' => $school_end_temp->modify('+16 hours'),
                            'workingTime' => 8,
                            'overTime' => 0,
                            'country_id' => 1,
                            'country' => 'DE',
                            'source_status' => 'AUTOMATIC',
                            'location_status' => 'SCHOOL',
                            'comment' => '',
                        ];
                    }
                }
            }
            foreach ($holidays_temp as $holiday) {
                if ($expenses && $holiday->getFromDate() <= $date_temp && $date_temp <= $holiday->getToDate() && $date_temp->format('N') < 6 && empty($this->getDoctrine()->getRepository('AbsenceBundle:PublicHoliday')->isPublicHoliday($date_temp)) && $date_temp > $date_begin) {
                    //$expenses['holidays']++;
                    if ((int)$holiday->getReason()->getId() == 2) {
                        $expenses['holidays'] = $this->getDoctrine()->getRepository('AbsenceBundle:AbsenceDetailClearing')->findSumAllAbsenceDetailClearingsByStartAndEndUseAsHoliday($employee, $date_begin_carbon->startOfMonth(), $date_begin_carbon->copy()->endOfMonth());
                    // $expenses['holidays'] = $this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getSumHolidaysByEmployeeDate($employee, $date_begin_carbon->startOfMonth(), $date_begin_carbon->copy()->endOfMonth());
                        // $absenceService = $this->container->get('app.absence_service', $this->getDoctrine());
                        // $expenses['holidays'] = $absenceService->getUsedAbsenceMonthly($date, $employee);
                    } elseif (!isset($expenses['holidays_3']) && (int)$holiday->getReason()->getId() == 3) {
                        $expenses['holidays_3'] = 1;
                    } elseif ((int)$holiday->getReason()->getId() == 3) {
                        $expenses['holidays_3']++;
                    } elseif (!isset($expenses['holidays_4']) && (int)$holiday->getReason()->getId() == 4) {
                        $expenses['holidays_4'] = 1;
                    } elseif ((int)$holiday->getReason()->getId() == 4) {
                        $expenses['holidays_4']++;
                    }
                }
                if ($holiday->getFromDate() <= $date_temp && $date_temp <= $holiday->getToDate() && $date_temp->format('N') < 6) {
                    $nonworkingdays[] = $date_temp_str;
                }
            }
            foreach ($illness_temp as $illness) {
                if ($expenses && $illness->getFromDate() <= $date_temp && $date_temp <= $illness->getToDate() && $date_temp > $date_begin) {
                    $expenses['illness']['ill']++;
                    if (!isset($expenses['illness']['till']) || $illness->getFromDate() <= $expenses['illness']['till']) {
                        $expenses['illness']['till'] = $illness->getFromDate();
                    }
                    if ((int)$illness->getReason()->getId() == 9) {
                        $expenses['illness']['benefit']++;
                    }
                    if (((int)$illness->getReason()->getId() == 9) && (!isset($expenses['illness']['benefittill']) || $illness->getFromDate() <= $expenses['illness']['benefittill'])) {
                        $expenses['illness']['benefittill'] = $illness->getFromDate();
                    }
                }
            }
            if (array_key_exists($date_temp_str, $nonworkingdays)) {
            } elseif (!array_key_exists($date_temp_str, $workdays)) {
                $nonworkingdays[] = $date_temp_str;
            } elseif (!is_null($weeklyHoursOfWork) && $workdays[$date_temp_str]['location_status'] != 'SCHOOL' && $date_temp->format('N') < 6) {
                //$workdays[$date_temp_str]['overTime'] = $workdays[$date_temp_str]['workingTime'] - ($weeklyHoursOfWork / 5);
                $workdays[$date_temp_str]['overTime']=0;
               
            } elseif (!is_null($weeklyHoursOfWork) && $workdays[$date_temp_str]['location_status'] != 'SCHOOL') {
                $workdays[$date_temp_str]['overTime'] = $workdays[$date_temp_str]['workingTime'];
            } else {
                $workdays[$date_temp_str]['overTime'] = !empty($workdays[$date_temp_str]['overTime'])?$workdays[$date_temp_str]['overTime']:0;
            }

            $date_temp = clone $date_temp;
            $date_temp->modify('midnight +1 day');
        }

        if (isset($expenses['illness']['till'])) {
            $expenses['illness']['till'] = $expenses['illness']['till']->format('d.m.Y');
        }
        if (isset($expenses['illness']['benefittill'])) {
            $expenses['illness']['benefittill'] = $expenses['illness']['benefittill']->format('d.m.Y');
        }

        if (array_key_exists($date_begin_str, $workdays)) {
            unset($workdays[$date_begin_str]);
        }
        if (array_key_exists($date_end_str, $workdays)) {
            unset($workdays[$date_end_str]);
        }
        return [$workdays,$nonworkingdays,$expenses];
    }


    private function checkLocationstatus($employee, $employee_id, $expenseWorkdayService, $workdays=[], $nonworkingdays=[], $cars_temp2=[], $updateCountry=false)
    {
        foreach ($workdays as $key => $value) {
            if ($updateCountry && in_array($employee_id, array(17, 12)) && $workdays[$key]['country_id'] == 1) {
                $finishingCountryIso = $expenseWorkdayService->getCountryIso($value['workdayEndLat'], $value['workdayEndLon']);
                $temp_finishingCountryExpenses = $this->getExpensesByCountrycode($finishingCountryIso);
                $workdays[$key]['country_id'] = $temp_finishingCountryExpenses['country_id'];
                $workdays[$key]['country'] = $finishingCountryIso;
            }

            $key_datepart_temp = explode('_', $key);
            
            $key_datepart = $key_datepart_temp[0];
            if ($workdays[$key]['location_status'] == 'SLEEPSINTRUCK') {
                $date_temp = new \DateTime($key_datepart);
                $yesterday = clone $date_temp;
                $yesterday->modify('-1 day');
                $yesterday = $yesterday->format('Y-m-d');
                $tomorrow = clone $date_temp;
                $tomorrow->modify('+1 day');
                $tomorrow = $tomorrow->format('Y-m-d');

                if (in_array($yesterday, $nonworkingdays) && in_array($tomorrow, $nonworkingdays) && empty($workdays[$key]['savedOnce'])) {
                    $workdays[$key]['location_status'] = 'SLEEPSATHOME';
                    $workdays[$key]['workdayBeginHome'] = true;
                    $workdays[$key]['workdayEndHome'] = true;
                } elseif (in_array($yesterday, $nonworkingdays) && empty($workdays[$key]['savedOnce'])) {
                    $workdays[$key]['location_status'] = 'STARTSATHOME';
                    $workdays[$key]['workdayBeginHome'] = true;
                } elseif (in_array($tomorrow, $nonworkingdays) && empty($workdays[$key]['savedOnce'])) {
                    $workdays[$key]['location_status'] = 'FINISHESATHOME';
                    $workdays[$key]['workdayEndHome'] = true;
                }
            } elseif ($workdays[$key]['location_status'] == 'STARTSATHOME') {
                $date_temp = new \DateTime($key_datepart);
                $tomorrow = clone $date_temp;
                $tomorrow->modify('+1 day');
                $tomorrow = $tomorrow->format('Y-m-d');

                if (in_array($tomorrow, $nonworkingdays) && empty($workdays[$key]['savedOnce'])) {
                    $workdays[$key]['location_status'] = 'SLEEPSATHOME';
                    $workdays[$key]['workdayEndHome'] = true;
                }
            } elseif ($workdays[$key]['location_status'] == 'FINISHESATHOME') {
                $date_temp = new \DateTime($key_datepart);
                $yesterday = clone $date_temp;
                $yesterday->modify('-1 day');
                $yesterday = $yesterday->format('Y-m-d');

                if (in_array($yesterday, $nonworkingdays) && empty($workdays[$key]['savedOnce'])) {
                    $workdays[$key]['location_status'] = 'SLEEPSATHOME';
                    $workdays[$key]['workdayBeginHome'] = true;
                }
            }

            if ($workdays[$key]['location_status'] == 'SLEEPSINTRUCK') {
                $workdays[$key]['absenceHome'] = 24;
            } elseif ($workdays[$key]['location_status'] == 'STARTSATHOME') {
                $date_temp = new \DateTime($key_datepart);
                $tomorrow = clone $date_temp;
                $tomorrow->modify('+1 day');
                $startsAtHome = $expenseWorkdayService->startsAtHome($employee, $workdays[$key]);
                $workdays[$key]['absenceHome'] = round(($tomorrow->getTimestamp() - $workdays[$key]['workdayBeginTime']->getTimestamp()) / 3600, 2);
                if (in_array($key, $cars_temp2) && $cars_temp2[$key]['workdayBeginTime'] < $workdays[$key]['workdayBeginTime']) {
                    $workdays[$key]['absenceHome'] = round(($tomorrow->getTimestamp() - $cars_temp2[$key]['workdayBeginTime']->getTimestamp()) / 3600, 2);
                } elseif ((!in_array($key, $cars_temp2) || $cars_temp2[$key]['workdayBeginTime'] >= $workdays[$key]['workdayBeginTime']) && !$startsAtHome) {
                    $workdays[$key]['absenceHome'] += $employee->getUsualHomeTravelHours();
                }
            } elseif ($workdays[$key]['location_status'] == 'FINISHESATHOME') {
                $date_temp = new \DateTime($key_datepart);
                $finishesAtHome = $expenseWorkdayService->finishesAtHome($employee, $workdays[$key]);
                $workdays[$key]['absenceHome'] = round(($workdays[$key]['workdayEndTime']->getTimestamp() - $date_temp->getTimestamp()) / 3600, 2);
                if (in_array($key, $cars_temp2) && $cars_temp2[$key]['workdayEndTime'] > $workdays[$key]['workdayEndTime']) {
                    $workdays[$key]['absenceHome'] = round(($cars_temp2[$key]['workdayEndTime']->getTimestamp() - $date_temp->getTimestamp()) / 3600, 2);
                } elseif ((!in_array($key, $cars_temp2) || $cars_temp2[$key]['workdayEndTime'] <= $workdays[$key]['workdayEndTime']) && !$finishesAtHome) {
                    $workdays[$key]['absenceHome'] += $employee->getUsualHomeTravelHours();
                }
            } else {
                $startsAtHome = $expenseWorkdayService->startsAtHome($employee, $workdays[$key]);
                $finishesAtHome = $expenseWorkdayService->finishesAtHome($employee, $workdays[$key]);

                $workdays[$key]['absenceHome'] = round(($workdays[$key]['workdayEndTime']->getTimestamp() - $workdays[$key]['workdayBeginTime']->getTimestamp()) / 3600, 2);
                if (in_array($key, $cars_temp2) && $cars_temp2[$key]['workdayBeginTime'] < $workdays[$key]['workdayBeginTime']) {
                    $workdays[$key]['absenceHome'] = round(($workdays[$key]['workdayBeginTime']->getTimestamp() - $cars_temp2[$key]['workdayBeginTime']->getTimestamp()) / 3600, 2);
                } elseif ((!in_array($key, $cars_temp2) || $cars_temp2[$key]['workdayBeginTime'] >= $workdays[$key]['workdayBeginTime']) && !$startsAtHome) {
                    $workdays[$key]['absenceHome'] += $employee->getUsualHomeTravelHours();
                }
                if (in_array($key, $cars_temp2) && $cars_temp2[$key]['workdayEndTime'] > $workdays[$key]['workdayEndTime']) {
                    $workdays[$key]['absenceHome'] = round(($cars_temp2[$key]['workdayEndTime']->getTimestamp() - $workdays[$key]['workdayEndTime']->getTimestamp()) / 3600, 2);
                } elseif ((!in_array($key, $cars_temp2) || $cars_temp2[$key]['workdayEndTime'] <= $workdays[$key]['workdayEndTime']) && !$finishesAtHome) {
                    $workdays[$key]['absenceHome'] += $employee->getUsualHomeTravelHours();
                }
            }
            /*if ($workdays[$key]['absenceHome'] == 24) {
                $workdays[$key]['absenceHome'] = 24;
                $workdays[$key]['location_status'] = 'SLEEPSINTRUCK';
            }*/
            if(($workdays[$key]['location_status'] != 'SLEEPSINTRUCK' && ($workdays[$key]['absenceHome'] - $workdays[$key]['workingTime']) >3) or $workdays[$key]['absenceHome'] <= $workdays[$key]['workingTime'] or $workdays[$key]['workingTime']>25 )
            {
                if($workdays[$key]['workingTime']>25)
                {
                 $workdays[$key]['workingTime'] = round(($workdays[$key]['workdayEndTime']->getTimestamp() - $workdays[$key]['workdayBeginTime']->getTimestamp()) / 3600, 2);
                }
                $workdays[$key]['absenceHome'] = $workdays[$key]['workingTime'] + $employee->getUsualHomeTravelHours();
            }
        }

        return $workdays;
    }

    function deleteWorkdayAction($id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL')) {
            throw new AccessDeniedException();
        }
        $expense = $this->getDoctrine()->getRepository('ExpenseBundle:Workday')->find($id);
        if(!$expense) {
            return new JsonResponse(false);
        }
         $em = $this->getDoctrine()->getManager();
         $em->remove($expense);
         $em->flush();
         return new JsonResponse(true);
    }

    function editcarreasonAction($id,$reason)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL')) {
            throw new AccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $VehicleLog = $em->getRepository('VehicleLogBundle:VehicleLog')->find($id);
        if(!$VehicleLog) {
            return new JsonResponse(false);
        }
        if($reason == 1 || $reason == 2 ){
        $VehicleLog->setExpensereason($reason);
        $em->persist($VehicleLog);
        $em->flush();}
        return new JsonResponse(true);
    }
    private function getExpensesByCountrycode($expenseCountrycode)
    {
        if (is_null($this->countryspecificexpenses)) {
            $countryspecificexpenses = $this->getDoctrine()->getRepository('ExpenseBundle:Countryspecificexpenses')->findAll();
            $this->countryspecificexpenses = array();
            foreach ($countryspecificexpenses as $countryspecificexpense) {
                $this->countryspecificexpenses[$countryspecificexpense->getCountryISO()] = array(
                    'expenses8h' => $countryspecificexpense->getExpenses8h(),
                    'expenses24h' => $countryspecificexpense->getExpenses24h(),
                    'country_id' => $countryspecificexpense->getId(),
                );
            }
        }

        if ($expenseCountrycode && array_key_exists($expenseCountrycode, $this->countryspecificexpenses)) {
            return $this->countryspecificexpenses[$expenseCountrycode];
        }

        return $this->countryspecificexpenses["DE"];
    }
}
