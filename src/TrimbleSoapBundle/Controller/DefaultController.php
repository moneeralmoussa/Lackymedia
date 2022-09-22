<?php

namespace TrimbleSoapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $traces = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findAll();

        return $this->render('TrimbleSoapBundle:Default:index.html.twig', array(
            'traces' => $traces
        ));
    }

    public function polltracesAction()
    {
        if ((strtotime('02:00:00')<= time()) && (time()<= strtotime('03:00:00'))) {
            // the container will instantiate a new MessageGenerator()
            $trimbleSoapService = $this->container->get('app.trimble_soap_service', $this->getDoctrine());
            $trimbleSoapService->pollTraces();
            $message = 'Im Zeitraum';
        } else {
            $message = 'Falsche Uhrzeit';
        }
        // or use this shorter syntax
        // $messageGenerator = $this->get('app.message_generator');

        $traces = []; //$this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findAll();

        return $this->render('TrimbleSoapBundle:Default:index.html.twig', array(
            'traces' => $traces,
            'message' => $message,
        ));
    }

    public function notloggedoutmailAction()
    {
        $yesterday = (new \DateTime())->modify('midnight -1 day');
        $today = (new \DateTime())->modify('midnight');

        $traces = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findAllLoginLogout($yesterday->format('Y-m-d'), $today->format('Y-m-d'));
        //$traces = $this->getDoctrine()->getRepository('TrimbleSoapBundle:Tracedata')->findAllLoginLogout('2017-06-06','2017-06-07');

        $loginLogoutMap = array();
        $employeesStillLoggedIn = array();

        foreach ($traces as $trace) {
            $did = $trace->getDid();
            $type = (string)$trace->getType();

            if (!array_key_exists($did, $loginLogoutMap)) {
                $loginLogoutMap[$did] = array('source'=>$trace->getSource());
            }
            if (!array_key_exists($type, $loginLogoutMap[$did])) {
                $loginLogoutMap[$did][$type] = array();
            }
            $loginLogoutMap[$did][$type][] = $trace->getTime();
        }
        unset($traces);

        foreach ($loginLogoutMap as $did => $did_values) {
            if (
                (array_key_exists('1', $did_values) && !array_key_exists('2', $did_values))
                || (
                    (array_key_exists('1', $did_values) && array_key_exists('2', $did_values))
                    && (
                        //(count($did_values['1']) > count($did_values['2']))
                        //||
                        ($did_values['1'][count($did_values['1'])-1] > $did_values['2'][count($did_values['2'])-1])
                    )
                )
            ) {
                $employeesStillLoggedIn[] = array('did'=>$did, 'source'=>$did_values['source']);
            }
        }
        unset($loginLogoutMap);

        $employees = array();
        if (count($employeesStillLoggedIn)>0) {
            foreach($employeesStillLoggedIn as $driver){
                $employees[] = array(
                    'driver' => $this->getDoctrine()->getRepository('EmployeeBundle:Employee')->findOneBy(array('trimbleId'=>$driver['did'])),
                    'vehicle' => $this->getDoctrine()->getRepository('VehicleBundle:Vehicle')->findOneBy(array('trimbleId'=>$driver['source'])),
                );
            }
            unset($employeesStillLoggedIn);

            $to = array(
                'hlaakmann@giesker-laakmann.de',
                'sheimken@giesker-laakmann.de',
                'mbusch@giesker-laakmann.de',
                'SGerdener@giesker-laakmann.de',
                'cbolz@giesker-laakmann.de',
                'hschulte-wien@giesker-laakmann.de',
                'KHesener@giesker-laakmann.de',
                'klaakmann@giesker-laakmann.de',
                'msiewert@giesker-laakmann.de',
                'rgerling@giesker-laakmann.de',
                'slaakmann@giesker-laakmann.de',
                'frickermann@giesker-laakmann.de',
                'lrossmoeller@giesker-laakmann.de',
                );

            $message = (new \Swift_Message(count($employees).' nicht abgemeldete Fahrer'))
                ->setFrom('gl@361gradmedien.de')
                ->setBcc('gl@361gradmedien.de')
                ->setTo($to)
                ->setBody(
                    $this->renderView(
                        'TrimbleSoapBundle:Default:email.html.twig',
                        array(
                            'employees' => $employees,
                            'date' => $yesterday,
                        )
                    ),
                    'text/html'
                )
                /*
                 * If you also want to include a plaintext version of the message
                ->addPart(
                    $this->renderView(
                        'Emails/registration.txt.twig',
                        array('name' => $name)
                    ),
                    'text/plain'
                )
                */
            ;

            $this->get('mailer')->send($message);
        }

        return $this->render('TrimbleSoapBundle:Default:email.html.twig', array(
            'employees' => $employees,
            'date' => $yesterday,
        ));
    }

    public function extractconsumptionAction()
    {
        set_time_limit(0);
        ini_set('memory_limit','-1');

        $process = new Process("python ../python/getmails.py");
        $process->setTimeout(null);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // return the output, don't use if you used NullOutput()
        $content = $process->getOutput();
        //return new JsonResponse(json_decode($content,true));

		$fmsreports = json_decode($content,true);

        foreach($fmsreports as $fmsreport) {
            $lastweek = \DateTime::createFromFormat('d.m.y', $fmsreport['begin'])->modify('midnight');

            if (empty($this->getDoctrine()->getRepository('VehicleBundle:Consumption')->findBy(['consumptionBeginTime'=>$lastweek]))) {
                $lastweek = \DateTime::createFromFormat('d.m.y', $fmsreport['begin'])->modify('midnight')->format('Y-m-d');
                $today = \DateTime::createFromFormat('d.m.y', $fmsreport['end'])->modify('midnight +1 day')->format('Y-m-d');

                $trimbleSoapService = $this->container->get('app.trimble_soap_service', $this->getDoctrine());
                $trimbleSoapService->extractConsumption($lastweek, $today, $fmsreport);
            }
        }

        return new JsonResponse(array(
            'success' => true,
        ));
    }
}
