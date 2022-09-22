<?php

namespace ExpenseBundle\Entity;

/**
 * Provenexpense
 */
class Provenexpense
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $startdate;

    /**
     * @var \DateTime
     */
    private $enddate;


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
     * Set startdate
     *
     * @param \DateTime $startdate
     *
     * @return Provenexpense
     */
    public function setStartdate($startdate)
    {
        $this->startdate = $startdate;

        return $this;
    }

    /**
     * Get startdate
     *
     * @return \DateTime
     */
    public function getStartdate()
    {
        return $this->startdate;
    }

    /**
     * Set enddate
     *
     * @param \DateTime $enddate
     *
     * @return Provenexpense
     */
    public function setEnddate($enddate)
    {
        $this->enddate = $enddate;

        return $this;
    }

    /**
     * Get enddate
     *
     * @return \DateTime
     */
    public function getEnddate()
    {
        return $this->enddate;
    }
    /**
     * @var \DateTime
     */
    private $prooveTime;

    /**
     * @var \DateTime
     */
    private $approoveTime;

    /**
     * @var \EmployeeBundle\Entity\Employee
     */
    private $employee;

    /**
     * @var \EmployeeBundle\Entity\Employee
     */
    private $approvedBy;


    /**
     * Set prooveTime
     *
     * @param \DateTime $prooveTime
     *
     * @return Provenexpense
     */
    public function setProoveTime($prooveTime)
    {
        $this->prooveTime = $prooveTime;

        return $this;
    }

    /**
     * Get prooveTime
     *
     * @return \DateTime
     */
    public function getProoveTime()
    {
        return $this->prooveTime;
    }

    /**
     * Set approoveTime
     *
     * @param \DateTime $approoveTime
     *
     * @return Provenexpense
     */
    public function setApprooveTime($approoveTime)
    {
        $this->approoveTime = $approoveTime;

        return $this;
    }

    /**
     * Get approoveTime
     *
     * @return \DateTime
     */
    public function getApprooveTime()
    {
        return $this->approoveTime;
    }

    /**
     * Set employee
     *
     * @param \EmployeeBundle\Entity\Employee $employee
     *
     * @return Provenexpense
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
     * Set approvedBy
     *
     * @param \EmployeeBundle\Entity\Employee $approvedBy
     *
     * @return Provenexpense
     */
    public function setApprovedBy(\EmployeeBundle\Entity\Employee $approvedBy = null)
    {
        $this->approvedBy = $approvedBy;

        return $this;
    }

    /**
     * Get approvedBy
     *
     * @return \EmployeeBundle\Entity\Employee
     */
    public function getApprovedBy()
    {
        return $this->approvedBy;
    }

    public function __toString()
    {
      return (String) ' von '.$this->employee.' für den Monat '.$this->startdate->format('F').' um '.(String)$this->prooveTime->format('H:i');
    }
}
