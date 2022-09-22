<?php

namespace EmployeeBundle\Service;
use EmployeeBundle\Entity\Employee;
use LocationBundle\Entity\Location;
use LocationBundle\Entity\Locationtype;

class EmployeeService
{
    private $employee;

    public function __construct($doctrine) {
        $this->doctrine = $doctrine;
    }

    public function geocodeEmployee(Employee $employee) {
        $this->employee = $employee;
        $lat1 = $this->employee->getLat();
        $lng1 = $this->employee->getLon();

        if ((is_null($lat1) || is_null($lng1)) && (!empty($this->employee->getTown()))) {
            $address = rawurlencode($this->employee->getStreet().", ".$this->employee->getZipCode()." ".$this->employee->getTown());
            $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=AIzaSyDRT-dvbz9V3wxObDtziSUesCxXGMN6E2M";
            $json = file_get_contents($url);
            $results = json_decode($json);
            if (count($results->results) > 0) {
                $location = $results->results[0]->geometry->location;
                $this->employee->setLat($location->lat);
                $this->employee->setLon($location->lng);
                $this->employee->setGeofenceMeters(1000);
                $this->doctrine->getManager()->flush();
                $this->prooveOrCopyGeofence($this->employee);
            }
        }
    }

    private function prooveOrCopyGeofence(Employee $employee) {
        $geofence_is_copied = false;

        if (empty($employee->getLat()) || empty($employee->getLon())) {
            $geofence_is_copied = true;
        }

        if (!$geofence_is_copied && !empty($employee->getGeofences())) {
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

    public function generatePassword() {
        $validChars = array(2,3,4,5,6,7,8,9,'a','b','c','d','e','f','g','h','i','j','k','m','n','o','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','J','K','L','M','N','P','Q','R','S','T','U','V','W','X','Y','Z');
        $ret = "";
        for ($i=0;$i<12;$i++) {
            $ret .= $validChars[array_rand($validChars,1)];
        }
        return $ret;
    }

    private function _usernameAvailable($username) {
        return empty($this->doctrine->getRepository('EmployeeBundle:User')->findBy(array('username'=>$username)));
    }

    private function _remove_accent($str)
    {
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        return str_replace($a, $b, $str);
    }

    public function generateUsername(Employee $employee) {
        $lName = explode(',',$employee->getName(),2);
        if (count($lName)==1) {
            $lName = array_reverse(explode(' ',$employee->getName(),2));
        }

        if (count($lName)==1) {
            $lName[1] = $employee->getPrename();
        }

        $name = preg_replace('/[^A-Z]/','',strtoupper($this->_remove_accent($lName[0])));
        $prename = preg_replace('/[^A-Z]/','',strtoupper($this->_remove_accent($lName[1])));

        $i = 1;
        while((!$this->_usernameAvailable(substr($prename,0,$i).$name)) && ($i <= strlen($prename))) {
            $i++;
        }
        if ($i > strlen($prename)) {
            $username = $prename.$name;
            $i = 1;
            while(!$this->_usernameAvailable($username.$i)) {
                $i++;
            }
            $username .= $i;
        } else {
            $username = substr($prename,0,$i).$name;
        }
        return $username;
    }

}
