<?php

namespace EmployeeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Carbon\Carbon;

//use EmployeeBundle\Entity\Contract;
//use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
/**
 * Employee
 */
class Employee
{
    //use SoftDeleteableEntity;
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $komalogId;

    /**
     * @var integer
     */
    private $trimbleId;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $prename;

    /**
     * @var string
     */
    private $salutation;

    /**
     * @var string
     */
    private $street;

    /**
     * @var string
     */
    private $zipCode;

    /**
     * @var string
     */
    private $town;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $fax;

    /**
     * @var string
     */
    private $mobile;

    /**
     * @var \DateTime
     */
    private $birthday;

    /**
     * @var \DateTime
     */
    private $vehicleLogBlocked;

    /**
     * @var integer
     */
    private $sex;

    /**
     * @var string
     */
    private $initial;

    /**
     * @var \EmployeeBundle\Entity\User
     */
    private $user;

    /**
     * @var \EmployeeBundle\Entity\Contract
     */
    private $contract;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $absences;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $workdays;

    /**
     * @var \EmployeeBundle\Entity\Department
     */
    private $department;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->absences = new \Doctrine\Common\Collections\ArrayCollection();
        $this->messagess = new \Doctrine\Common\Collections\ArrayCollection();
        $this->workdays = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set komalogId
     *
     * @param integer $komalogId
     *
     * @return Employee
     */
    public function setKomalogId($komalogId)
    {
        $this->komalogId = $komalogId;

        return $this;
    }

    /**
     * Get komalogId
     *
     * @return integer
     */
    public function getKomalogId()
    {
        return $this->komalogId;
    }

    /**
     * Set trimbleId
     *
     * @param integer $trimbleId
     *
     * @return Employee
     */
    public function setTrimbleId($trimbleId)
    {
        $this->trimbleId = $trimbleId;

        return $this;
    }

    /**
     * Get trimbleId
     *
     * @return integer
     */
    public function getTrimbleId()
    {
        return $this->trimbleId;
    }

    /**
     * Get trimbleId
     *
     * @return [integer]
     */
    public function getTrimbleIds()
    {
        $ret = [$this->trimbleId];
        foreach($this->employeeArchivs as $employeeArchiv) {
            $temp_TrimbleId = $employeeArchiv->getTrimbleId();
            if(!in_array($temp_TrimbleId, $ret)) {
                $ret[] = $temp_TrimbleId;
            }
        }
        return $ret;
    }

    /**
     * Set countryCode
     *
     * @param string $countryCode
     *
     * @return Employee
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Employee
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set prename
     *
     * @param string $prename
     *
     * @return Employee
     */
    public function setPrename($prename)
    {
        $this->prename = $prename;

        return $this;
    }

    /**
     * Get prename
     *
     * @return string
     */
    public function getPrename()
    {
        return $this->prename;
    }

    /**
     * Set salutation
     *
     * @param string $salutation
     *
     * @return Employee
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;

        return $this;
    }

    /**
     * Get salutation
     *
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Employee
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set zipCode
     *
     * @param string $zipCode
     *
     * @return Employee
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set town
     *
     * @param string $town
     *
     * @return Employee
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * Get town
     *
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    public function getAddress()
    {
        return $this->street.(!empty($this->street) && (!empty($this->zipCode) || !empty($this->town))?', ':'').$this->zipCode.(!empty($this->zipCode) && !empty($this->town)?' ':'').$this->town;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Employee
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set fax
     *
     * @param string $fax
     *
     * @return Employee
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     *
     * @return Employee
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     *
     * @return Employee
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set vehicleLogBlocked
     *
     * @param \DateTime $vehicleLogBlocked
     *
     * @return Employee
     */
    public function setVehicleLogBlocked($vehicleLogBlocked)
    {
        $this->vehicleLogBlocked = $vehicleLogBlocked;

        return $this;
    }

    /**
     * Get vehicleLogBlocked
     *
     * @return \DateTime
     */
    public function getVehicleLogBlocked()
    {
        return $this->vehicleLogBlocked;
    }

    /**
     * Set sex
     *
     * @param integer $sex
     *
     * @return Employee
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get sex
     *
     * @return integer
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set initial
     *
     * @param string $initial
     *
     * @return Employee
     */
    public function setInitial($initial)
    {
        $this->initial = $initial;

        return $this;
    }

    /**
     * Get initial
     *
     * @return string
     */
    public function getInitial()
    {
        return $this->initial;
    }

    /**
     * Set user
     *
     * @param \EmployeeBundle\Entity\User $user
     *
     * @return Employee
     */
    public function setUser(\EmployeeBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \EmployeeBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set contract
     *
     * @param \EmployeeBundle\Entity\Contract $contract
     *
     * @return Employee
     */
    public function setContract(\EmployeeBundle\Entity\Contract $contract = null)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get contract
     *
     * @return \EmployeeBundle\Entity\Contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Add absence
     *
     * @param \AbsenceBundle\Entity\Absence $absence
     *
     * @return Employee
     */
    public function addAbsence(\AbsenceBundle\Entity\Absence $absence)
    {
        $this->absences[] = $absence;

        return $this;
    }

    /**
     * Remove absence
     *
     * @param \AbsenceBundle\Entity\Absence $absence
     */
    public function removeAbsence(\AbsenceBundle\Entity\Absence $absence)
    {
        $this->absences->removeElement($absence);
    }

    /**
     * Get absences
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAbsences()
    {
        return $this->absences;
    }

    /**
     * Add workday
     *
     * @param \ExpenseBundle\Entity\Workday $workday
     *
     * @return Employee
     */
    public function addWorkday(\ExpenseBundle\Entity\Workday $workday)
    {
        $this->workdays[] = $workday;

        return $this;
    }

    /**
     * Remove workday
     *
     * @param \ExpenseBundle\Entity\Workday $workday
     */
    public function removeWorkday(\ExpenseBundle\Entity\Workday $workday)
    {
        $this->workdays->removeElement($workday);
    }

    /**
     * Get workdays
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWorkdays()
    {
        return $this->workdays;
    }

    /**
     * Set department
     *
     * @param \EmployeeBundle\Entity\Department $department
     *
     * @return Employee
     */
    public function setDepartment(\EmployeeBundle\Entity\Department $department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department
     *
     * @return \EmployeeBundle\Entity\Department
     */
    public function getDepartment()
    {
        return $this->department;
    }
    public function __toString()
    {
        return $this->getName() . ", " . $this->getPrename(); //return $this->getPrename()." ".$this->getName();
    }
    public function getFullname()
    {
        return $this->getPrename()." ".$this->getName();
    }
    public function getFname()
    {
        $FName = $this->getPrename()." ".  $this->getName();
        if (strpos($this->getName(), ',') !== false) {
            $FName= $this->getPrename()." ". explode(',',$this->getName())[0];
        }
        if (strpos($this->getName(), ' ') !== false) {
            $FName= $this->getPrename()." ". explode(',',$this->getName())[0];
         }

      if (strpos($FName, '.') !== false) {
        $FName= explode(',',$this->getName())[1] .' '.  explode(' ',$FName)[2];
        }
        if($FName=='Bernd Laakmann Bernd') {$FName ='Bernd Laakmann';  }

        return $FName;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vehicleLogs;


    /**
     * Add vehicleLog
     *
     * @param \VehicleLogBundle\Entity\VehicleLog $vehicleLog
     *
     * @return Employee
     */
    public function addVehicleLog(\VehicleLogBundle\Entity\VehicleLog $vehicleLog)
    {
        $this->vehicleLogs[] = $vehicleLog;

        return $this;
    }

    /**
     * Remove vehicleLog
     *
     * @param \VehicleLogBundle\Entity\VehicleLog $vehicleLog
     */
    public function removeVehicleLog(\VehicleLogBundle\Entity\VehicleLog $vehicleLog)
    {
        $this->vehicleLogs->removeElement($vehicleLog);
    }

    /**
     * Get vehicleLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVehicleLogs()
    {
        return $this->vehicleLogs;
    }

    public function getUsername()
    {
        if ($this->user) {
            return $this->user->getUsername();
        }

        return '';
    }

    public function getEmail()
    {
        if ($this->user) {
            return $this->user->getEmail();
        }

        return '';
    }

    public function getPassword()
    {
        return '';
    }

    public function setUsername($name)
    {
        if ($this->user) {
            $this->user->setUsername($name);
        }
    }

    public function setEmail($email)
    {
        if ($this->user) {
            $this->user->setEmail($email);
        }
    }

    public function setPassword($password)
    {
        if ($this->user && !empty($password)) {
            $this->user->setPassword($password);
        }
    }

    public function getRoles()
    {
        // dump($this);die();
        if ($this->user) {
            return $this->user->getRoles();
        }

        return array();
    }
    /**
     * @var \DateTime
     */
    private $deleted_at;


    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Employee
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deleted_at = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }

    public function isDeleted()
    {
        if (!is_null($this->deleted_at) && $this->deleted_at < (new \DateTime())) {
            return true;
        }
        return false;
    }

    /**
     * @var string
     */
    private $lat;

    /**
     * @var string
     */
    private $lon;

    /**
     * @var string
     */
    private $geofenceMeters;


    /**
     * Set lat
     *
     * @param string $lat
     *
     * @return Employee
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return string
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lon
     *
     * @param string $lon
     *
     * @return Employee
     */
    public function setLon($lon)
    {
        $this->lon = $lon;

        return $this;
    }

    /**
     * Get lon
     *
     * @return string
     */
    public function getLon()
    {
        return $this->lon;
    }

    /**
     * Set geofenceMeters
     *
     * @param string $geofenceMeters
     *
     * @return Employee
     */
    public function setGeofenceMeters($geofenceMeters)
    {
        $this->geofenceMeters = $geofenceMeters;

        return $this;
    }

    /**
     * Get geofenceMeters
     *
     * @return string
     */
    public function getGeofenceMeters()
    {
        return $this->geofenceMeters;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vehicleReservations;


    /**
     * Add vehicleReservation
     *
     * @param \VehicleLogBundle\Entity\VehicleReservation $vehicleReservation
     *
     * @return Employee
     */
    public function addVehicleReservation(\VehicleLogBundle\Entity\VehicleReservation $vehicleReservation)
    {
        $this->vehicleReservations[] = $vehicleReservation;

        return $this;
    }

    /**
     * Remove vehicleReservation
     *
     * @param \VehicleLogBundle\Entity\VehicleReservation $vehicleReservation
     */
    public function removeVehicleReservation(\VehicleLogBundle\Entity\VehicleReservation $vehicleReservation)
    {
        $this->vehicleReservations->removeElement($vehicleReservation);
    }

    /**
     * Get vehicleReservations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVehicleReservations()
    {
        return $this->vehicleReservations;
    }
    /**
     * @var boolean
     */
    private $sleepsInCompanyMeansSleepsAtHome;

    /**
     * @var string
     */
    private $salary;


    /**
     * Set sleepsInCompanyMeansSleepsAtHome
     *
     * @param boolean $sleepsInCompanyMeansSleepsAtHome
     *
     * @return Employee
     */
    public function setSleepsInCompanyMeansSleepsAtHome($sleepsInCompanyMeansSleepsAtHome)
    {
        $this->sleepsInCompanyMeansSleepsAtHome = $sleepsInCompanyMeansSleepsAtHome;

        return $this;
    }

    /**
     * Get sleepsInCompanyMeansSleepsAtHome
     *
     * @return boolean
     */
    public function getSleepsInCompanyMeansSleepsAtHome()
    {
        return $this->sleepsInCompanyMeansSleepsAtHome;
    }

    /**
     * Set salary
     *
     * @param string $salary
     *
     * @return Employee
     */
    public function setSalary($salary)
    {
        $this->salary = $salary;

        return $this;
    }

    /**
     * Get salary
     *
     * @return string
     */
    public function getSalary()
    {
        return $this->salary;
    }

    public function getDailySalary()
    {
        return $this->getSalary() / 20;
    }

    public function getTheoreticRemainingDaysOfVacation($date=null)
    {
        if (empty($date)) {
            $date= new \DateTime();
        }
        $holidays = is_null($this->getContract()) ? 0 : $this->getContract()->getHolidays();
        return (12 - (int)$date->format('n')) / 12 * $holidays;
    }

    /**
     * @var string
     */
    private $remainingDaysOfVacation;


    /**
     * Set remainingDaysOfVacation
     *
     * @param string $remainingDaysOfVacation
     *
     * @return Employee
     */
    public function setRemainingDaysOfVacation($remainingDaysOfVacation)
    {
        $this->remainingDaysOfVacation = $remainingDaysOfVacation;

        return $this;
    }

    /**
     * Get remainingDaysOfVacation
     *
     * @return string
     */
    public function getRemainingDaysOfVacation()
    {
        return floatval($this->remainingDaysOfVacation);
    }
    /**
     * @var string
     */
    private $usualHomeTravelHours;


    /**
     * Set usualHomeTravelHours
     *
     * @param string $usualHomeTravelHours
     *
     * @return Employee
     */
    public function setUsualHomeTravelHours($usualHomeTravelHours)
    {
        $this->usualHomeTravelHours = $usualHomeTravelHours;

        return $this;
    }

    /**
     * Get usualHomeTravelHours
     *
     * @return string
     */
    public function getUsualHomeTravelHours()
    {
        return $this->usualHomeTravelHours;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $absenceClearings;


    /**
     * Add absenceClearing
     *
     * @param \EmployeeBundle\Entity\AbsenceClearing $absenceClearing
     *
     * @return Employee
     */
    public function addAbsenceClearing(\EmployeeBundle\Entity\AbsenceClearing $absenceClearing)
    {
        $this->absenceClearings[] = $absenceClearing;

        return $this;
    }

    /**
     * Remove absenceClearing
     *
     * @param \EmployeeBundle\Entity\AbsenceClearing $absenceClearing
     */
    public function removeAbsenceClearing(\EmployeeBundle\Entity\AbsenceClearing $absenceClearing)
    {
        $this->absenceClearings->removeElement($absenceClearing);
    }

    /**
     * Get absenceClearings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAbsenceClearings()
    {
        return $this->absenceClearings;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $geofences;


    /**
     * Add geofence
     *
     * @param \LocationBundle\Entity\Location $geofence
     *
     * @return Employee
     */
    public function addGeofence(\LocationBundle\Entity\Location $geofence)
    {
        $this->geofences[] = $geofence;

        return $this;
    }

    /**
     * Remove geofence
     *
     * @param \LocationBundle\Entity\Location $geofence
     */
    public function removeGeofence(\LocationBundle\Entity\Location $geofence)
    {
        $this->geofences->removeElement($geofence);
    }

    /**
     * Get geofences
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGeofences()
    {
        return $this->geofences;
    }

    public function isProvenexpense($date, \ExpenseBundle\Repository\ProvenexpenseRepository $provenexpense)
    {
        $em = $provenexpense;
        $current = Carbon::instance($date);
        $monthstart = new Carbon($current->startOfMonth());
        $monthend = new Carbon($current->endOfMonth());
        $provenexpense = $em->findBy(array(
            'startdate' => $monthstart,
            'enddate' => $monthend,
            'employee' => $this
        ));
        // $provenexpense = $em->findLast($this, $monthstart->toDateString(), $monthend->toDateString());

        if ($provenexpense) {
            return true ;
        }
        return false;
    }

    public function getHoliday($year)
    {
        $holidayNew = is_null($this->contract) ? 0 : $this->contract->getHolidays();
        $remainingOld = $this->getRemainingDaysOfVacation();

        $substract = 0;
        $additional = 0;

        foreach ($this->getAbsenceClearings() as $absenceClearing) {
            if ($absenceClearing->getYear() == 2018) {
                $substract = $absenceClearing->getSubstractDaysOfVacation();
                $additional = $absenceClearing->getAdditionalDaysOfVacation();
                $remainingOld = (empty($absenceClearing->getRemainingDaysOfVacation())?$remainingOld:$absenceClearing->getRemainingDaysOfVacation());
            }
        }

        $holiday = $holidayNew + $remainingOld - $substract + $additional;
        return $holiday;
    }

    public function getHolidayByYear($year)
    {
        $holidayNew = is_null($this->contract) ? 0 : $this->contract->getHolidays();
        if ($this->isDeleted() && $year > intval($this->getDeletedAt()->format('Y'))) {
            $holidayNew = 0;
        }
        $remainingOld = $this->getRemainingDaysOfVacation();

        $substract = 0;
        $additional = 0;

        foreach ($this->getAbsenceClearings() as $absenceClearing) {
            if ($absenceClearing->getYear() == $year) {
                $substract = $absenceClearing->getSubstractDaysOfVacation();
                $additional = $absenceClearing->getAdditionalDaysOfVacation();
                $remainingOld = (empty($absenceClearing->getRemainingDaysOfVacation())?$remainingOld:$absenceClearing->getRemainingDaysOfVacation());
            }
        }

        $holiday = $holidayNew + $remainingOld - $substract + $additional;
        return $holiday;
    }
    /**
     * @var \DateTime
     */
    private $entry_date;


    /**
     * Set entryDate
     *
     * @param \DateTime $entryDate
     *
     * @return Employee
     */
    public function setEntryDate($entryDate)
    {
        $this->entry_date = $entryDate;

        return $this;
    }

    /**
     * Get entryDate
     *
     * @return \DateTime
     */
    public function getEntryDate()
    {
        return $this->entry_date;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $consumption;


    /**
     * Add consumption
     *
     * @param \VehicleBundle\Entity\Consumption $consumption
     *
     * @return Employee
     */
    public function addConsumption(\VehicleBundle\Entity\Consumption $consumption)
    {
        $this->consumption[] = $consumption;

        return $this;
    }

    /**
     * Remove consumption
     *
     * @param \VehicleBundle\Entity\Consumption $consumption
     */
    public function removeConsumption(\VehicleBundle\Entity\Consumption $consumption)
    {
        $this->consumption->removeElement($consumption);
    }

    /**
     * Get consumption
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConsumption()
    {
        return $this->consumption;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $consumptions;


    /**
     * Get consumptions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConsumptions()
    {
        return $this->consumptions;
    }

    public function getHolidaysYearly($date)
    {
        global $kernel;
        $em = $kernel->getContainer()->get('doctrine')->getManager();

        $start = new Carbon($date);
        $end = new Carbon($date);
        $month = $start->month;
        $start = $start->startOfYear()->format('Y-m-d');
        $end = $end->endOfMonth()->format('Y-m-d');
        $test = $em->getRepository('AbsenceBundle:Absence')->getAbsencesBetweenByEmployeeID($this, $start, $end);

        $new = array();
        foreach ($test as $t) {
            array_push($new, $em->getRepository('AbsenceBundle:Absence')->getSplittedInMonthsHolidaysByEmployeeDate($this, ($t['fromDate'])->format('Y-m-d'), ($t['toDate'])->format('Y-m-d')));
        }
        $used = 0;
        foreach ($new as $n) {
            foreach ($n as $x) {
                if ($x['month'] <= $month) {
                    $used += floatval($x['days']);
                }
            }
        }
        return $used;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $employeeArchivs;


    /**
     * Add employeeArchiv
     *
     * @param \EmployeeBundle\Entity\EmployeeArchiv $employeeArchiv
     *
     * @return Employee
     */
    public function addEmployeeArchiv(\EmployeeBundle\Entity\EmployeeArchiv $employeeArchiv)
    {
        $this->employeeArchivs[] = $employeeArchiv;

        return $this;
    }
    /**
     * Remove employeeArchiv
     *
     * @param \EmployeeBundle\Entity\EmployeeArchiv $employeeArchiv
     */
    public function removeEmployeeArchiv(\EmployeeBundle\Entity\EmployeeArchiv $employeeArchiv)
    {
        $this->employeeArchivs->removeElement($employeeArchiv);
    }

    /**
     * Get employeeArchivs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmployeeArchivs()
    {
        return $this->employeeArchivs;
    }
    /**
      * @var string
      */
      private $phonePrivate;

      /**
      * Set phonePrivate
      *
      * @param string $phonePrivate
      *
      * @return Employee
      */
      public function setPhonePrivate($phonePrivate)
      {
        $this->phonePrivate = $phonePrivate;

        return $this;
      }

      /**
      * Get phonePrivate
      *
      * @return string
      */
      public function getPhonePrivate()
      {
        return $this->phonePrivate;
      }
      /**
     * @var string
     */
     private $emailPrivate;

    /**
     * Set emailPrivate
     *
     * @param string $emailPrivate
     *
     * @return Employee
     */
    public function setEmailPrivate($emailPrivate)
    {
        $this->emailPrivate = $emailPrivate;
        return $this;
    }

    /**
     * Get emailPrivate
     *
     * @return string
     */
    public function getEmailPrivate()
    {
        return $this->emailPrivate;
    }


/**
 * @var \Doctrine\Common\Collections\Collection
 */
private $messagess;

/**
 * Add messages
 *
 * @param \MessageBundle\Entity\Messages $messages
 *
 * @return Employee
 */

public function addMessages(\MessageBundle\Entity\Messages $messages)
{
    $this->$messagess[] = $messages;
    return $this;
}

/**
 * Remove messages
 *
 * @param \MessageBundle\Entity\Messages $messages
 */
public function removeMessages(\MessageBundle\Entity\Messages $messages)
{
    $this->$messagess->removeElement($messages);
}

/**
 * Get messages
 *
 * @return \Doctrine\Common\Collections\Collection
 */
public function getMessagess()
{
    return $this->messagess;
}



}
