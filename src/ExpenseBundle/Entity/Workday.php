<?php

namespace ExpenseBundle\Entity;

/**
 * Workday
 */
class Workday
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \DateTime
     */
    private $workdaybegin;

    /**
     * @var \DateTime
     */
    private $workdayend;

    /**
     * @var string
     */
    private $workdayBeginLat;

    /**
     * @var string
     */
    private $workdayBeginLon;

    /**
     * @var string
     */
    private $workdayEndLat;

    /**
     * @var string
     */
    private $workdayEndLon;

    /**
     * @var bool
     */
    private $accommodation;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Workday
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set workdaybegin
     *
     * @param \DateTime $workdaybegin
     *
     * @return Workday
     */
    public function setWorkdaybegin($workdaybegin)
    {
        $this->workdaybegin = $workdaybegin;

        return $this;
    }

    /**
     * Get workdaybegin
     *
     * @return \DateTime
     */
    public function getWorkdaybegin()
    {
        return $this->workdaybegin;
    }

    /**
     * Set workdayend
     *
     * @param \DateTime $workdayend
     *
     * @return Workday
     */
    public function setWorkdayend($workdayend)
    {
        $this->workdayend = $workdayend;

        return $this;
    }

    /**
     * Get workdayend
     *
     * @return \DateTime
     */
    public function getWorkdayend()
    {
        return $this->workdayend;
    }

    /**
     * Set workdayBeginLat
     *
     * @param string $workdayBeginLat
     *
     * @return Workday
     */
    public function setWorkdayBeginLat($workdayBeginLat)
    {
        $this->workdayBeginLat = $workdayBeginLat;

        return $this;
    }

    /**
     * Get workdayBeginLat
     *
     * @return string
     */
    public function getWorkdayBeginLat()
    {
        return $this->workdayBeginLat;
    }

    /**
     * Set workdayBeginLon
     *
     * @param string $workdayBeginLon
     *
     * @return Workday
     */
    public function setWorkdayBeginLon($workdayBeginLon)
    {
        $this->workdayBeginLon = $workdayBeginLon;

        return $this;
    }

    /**
     * Get workdayBeginLon
     *
     * @return string
     */
    public function getWorkdayBeginLon()
    {
        return $this->workdayBeginLon;
    }

    /**
     * Set workdayEndLat
     *
     * @param string $workdayEndLat
     *
     * @return Workday
     */
    public function setWorkdayEndLat($workdayEndLat)
    {
        $this->workdayEndLat = $workdayEndLat;

        return $this;
    }

    /**
     * Get workdayEndLat
     *
     * @return string
     */
    public function getWorkdayEndLat()
    {
        return $this->workdayEndLat;
    }

    /**
     * Set workdayEndLon
     *
     * @param string $workdayEndLon
     *
     * @return Workday
     */
    public function setWorkdayEndLon($workdayEndLon)
    {
        $this->workdayEndLon = $workdayEndLon;

        return $this;
    }

    /**
     * Get workdayEndLon
     *
     * @return string
     */
    public function getWorkdayEndLon()
    {
        return $this->workdayEndLon;
    }

    /**
     * Set accommodation
     *
     * @param boolean $accommodation
     *
     * @return Workday
     */
    public function setAccommodation($accommodation)
    {
        $this->accommodation = $accommodation;

        return $this;
    }

    /**
     * Get accommodation
     *
     * @return bool
     */
    public function getAccommodation()
    {
        return $this->accommodation;
    }
    /**
     * @var \DateTime
     */
    private $workdayBeginTime;

    /**
     * @var \DateTime
     */
    private $workdayEndTime;

    /**
     * @var boolean
     */
    private $workdayBeginHome;

    /**
     * @var boolean
     */
    private $workdayEndHome;

    /**
     * @var \VehicleBundle\Entity\Vehicle
     */
    private $truck;

    /**
     * @var \VehicleBundle\Entity\Vehicle
     */
    private $car;


    /**
     * Set workdayBeginTime
     *
     * @param \DateTime $workdayBeginTime
     *
     * @return Workday
     */
    public function setWorkdayBeginTime($workdayBeginTime)
    {
        $this->workdayBeginTime = $workdayBeginTime;

        return $this;
    }

    /**
     * Get workdayBeginTime
     *
     * @return \DateTime
     */
    public function getWorkdayBeginTime()
    {
        return $this->workdayBeginTime;
    }

    /**
     * Set workdayEndTime
     *
     * @param \DateTime $workdayEndTime
     *
     * @return Workday
     */
    public function setWorkdayEndTime($workdayEndTime)
    {
        $this->workdayEndTime = $workdayEndTime;

        return $this;
    }

    /**
     * Get workdayEndTime
     *
     * @return \DateTime
     */
    public function getWorkdayEndTime()
    {
        return $this->workdayEndTime;
    }

    /**
     * Set workdayBeginHome
     *
     * @param boolean $workdayBeginHome
     *
     * @return Workday
     */
    public function setWorkdayBeginHome($workdayBeginHome)
    {
        $this->workdayBeginHome = $workdayBeginHome;

        return $this;
    }

    /**
     * Get workdayBeginHome
     *
     * @return boolean
     */
    public function getWorkdayBeginHome()
    {
        return $this->workdayBeginHome;
    }

    /**
     * Set workdayEndHome
     *
     * @param boolean $workdayEndHome
     *
     * @return Workday
     */
    public function setWorkdayEndHome($workdayEndHome)
    {
        $this->workdayEndHome = $workdayEndHome;

        return $this;
    }

    /**
     * Get workdayEndHome
     *
     * @return boolean
     */
    public function getWorkdayEndHome()
    {
        return $this->workdayEndHome;
    }

    /**
     * Set truck
     *
     * @param \VehicleBundle\Entity\Vehicle $truck
     *
     * @return Workday
     */
    public function setTruck(\VehicleBundle\Entity\Vehicle $truck = null)
    {
        $this->truck = $truck;

        return $this;
    }

    /**
     * Get truck
     *
     * @return \VehicleBundle\Entity\Vehicle
     */
    public function getTruck()
    {
        return $this->truck;
    }

    /**
     * Set car
     *
     * @param \VehicleBundle\Entity\Vehicle $car
     *
     * @return Workday
     */
    public function setCar(\VehicleBundle\Entity\Vehicle $car = null)
    {
        $this->car = $car;

        return $this;
    }

    /**
     * Get car
     *
     * @return \VehicleBundle\Entity\Vehicle
     */
    public function getCar()
    {
        return $this->car;
    }
    /**
     * @var \EmployeeBundle\Entity\Employee
     */
    private $employee;


    /**
     * Set employee
     *
     * @param \EmployeeBundle\Entity\Employee $employee
     *
     * @return Workday
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
     * @var \ExpenseBundle\Entity\Countryspecificexpenses
     */
    private $country;


    /**
     * Set country
     *
     * @param \ExpenseBundle\Entity\Countryspecificexpenses $country
     *
     * @return Workday
     */
    public function setCountry(\ExpenseBundle\Entity\Countryspecificexpenses $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \ExpenseBundle\Entity\Countryspecificexpenses
     */
    public function getCountry()
    {
        return $this->country;
    }
    /**
     * @var \AbsenceBundle\Entity\Status
     */
    private $status;


    /**
     * Set status
     *
     * @param \AbsenceBundle\Entity\Status $status
     *
     * @return Workday
     */
    public function setStatus(\AbsenceBundle\Entity\Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \AbsenceBundle\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }
    /**
     * @var string
     */
    private $comment;


    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Workday
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
}
