<?php

namespace EmployeeBundle\Entity;

/**
 * AbsenceClearing
 */
class AbsenceClearing
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $remainingDaysOfVacation;

    /**
     * @var string
     */
    private $substractDaysOfVacation;

    /**
     * @var string
     */
    private $additionalDaysOfVacation;

    /**
     * @var int
     */
    private $year;


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
     * Set remainingDaysOfVacation
     *
     * @param string $remainingDaysOfVacation
     *
     * @return AbsenceClearing
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
     * Set substractDaysOfVacation
     *
     * @param string $substractDaysOfVacation
     *
     * @return AbsenceClearing
     */
    public function setSubstractDaysOfVacation($substractDaysOfVacation)
    {
        $this->substractDaysOfVacation = $substractDaysOfVacation;

        return $this;
    }

    /**
     * Get substractDaysOfVacation
     *
     * @return string
     */
    public function getSubstractDaysOfVacation()
    {
        return $this->substractDaysOfVacation;
    }

    /**
     * Set additionalDaysOfVacation
     *
     * @param string $additionalDaysOfVacation
     *
     * @return AbsenceClearing
     */
    public function setAdditionalDaysOfVacation($additionalDaysOfVacation)
    {
        $this->additionalDaysOfVacation = $additionalDaysOfVacation;

        return $this;
    }

    /**
     * Get additionalDaysOfVacation
     *
     * @return string
     */
    public function getAdditionalDaysOfVacation()
    {
        return $this->additionalDaysOfVacation;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return AbsenceClearing
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
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
     * @return AbsenceClearing
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
     * @var string
     */
    private $comment;


    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return AbsenceClearing
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
    /**
     * @var string
     */
    private $comment2;


    /**
     * Set comment2
     *
     * @param string $comment2
     *
     * @return AbsenceClearing
     */
    public function setComment2($comment2)
    {
        $this->comment2 = $comment2;

        return $this;
    }

    /**
     * Get comment2
     *
     * @return string
     */
    public function getComment2()
    {
        return $this->comment2;
    }

    public function __toString(){
        return (String) $this->employee;
    }
}
