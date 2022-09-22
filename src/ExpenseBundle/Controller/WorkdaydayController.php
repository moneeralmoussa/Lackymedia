<?php

namespace ExpenseBundle\Controller;

use Carbon\Carbon;
use ExpenseBundle\Entity\Workday;
use ExpenseBundle\Entity\Provenexpense;
use EmployeeBundle\Entity\Employee;
//use ExpenseBundle\Form\WorkdayType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WorkdaydayController extends Controller
{
    protected $countryspecificexpenses = null;
    public static $expensetest = null;

    private function getExpensesByCountrycode($expenseCountrycode) {
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

        if ($expenseCountrycode && array_key_exists($expenseCountrycode,$this->countryspecificexpenses)) {
            return $this->countryspecificexpenses[$expenseCountrycode];
        }

        return $this->countryspecificexpenses["DE"];
    }

    public function submitExpenseAction(Employee $employee, Request $request)
    {
      $em = $this->getDoctrine()->getRepository('ExpenseBundle:Provenexpense');
      $current = new Carbon($request->get("current"));
      // $current->subMonth();
      $monthstart = new Carbon($current->startOfMonth());
      $monthend = new Carbon($current->endOfMonth());

      $provenexpense = $em->findOneBy(array(
        'startdate' => $monthstart,
        'enddate' => $monthend,
        'employee' => $employee
      ));

      if($provenexpense){
        return new JsonResponse(array(
          'success' => false,
          'data'    => $provenexpense
        ));
      }

      $provenexpense = new Provenexpense();
      $provenexpense->setStartdate($monthstart); //Monatsanfang
      $provenexpense->setEnddate($monthend);  //Monatsende +1
      $provenexpense->setEmployee($employee); //Mitarbeiter
      $provenexpense->setProoveTime(Carbon::now());


      $em = $this->getDoctrine()->getManager();
      $em->persist($provenexpense);
      $em->flush();

      return new JsonResponse(array(
        'success' => true,
        'data'    => $provenexpense
      ));
    }

    public function checkExpenseAction(Employee $employee, Request $request)
    {

      $em = $this->getDoctrine()->getRepository('ExpenseBundle:Provenexpense');
      $current = new Carbon($request->get("current"));
      $monthstart = new Carbon($current->startOfMonth());
      $monthend = new Carbon($current->endOfMonth());

      $provenexpense = $em->findOneBy(array(
        'startdate' => $monthstart,
        'enddate' => $monthend,
        'employee' => $employee
      ));

      if($provenexpense){
        return new JsonResponse(array(
          'success' => false,
          'data'    => $provenexpense
        ));
      }

      return new JsonResponse(array(
        'success' => true,
        'data'    => $provenexpense
      ));
    }

    public function createSubmitAction(Request $request) {
        $usr = $this->container->get('security.context')->getToken()->getUser();

        $truck = $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->find($request->get("truck"));
        $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($request->get("employee"));
        $country_id = $request->get("country_id");
        if (is_null($country_id) || $country_id == 0) {
            $country_id = 1;
        }
        $country = $this->getDoctrine()->getRepository('ExpenseBundle:Countryspecificexpenses')->find($country_id);

        $status_id = $request->get("status")?$request->get("status"):1;
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL')){
            if ($employee->getId() != $usr->getEmployee()->getId()) {
                throw new AccessDeniedException();
            } else {
                $status_id = 3;
            }
        }
        $status = $this->getDoctrine()->getRepository('AbsenceBundle:Status')->find($status_id);

        $tmp_date = new \DateTime($request->get("date"));

        $em = $this->getDoctrine()->getManager();

        $workday = $this->getDoctrine()->getRepository('ExpenseBundle:Workday')->findOneBy(array("employee"=>$employee,"date"=>$tmp_date));
        if (is_null($workday)) {
            $workday = new Workday();
            $workday->setDate($tmp_date);
            $workday->setEmployee($employee);
        }
        $workday->setTruck($truck);
        $workday->setCountry($country);
        $workday->setStatus($status);
        $workday->setComment($request->get("comment"));
        $workday->setWorkdayBeginTime(Carbon::parse($request->get("startTime")));
        $workday->setWorkdayBeginLat($request->get("startPoint")["lat"]);
        $workday->setWorkdayBeginLon($request->get("startPoint")["lng"]);
        $workday->setWorkdayEndTime(Carbon::parse($request->get("endTime")));
        $workday->setWorkdayEndLat($request->get("endPoint")["lat"]);
        $workday->setWorkdayEndLon($request->get("endPoint")["lng"]);

        $startHome = $request->get("startHome");
        if (!is_null($startHome)) {
            $workday->setWorkdayBeginHome($startHome);
        }
        $endHome = $request->get("endHome");
        if (!is_null($endHome)) {
            $workday->setWorkdayEndHome($endHome);
        }

        $em->persist($workday);
        $em->flush();


        return new Response('Saved new workday with id '.$workday->getId());

        /*return $this->render('ExpenseBundle:Expense:create.html.twig', array(
            'form' => $form->createView()
        ));*/
    }

    public function loadWorkdayAction($employee_id, $date)
    {
        $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
        $workdays = $this->getDoctrine()->getRepository('ExpenseBundle:Workday')->findBy(array("employee"=>$employee, "date"=>new \DateTime($date)));

        return $this->render('ExpenseBundle:Workday:loadWorkdays.html.twig', array(
            'employee' => $employee,
            'workdays' => $workdays,
            'base_date' => $date,
        ));
    }

    public function loadWorkdaysByDayGetAction(Request $request, $employee_id)
    {
        $usr = $this->container->get('security.context')->getToken()->getUser();
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL')){
            $employee = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->find($employee_id);
            if ($employee->getId() != $usr->getEmployee()->getId()) {
                throw new AccessDeniedException();
            }
        }
        $date = (new \DateTime($request->get("start")))->format('Y-m-d');
        return $this->loadWorkdaysByDayAction($employee_id, $date);
    }

    private function getWorkdaysByEmployeeDay($employee_id, $date, $workdays=[], $approvedOnly=false)
    {
        $workdays_temp = $this->getDoctrine()->getRepository('ExpenseBundle:Workday')->findByMonth($employee_id, (new \DateTime($date))->modify('midnight -1 day')->format('Y-m-d'), (new \DateTime($date))->modify('midnight +1 day')->format('Y-m-d'));

        foreach ($workdays_temp as $workday) {
            $status_temp = !is_null($workday->getStatus())&&!is_null($workday->getStatus()->getId())?$workday->getStatus()->getId():1;
            if ($approvedOnly && $status_temp>1) {
                continue;
            }
            $date_temp = $workday->getDate()->format('Y-m-d').($status_temp>2?'_'.  strtolower(str_replace(' ','-',$workday->getStatus())):'');
            $workdays[$date_temp] = array(
                'id' => $workday->getId().($status_temp>1?'_'.  strtolower(str_replace(' ','-',$workday->getStatus())):''),
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
            );
        }

        unset($workdays_temp);

        return $workdays;
    }

    private function getTracesByEmployeeDay($employee, $date, $workdays=[])
    {
        $traces_temp = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findByMonth($employee->getTrimbleIds(), (new \DateTime($date))->modify('midnight -1 day')->format('Y-m-d'), (new \DateTime($date))->modify('midnight +1 day')->format('Y-m-d'));

        $driver_ignore_types = array('9', '55', '82', '87', '300', '301', '302');
        $traces = array();

        foreach ($traces_temp as $trace) {
            if (!\is_null($trace->getLat()) && !\is_null($trace->getLon())) {
                $date_temp = $trace->getTime()->format('Y-m-d');
                if (!array_key_exists($date_temp, $workdays) && !in_array($trace->getType(), $driver_ignore_types) && !array_key_exists($date_temp, $traces)){
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
                    );
                } else if (!array_key_exists($date_temp, $workdays) && !in_array($trace->getType(), $driver_ignore_types) && array_key_exists($date_temp, $traces)) {
                    $traces[$date_temp]['workdayEndTime'] = $trace->getTime();
                    $traces[$date_temp]['workdayEndLat'] = $trace->getLat();
                    $traces[$date_temp]['workdayEndLon'] = $trace->getLon();
                }
            }
        }

        unset($traces_temp);

        foreach ($traces as $key => $trace) {
            $workdays[$key] = $trace;
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
            if($needGeocoding) {
                $workdays[$date_temp]['workdayBeginAddress'] = $expenseWorkdayService->reverseGeocoderOSM($workday['workdayBeginLat'], $workday['workdayBeginLon']);
                $workdays[$date_temp]['workdayEndAddress'] = $expenseWorkdayService->reverseGeocoderOSM($workday['workdayEndLat'], $workday['workdayEndLon']);
            }

            $workdays[$date_temp]['workingTime'] = round(($workday['workdayEndTime']->getTimestamp() - $workday['workdayBeginTime']->getTimestamp()) / 3600, 2);

            $key_datepart_temp = explode('_', $date_temp);
            $key_datepart = $key_datepart_temp[0];

            if((int)(new \DateTime($key_datepart))->format('N') >= 6) {
                $workdays[$date_temp]['overTime'] = $workdays[$date_temp]['workingTime'];
            }

            if ($workdays[$date_temp]['workdayBeginHome'] || (empty($workdays[$date_temp]['savedOnce']) && ($startsAtHome || $startsAtCompany))) {
                $workdays[$date_temp]['workdayBeginHome'] = True;
            } else {
                $workdays[$date_temp]['workdayBeginHome'] = False;
            }
            if ($workdays[$date_temp]['workdayEndHome'] || (empty($workdays[$date_temp]['savedOnce']) && ($finishesAtHome || $finishesAtCompany))) {
                $workdays[$date_temp]['workdayEndHome'] = True;
            } else {
                $workdays[$date_temp]['workdayEndHome'] = False;
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
        $date_end->modify('midnight +1 day');
        $date_end_str = $date_end->format('Y-m-d');
        $date_temp->modify('midnight -1 day');

        while ($date_temp < $date_end) {
            $date_temp_str = $date_temp->format('Y-m-d');

            foreach ($schooldays_temp as $schoolday) {
                if($schoolday->getFromDate() <= $date_temp && $date_temp < $schoolday->getToDate() && $date_temp->format('N') < 6) {
                    $date_temp_str_school = $date_temp_str;
                    if (array_key_exists($date_temp_str, $workdays)) {
                        $workdays[$date_temp_str]['workingTime'] += 8;
                    }
                    if($needSchooldays) {
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
                if($holiday->getFromDate() <= $date_temp && $date_temp <= $holiday->getToDate() && $date_temp->format('N') < 6) {
                    $nonworkingdays[] = $date_temp_str;
                    $workdays[$date_temp_str] = "holiday";
                }
            }
            foreach ($illness_temp as $illness) {
                if($illness->getFromDate() <= $date_temp && $date_temp <= $illness->getToDate() && $date_temp > $date_begin) {
                    $workdays[$date_temp_str] = "ill";
                }
            }

            if (array_key_exists($date_temp_str, $nonworkingdays)) {
            } elseif (!array_key_exists($date_temp_str, $workdays)) {
                $nonworkingdays[] = $date_temp_str;
            }

            $date_temp = clone $date_temp;
            $date_temp->modify('midnight +1 day');
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
                $finishingCountryIso = $expenseWorkdayService->getCountryIso($value['workdayEndLat'],$value['workdayEndLon']);
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
                if(in_array($key, $cars_temp2) && $cars_temp2[$key]['workdayBeginTime'] < $workdays[$key]['workdayBeginTime']) {
                    $workdays[$key]['absenceHome'] = round(($tomorrow->getTimestamp() - $cars_temp2[$key]['workdayBeginTime']->getTimestamp()) / 3600, 2);
                } elseif((!in_array($key, $cars_temp2) || $cars_temp2[$key]['workdayBeginTime'] >= $workdays[$key]['workdayBeginTime']) && !$startsAtHome) {
                    $workdays[$key]['absenceHome'] += $employee->getUsualHomeTravelHours();
                }
            } elseif ($workdays[$key]['location_status'] == 'FINISHESATHOME') {
                $date_temp = new \DateTime($key_datepart);
                $finishesAtHome = $expenseWorkdayService->finishesAtHome($employee, $workdays[$key]);

                $workdays[$key]['absenceHome'] = round(($workdays[$key]['workdayEndTime']->getTimestamp() - $date_temp->getTimestamp()) / 3600, 2);
                if(in_array($key, $cars_temp2) && $cars_temp2[$key]['workdayEndTime'] > $workdays[$key]['workdayEndTime']) {
                    $workdays[$key]['absenceHome'] = round(($cars_temp2[$key]['workdayEndTime']->getTimestamp() - $date_temp->getTimestamp()) / 3600, 2);
                } elseif((!in_array($key, $cars_temp2) || $cars_temp2[$key]['workdayEndTime'] <= $workdays[$key]['workdayEndTime']) && !$finishesAtHome) {
                    $workdays[$key]['absenceHome'] += $employee->getUsualHomeTravelHours();
                }
            } else {
                $startsAtHome = $expenseWorkdayService->startsAtHome($employee, $workdays[$key]);
                $finishesAtHome = $expenseWorkdayService->finishesAtHome($employee, $workdays[$key]);

                $workdays[$key]['absenceHome'] = round(($workdays[$key]['workdayEndTime']->getTimestamp() - $workdays[$key]['workdayBeginTime']->getTimestamp()) / 3600, 2);
                if(in_array($key, $cars_temp2) && $cars_temp2[$key]['workdayBeginTime'] < $workdays[$key]['workdayBeginTime']) {
                    $workdays[$key]['absenceHome'] = round(($workdays[$key]['workdayBeginTime']->getTimestamp() - $cars_temp2[$key]['workdayBeginTime']->getTimestamp()) / 3600, 2);
                } elseif((!in_array($key, $cars_temp2) || $cars_temp2[$key]['workdayBeginTime'] >= $workdays[$key]['workdayBeginTime']) && !$startsAtHome) {
                    $workdays[$key]['absenceHome'] += $employee->getUsualHomeTravelHours();
                }
                if(in_array($key, $cars_temp2) && $cars_temp2[$key]['workdayEndTime'] > $workdays[$key]['workdayEndTime']) {
                    $workdays[$key]['absenceHome'] = round(($cars_temp2[$key]['workdayEndTime']->getTimestamp() - $workdays[$key]['workdayEndTime']->getTimestamp()) / 3600, 2);
                } elseif((!in_array($key, $cars_temp2) || $cars_temp2[$key]['workdayEndTime'] <= $workdays[$key]['workdayEndTime']) && !$finishesAtHome) {
                    $workdays[$key]['absenceHome'] += $employee->getUsualHomeTravelHours();
                }
            }
            if ($workdays[$key]['absenceHome'] >= 24) {
                $workdays[$key]['absenceHome'] = 24;
                $workdays[$key]['location_status'] = 'SLEEPSINTRUCK';
            }
        }

        return $workdays;
    }

    private function calcExpenses($employee_id, $expenseWorkdayService, $workdays=[], $expenses=[], $additionalExpenses8h=0, $additionalExpenses24h=0)
    {
        foreach ($workdays as $date_temp => $workday) {
            if (in_array($employee_id, array(17, 12)) && $workday['country_id'] == 1) {
                $finishingCountryIso = $expenseWorkdayService->getCountryIso($workday['workdayEndLat'],$workday['workdayEndLon']);
                $finishingCountryExpenses = $this->getExpensesByCountrycode($finishingCountryIso);
            } else {
                $finishingCountryExpenses = $this->getExpensesByCountrycode($workday['country']);
            }
            $expenses['overTime'] += $workday['overTime'];
            $expenses['sumOvertimeBenefit'.($expenses['overtimeBenefit'][$workday['workdayBeginTime']->format('N')]['brutto']?'Brutto':'Netto')] += ($expenses['overtimeBenefit'][$workday['workdayBeginTime']->format('N')]['value'] * $workday['overTime']);

            if ($workdays[$date_temp]['location_status'] == 'SLEEPSATHOME') {
                if ($workday['absenceHome'] > 8) {
                    $expenses['free'] += $finishingCountryExpenses['expenses8h'];
                    $expenses['extra'] += $additionalExpenses8h;
                } else {
                    $expenses['free'] += 0;
                    $expenses['extra'] += 0;
                    if ($workday['workdayBeginTime'] >= (new \DateTime('2018-01-01')) && $workday['absenceHome'] > 5) {
                        $expenses['extra'] += $additionalExpenses8h;
                    }
                }
            } elseif ($workdays[$date_temp]['location_status'] == 'FINISHESATHOME') {
                $date_temp1 = clone $workday['workdayEndTime'];
                $date_temp1->modify('midnight');
                if ($workday['absenceHome'] > 8 || (($workday['workdayEndTime']->getTimestamp() - $date_temp1->getTimestamp()) / 3600) > 8) {
                    $expenses['free'] += $finishingCountryExpenses['expenses8h'];
                    $expenses['extra'] += $additionalExpenses8h;
                } else {
                    $expenses['free'] += 0;
                    $expenses['extra'] += 0;
                    if ($workday['workdayBeginTime'] >= (new \DateTime('2018-01-01')) && $workday['absenceHome'] > 5) {
                        $expenses['extra'] += $additionalExpenses8h;
                    }
                }
            } elseif ($workdays[$date_temp]['location_status'] == 'STARTSATHOME') {
                $date_temp1 = clone $workday['workdayEndTime'];
                $date_temp1->modify('midnight +1 day');
                if ($workday['absenceHome'] > 8 || (($date_temp1->getTimestamp() - $workday['workdayBeginTime']->getTimestamp()) / 3600) > 8) {
                    $expenses['free'] += $finishingCountryExpenses['expenses8h'];
                    $expenses['extra'] += $additionalExpenses8h;
                } else {
                    $expenses['free'] += 0;
                    $expenses['extra'] += 0;
                    if ($workday['workdayBeginTime'] >= (new \DateTime('2018-01-01')) && $workday['absenceHome'] > 5) {
                        $expenses['extra'] += $additionalExpenses8h;
                    }
                }
            } else {
                $expenses['free'] += $finishingCountryExpenses['expenses24h'];
                $expenses['extra'] += $additionalExpenses24h;
            }
        }

        return $expenses;
    }

    public function loadDayValidAction(Employee $employee, $date, $expenseWorkdayService=null)
    {
        $employee_id = $employee->getId();
        /*if (!in_array($employee_id, [23,54,82,86])) {
            return new JsonResponse(false);
        }*/
        if (empty($employee->getTrimbleId())) {
            return new JsonResponse(false);
        }

        if(empty($expenseWorkdayService)) {
            $expenseWorkdayService = $this->container->get('app.expense_workday_service', $this->getDoctrine());
        }

        $schooldays_temp = [];//$this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getSchooldaysByEmployeeDate($employee_id, (new \DateTime($date))->modify('midnight -1 day')->format('Y-m-d'), (new \DateTime($date))->modify('midnight +1 month +1 day')->format('Y-m-d'));
        $holidays_temp = $this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getAbsencesByEmployeeDate($employee_id, (new \DateTime($date))->modify('midnight -1 day')->format('Y-m-d'), (new \DateTime($date))->modify('midnight +1 month +1 day')->format('Y-m-d'));
        $illness_temp = [];//$this->getDoctrine()->getRepository('AbsenceBundle:Absence')->getIllnessesByEmployeeDate($employee_id, (new \DateTime($date))->modify('midnight -1 day')->format('Y-m-d'), (new \DateTime($date))->modify('midnight +1 month +1 day')->format('Y-m-d'));

        if(!is_null($employee->getContract())) {
            $weeklyHoursOfWork = $employee->getContract()->getWeeklyHoursOfWork();
        } else {
            $weeklyHoursOfWork = $employee->getContract();
        }

        // Workdays
        $workdays = $this->getWorkdaysByEmployeeDay($employee_id, $date, [], true);
        // Traces
        $workdays = $this->getTracesByEmployeeDay($employee, $date, $workdays);

        // checkBeginEndHome
        $workdays = $this->checkBeginEndHome($employee, $expenseWorkdayService, $workdays);

        $cars_temp = $this->getDoctrine()->getRepository('VehicleLogBundle:VehicleLog')->findByMonth($employee_id, $date, (new \DateTime($date))->modify('midnight +1 day')->format('Y-m-d'));
        $cars_temp2 = array();
        $car_days = array();

        foreach ($cars_temp as $car) {
            if ($car->getReason()->getId() == 1) {
                $date_temp = "car_".$car->getVehicleLogBeginTime()->format('Y-m-d H:i:s');
                $date_temp2 = $car->getVehicleLogBeginTime()->format('Y-m-d');

                $car_days[$date_temp] = array(
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
                );
                if (!empty($car->getVehicleLogEndTime())) {
                    $car_days[$date_temp]['workdayEndTime'] = $car->getVehicleLogEndTime();
                    $car_days[$date_temp]['workdayEndLat'] = $car->getVehicleLogEndPosition()->getLat();
                    $car_days[$date_temp]['workdayEndLon'] = $car->getVehicleLogEndPosition()->getLon();
                }
                $startsAtHome = $expenseWorkdayService->startsAtHome($employee, $car_days[$date_temp]);
                $startsAtCompany = $expenseWorkdayService->startsAtCompany($car_days[$date_temp]);
                $car_days[$date_temp]['workdayBeginHome'] = ($startsAtHome||$startsAtCompany)?True:False;

                $finishesAtHome = $expenseWorkdayService->finishesAtHome($employee, $car_days[$date_temp]);
                $finishesAtCompany = $expenseWorkdayService->finishesAtCompany($car_days[$date_temp]);
                $car_days[$date_temp]['workdayEndHome'] = ($finishesAtHome||$finishesAtCompany)?True:False;

                if (!array_key_exists($date_temp2, $cars_temp2)) {
                    $cars_temp2[$date_temp2] = array($car_days[$date_temp]);
                } else {
                    $cars_temp2[$date_temp2][] = $car_days[$date_temp];
                }
            }
        }

        unset($car_days);

        foreach ($cars_temp2 as $date_temp => $lCar) {
            if (array_key_exists($date_temp, $workdays)) {
                foreach ($lCar as $car) {
                    if ($car['workdayBeginTime'] < $workdays[$date_temp]['workdayBeginTime']) {
                        $workdays[$date_temp]['workdayBeginHome'] = ($workdays[$date_temp]['workdayBeginHome'] || (empty($workdays[$date_temp]['savedOnce']) && ($car['workdayBeginHome']?True:False))); //True)); //($car['workdayBeginHome']?True:False)));
                        $workdays[$date_temp]['workingTime'] += round(($workdays[$date_temp]['workdayBeginTime']->getTimestamp() - $car['workdayBeginTime']->getTimestamp()) / 3600, 2);
                    }
                    if ($car['workdayEndTime'] > $workdays[$date_temp]['workdayEndTime']) {
                        $workdays[$date_temp]['workdayEndHome'] = ($workdays[$date_temp]['workdayEndHome'] || (empty($workdays[$date_temp]['savedOnce']) && ($car['workdayEndHome']?True:False))); //True)); //($car['workdayEndHome']?True:False)));
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

        //checkAbsence
        $checkedAbsence = $this->checkAbsence($employee, $date, $weeklyHoursOfWork, $workdays, $schooldays_temp, $holidays_temp, $illness_temp);
        $workdays = $checkedAbsence[0];

        sleep(1);
        return new JsonResponse(['employee_id'=>$employee_id, 'employee_name'=>$employee->getFullname(), 'day'=>$date, 'valid'=>!empty($workdays)]);
    }

    public function loadValiddaysByDayAction($date=false,$group='active')
    {
        if (empty($date)) {
            $date = (new \DateTime())->modify('midnight -1 day')->format('Y-m-d');
        }

        $drivers = [];

        if((new \DateTime())->format('N') < 6){
            set_time_limit(0);
            ini_set('memory_limit','-1');

            if ($group=='all') {
                $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->findAll();
            } else if ($group=='archived') {
                $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllSoftDeleted();
            } else if ($group=='active') {
                $employees = $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->getAllDriversAvailable();//(new \DateTime($date))->modify('midnight -1 month')->format('Y-m-d H:i:s'));
            }

            WorkdaydayController::$expensetest = array();
            $asyncRequest = new \AsyncRequest\AsyncRequest();
            $asyncRequest->setParallelLimit(5);
            foreach ($employees as $employee) {
                if ($employee->getId() == 9996) {
                    continue;
                }
                $request = new \AsyncRequest\Request($this->get('request')->getSchemeAndHttpHost().$this->generateUrl('expenses_workday_loadDayValid', ['id'=>$employee->getId(), 'date'=>$date]));
                $request->setOption(CURLOPT_USERPWD, "trimble:ca584Babn+F_ZSAJ.qW");
                $asyncRequest->enqueue($request, function(\AsyncRequest\Response $response) {
                    $temp_expenses = json_decode($response->getBody(),true);
                    //if($temp_expenses) {
                        WorkdaydayController::$expensetest[$temp_expenses['employee_id']] = $temp_expenses;
                    //}
                });
            }
            $asyncRequest->run();
            $expenses = WorkdaydayController::$expensetest;

            foreach($employees as $employee) {
                if ($employee->getId() == 9996) {
                    continue;
                } elseif(array_key_exists($employee->getId(), $expenses) && $expenses[$employee->getId()]['valid']) {
                    continue;
                } else {
                    $drivers[$employee->getId()] = $employee;
                }
            }

            if (count($drivers)>0) {
                usort($drivers, function($a, $b) {
                    if ($a->__toString() == $b->__toString()) {
                        return 0;
                    }
                    return ($a->__toString() < $b->__toString()) ? -1 : 1;
                });

                $to = array(
                    'fhoeing@giesker-laakmann.de',
                    'mbusch@giesker-laakmann.de',
                );

                $message = (new \Swift_Message(count($drivers).' Mitarbeiter ohne Kalendereintrag'))
                    ->setFrom('gl@361gradmedien.de')
                    ->setBcc('gl@361gradmedien.de')
                    ->setTo($to)
                    ->setBody(
                        $this->renderView(
                            'ExpenseBundle:Workday:email.html.twig',
                            array(
                                'drivers' => $drivers,
                                'date' => $date,
                            )
                        ),
                        'text/html'
                    );

                $this->get('mailer')->send($message);
            }
        }

        return $this->render('ExpenseBundle:Workday:email.html.twig', array(
            'drivers' => $drivers,
            'date' => $date,
        ));
    }
}
