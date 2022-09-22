<?php

namespace CalendarBundle\Service;

use AbsenceBundle\Entity\PublicHoliday;
use Carbon\Carbon;

class PublicHolidayService
{
    private $doctrine;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getPublicHolidaysFromAPIandSave($year)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://feiertage-api.de/api/?nur_land=NW&jahr='.$year);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $data = json_decode($response);
        foreach ($data as $key => $value) {
            $publicHoliday = new PublicHoliday();
            $publicHoliday->setState('DE-NW');
            $publicHoliday->setTitle($key);
            $publicHoliday->setStart(new Carbon($value->datum));
            $publicHoliday->setEnd(new Carbon($value->datum));
            $publicHoliday->setType(1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($publicHoliday);
            $em->flush();
        }
    }

    public function getVacationsFromAPIandSave($year)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://ferien-api.de/api/v1/holidays/NW/'.$year);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $data = json_decode($response);
        foreach ($data as $key => $value) {
            $publicHoliday = new PublicHoliday();
            $publicHoliday->setState('DE-NW');
            $publicHoliday->setTitle($value->name);
            $publicHoliday->setStart(new Carbon($value->start));
            $publicHoliday->setEnd(new Carbon($value->end));
            $publicHoliday->setType(2);
            $em = $this->getDoctrine()->getManager();
            $em->persist($publicHoliday);
            $em->flush();
        }
    }

    public function publicHolidaysToJson($publicHolidays)
    {
        $ret = [];

        foreach ($publicHolidays as $publicHoliday) {
            $end = clone $publicHoliday->getEnd();
            $end->modify('+1 day');
            $temp = [
                "title" => $publicHoliday->getTitle(),
                "start" => $publicHoliday->getStart()->format('Y-m-d'),
                "end" 	=> $end->format('Y-m-d'),
                "rendering" => "background",
            ];
            if ($publicHoliday->getType() === 1) {
                $temp["color"] = "#ff9f89";
                $temp["publicHoliday"] = true;
            } elseif ($publicHoliday->getType() === 2) {
                $temp["publicHoliday"] = false;
            }
            $ret[] = $temp;
        }
        return $ret;
    }

    public function checkIfYearIsSaved($year)
    {
        $years = $this->doctrine->getRepository('AbsenceBundle:PublicHoliday')->getAllYears();
        if (in_array($year, $years)) {
            return true;
        }
        return false;
    }
}
