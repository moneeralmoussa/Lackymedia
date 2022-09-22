<?php

namespace ExpenseBundle\Service;
use EmployeeBundle\Entity\Employee;
use LocationBundle\Entity\Location;
use LocationBundle\Entity\Locationtype;

class ExpenseWorkdayService
{
    private $employee;
    
    public function __construct($doctrine) {
        $this->doctrine = $doctrine;
        $this->locations = null;
    }

    private function prooveOrCopyGeofence(Employee $employee) {
        $geofence_is_copied = false;

        if (empty($employee->getLat()) || empty($employee->getLon())) {
            $geofence_is_copied = true;
        }

        if (!$geofence_is_copied) {
            foreach ($employee->getGeofences() as $geofence) {
                if ($geofence->getLat() == $employee->getLat() && $geofence->getLon() == $employee->getLon()) {
                    $geofence_is_copied = true;
                }
            }
        }
        
        if (!$geofence_is_copied) {
            $location = new Location();
            $location->setLocationtype($this->doctrine->getRepository('LocationBundle:Locationtype')->find(4));
            $location->setLat($employee->getLat());
            $location->setLon($employee->getLon());
            $location->setGeofenceMeters($employee->getGeofenceMeters());
            $location->setName('Wohnort '.$employee->getFullname());
            $location->setStreet($employee->getStreet());
            $location->setZipCode($employee->getZipCode());
            $location->setTown($employee->getTown());
            $location->setEmployee($employee);
            $employee->addGeofence($location);
            $this->doctrine->getManager()->persist($location);
            $this->doctrine->getManager()->flush();
        }
    }

    public function startsAtHome(Employee $employee, $workday, $oldValue=null) {
        $this->employee = $employee;
        $this->prooveOrCopyGeofence($this->employee);
        
        if (!is_null($oldValue)) {
            return $oldValue;
        }
        
        foreach ($this->employee->getGeofences() as $location) {
            $lat2 = $workday['workdayBeginLat'];
            $lng2 = $workday['workdayBeginLon'];
            $lat1 = $location->getLat();
            $lng1 = $location->getLon();
            $max_diff = $location->getGeofenceMeters();
            if ((acos(sin($lat1=deg2rad($lat1))*sin($lat2=deg2rad($lat2))+cos($lat1)*cos($lat2) *cos(deg2rad($lng2) - deg2rad($lng1)))*(6378137)) <= $max_diff) {
                return true;
            }
        }
        return false; //(acos(sin($lat1=deg2rad($lat1))*sin($lat2=deg2rad($lat2))+cos($lat1)*cos($lat2) *cos(deg2rad($lng2) - deg2rad($lng1)))*(6378137)) <= $max_diff;
    }
    
    public function finishesAtHome(Employee $employee, $workday, $oldValue=null) {
        $this->employee = $employee;
        $this->prooveOrCopyGeofence($this->employee);
        
        if (!is_null($oldValue)) {
            return $oldValue;
        }
        
        foreach ($this->employee->getGeofences() as $location) {
            $lat2 = $workday['workdayEndLat'];
            $lng2 = $workday['workdayEndLon'];
            $lat1 = $location->getLat();
            $lng1 = $location->getLon();
            $max_diff = $location->getGeofenceMeters();
            if ((acos(sin($lat1=deg2rad($lat1))*sin($lat2=deg2rad($lat2))+cos($lat1)*cos($lat2) *cos(deg2rad($lng2) - deg2rad($lng1)))*(6378137)) <= $max_diff) {
                return true;
            }
        }
        return false; //(acos(sin($lat1=deg2rad($lat1))*sin($lat2=deg2rad($lat2))+cos($lat1)*cos($lat2) *cos(deg2rad($lng2) - deg2rad($lng1)))*(6378137)) <= $max_diff;
    }
    
    public function startsAtCompany($workday) {
        if (!$this->employee->getSleepsInCompanyMeansSleepsAtHome()) {
            return false;
        }

        if (\is_null($this->locations)) {
            $this->locations = $this->doctrine->getRepository('LocationBundle:Location')->findByType(1);
        }
        
        foreach ($this->locations as $location) {
            $lat2 = $workday['workdayBeginLat'];
            $lng2 = $workday['workdayBeginLon'];
            $lat1 = $location->getLat();
            $lng1 = $location->getLon();
            $max_diff = $location->getGeofenceMeters();
            if ((acos(sin($lat1=deg2rad($lat1))*sin($lat2=deg2rad($lat2))+cos($lat1)*cos($lat2) *cos(deg2rad($lng2) - deg2rad($lng1)))*(6378137)) <= $max_diff) {
                return true;
            }
        }
        return false;
    }
    
    public function finishesAtCompany($workday) {
        if (!$this->employee->getSleepsInCompanyMeansSleepsAtHome()) {
            return false;
        }

        if (\is_null($this->locations)) {
            $this->locations = $this->doctrine->getRepository('LocationBundle:Location')->findByType(1);
        }
        
        foreach ($this->locations as $location) {
            $lat2 = $workday['workdayEndLat'];
            $lng2 = $workday['workdayEndLon'];
            $lat1 = $location->getLat();
            $lng1 = $location->getLon();
            $max_diff = $location->getGeofenceMeters();
            if ((acos(sin($lat1=deg2rad($lat1))*sin($lat2=deg2rad($lat2))+cos($lat1)*cos($lat2) *cos(deg2rad($lng2) - deg2rad($lng1)))*(6378137)) <= $max_diff) {
                return true;
            }
        }
        return false;
    }

    private function geocodeViaOSM($lat, $lon) {
        $locations = $this->doctrine->getRepository('LocationBundle:Location')->findByType(2);
        
        foreach ($locations as $location) {
            $lat2 = $lat;
            $lng2 = $lon;
            $lat1 = $location->getLat();
            $lng1 = $location->getLon();
            $max_diff = 500;
            if ((acos(sin($lat1=deg2rad($lat1))*sin($lat2=deg2rad($lat2))+cos($lat1)*cos($lat2) *cos(deg2rad($lng2) - deg2rad($lng1)))*(6378137)) <= $max_diff) {
                $results = (object)[];
                $results->address = (object)[];
                $results->address->country_code = $location->getCountryCode();
                $results->address->town = $location->getTown();

                return $results;
            }
        }

        $location = new Location();
        $location->setLocationtype($this->doctrine->getRepository('LocationBundle:Locationtype')->find(2));
        $location->setLat($lat);
        $location->setLon($lon);
        $saveLocation = false;
        
        sleep(1);
        $url = "https://eu1.locationiq.org/v1/reverse.php?key=9c904cbcb7b28a&lat=$lat&lon=$lon&format=json&accept-language=de,en-US;q=0.7,en;q=0.3";
        $json = @file_get_contents($url);
        $results = @json_decode($json);
        if ($results && isset($results->address) && isset($results->address->country_code)) {
            $saveLocation = true;
            $location->setCountryCode($results->address->country_code);
        }
        
        if ($results && isset($results->address) && isset($results->address->town)) {
            $saveLocation = true;
            $location->setTown($results->address->town);
        } elseif ($results && isset($results->address) && isset($results->address->city)) {
            $saveLocation = true;
            $location->setTown($results->address->city);
        } elseif ($results && isset($results->address) && isset($results->address->village)) {
            $saveLocation = true;
            $location->setTown($results->address->village);
        } elseif ($results && isset($results->address) && isset($results->address->county) && isset($results->address->suburb)) {
            $saveLocation = true;
            $location->setTown($results->address->suburb);
        } elseif ($results && isset($results->address) && isset($results->address->county)) {
            $saveLocation = true;
            $location->setTown($results->address->county);
        }
        
        if ($saveLocation) {
            $location->setName(strtoupper($location->getCountryCode()).'-'.$location->getTown());
            $this->doctrine->getManager()->persist($location);
            $this->doctrine->getManager()->flush();
        }

        return $results;
    }
    
    public function getCountryIso($lat,$lon) {
        if (!is_null($lat) && !is_null($lon)) {
            $results = $this->geocodeViaOSM($lat, $lon);

            if ($results && $results->address && $results->address->country_code) {
                return strtoupper($results->address->country_code);
            }
        }
        return false;
    }
    
    public function reverseGeocoderOSM($lat, $lon) {
        if (!is_null($lat) && !is_null($lon)) {
            $results = $this->geocodeViaOSM($lat, $lon);

            if ($results && isset($results->address) && isset($results->address->town)) {
                return (!empty($results->address->country_code) && $results->address->country_code !="de"?strtoupper($results->address->country_code)."-":"").$results->address->town;
            }
            if ($results && isset($results->address) && isset($results->address->city)) {
                return (!empty($results->address->country_code) && $results->address->country_code !="de"?strtoupper($results->address->country_code)."-":"").$results->address->city;
            }
            if ($results && isset($results->address) && isset($results->address->village)) {
                return (!empty($results->address->country_code) && $results->address->country_code !="de"?strtoupper($results->address->country_code)."-":"").$results->address->village;
            }
            if ($results && isset($results->address) && isset($results->address->county) && isset($results->address->suburb)) {
                return (!empty($results->address->country_code) && $results->address->country_code !="de"?strtoupper($results->address->country_code)."-":"").$results->address->county."-".$results->address->suburb;
            }
            if ($results && isset($results->address) && isset($results->address->county)) {
                return (!empty($results->address->country_code) && $results->address->country_code !="de"?strtoupper($results->address->country_code)."-":"").$results->address->county;
            }
        }
        return false;
    }
}
