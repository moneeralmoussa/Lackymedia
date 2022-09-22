<?php

namespace EmployeeBundle\Entity;

/**
 * EmployeeArchiv
 */
class EmployeeArchiv
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $komalogId;

    /**
     * @var string
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
     * @var \DateTime
     */
    private $entry_date;

    /**
     * @var string
     */
    private $initial;

    /**
     * @var \DateTime
     */
    private $deleted_at;

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
     * @var boolean
     */
    private $sleepsInCompanyMeansSleepsAtHome;

    /**
     * @var string
     */
    private $salary;

    /**
     * @var string
     */
    private $remainingDaysOfVacation;

    /**
     * @var string
     */
    private $usualHomeTravelHours;

    /**
     * @var \EmployeeBundle\Entity\Employee
     */
    private $employee;

    /**
     * @var \EmployeeBundle\Entity\Department
     */
    private $department;

    /**
     * @var \EmployeeBundle\Entity\Contract
     */
    private $contract;


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
     * @return EmployeeArchiv
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
     * @param string $trimbleId
     *
     * @return EmployeeArchiv
     */
    public function setTrimbleId($trimbleId)
    {
        $this->trimbleId = $trimbleId;

        return $this;
    }

    /**
     * Get trimbleId
     *
     * @return string
     */
    public function getTrimbleId()
    {
        return $this->trimbleId;
    }

    /**
     * Set countryCode
     *
     * @param string $countryCode
     *
     * @return EmployeeArchiv
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
     * @return EmployeeArchiv
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
     * @return EmployeeArchiv
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
     * @return EmployeeArchiv
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
     * @return EmployeeArchiv
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
     * @return EmployeeArchiv
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
     * @return EmployeeArchiv
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

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return EmployeeArchiv
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
     * @return EmployeeArchiv
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
     * @return EmployeeArchiv
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
     * @return EmployeeArchiv
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
     * @return EmployeeArchiv
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
     * Set entryDate
     *
     * @param \DateTime $entryDate
     *
     * @return EmployeeArchiv
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
     * Set initial
     *
     * @param string $initial
     *
     * @return EmployeeArchiv
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
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return EmployeeArchiv
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

    /**
     * Set lat
     *
     * @param string $lat
     *
     * @return EmployeeArchiv
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
     * @return EmployeeArchiv
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
     * @return EmployeeArchiv
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
     * Set sleepsInCompanyMeansSleepsAtHome
     *
     * @param boolean $sleepsInCompanyMeansSleepsAtHome
     *
     * @return EmployeeArchiv
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
     * @return EmployeeArchiv
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

    /**
     * Set remainingDaysOfVacation
     *
     * @param string $remainingDaysOfVacation
     *
     * @return EmployeeArchiv
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
        return $this->remainingDaysOfVacation;
    }

    /**
     * Set usualHomeTravelHours
     *
     * @param string $usualHomeTravelHours
     *
     * @return EmployeeArchiv
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
     * Set employee
     *
     * @param \EmployeeBundle\Entity\Employee $employee
     *
     * @return EmployeeArchiv
     */
    public function setEmployee(\EmployeeBundle\Entity\Employee $employee = null)
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * Get employee
     *
     * @return \EmployeeBundle\Entity\Employee
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * Set department
     *
     * @param \EmployeeBundle\Entity\Department $department
     *
     * @return EmployeeArchiv
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

    /**
     * Set contract
     *
     * @param \EmployeeBundle\Entity\Contract $contract
     *
     * @return EmployeeArchiv
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
}
