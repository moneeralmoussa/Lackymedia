<?php

namespace EmployeeBundle\Entity;

/**
 * Contract
 */
class Contract
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $holidays;


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
     * Set holidays
     *
     * @param integer $holidays
     *
     * @return Contract
     */
    public function setHolidays($holidays)
    {
        if (!is_null($this->parent) && $this->parent->getHolidays() === $holidays) {
            $this->holidays = null;
        } else {
            $this->holidays = $holidays;
        }

        return $this;
    }

    /**
     * Get holidays
     *
     * @return int
     */
    public function getHolidays()
    {
        if (!is_null($this->holidays)) {
            $retval = $this->holidays;
        } elseif (!is_null($this->parent)){
            $retval = $this->parent->getHolidays();
        } else {
            $retval = null;
        }
        return $retval;
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
     * @return Contract
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

    public function __toString()
    {
        return (string) $this->getName();
    }
    /**
     * @var string
     */
    private $name;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return Contract
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
     * @var string
     */
    private $weeklyHoursOfWork;

    /**
     * @var boolean
     */
    private $standby;

    /**
     * @var string
     */
    private $additionalExpenses8h;

    /**
     * @var string
     */
    private $additionalExpenses24h;

    /**
     * @var string
     */
    private $trainingOvertimePremium;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $children;

    /**
     * @var \EmployeeBundle\Entity\Contract
     */
    private $parent;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set weeklyHoursOfWork
     *
     * @param string $weeklyHoursOfWork
     *
     * @return Contract
     */
    public function setWeeklyHoursOfWork($weeklyHoursOfWork)
    {
        if (!is_null($this->parent) && $this->parent->getWeeklyHoursOfWork() === $weeklyHoursOfWork) {
            $this->weeklyHoursOfWork = null;
        } else {
            $this->weeklyHoursOfWork = $weeklyHoursOfWork;
        }

        return $this;
    }

    /**
     * Get weeklyHoursOfWork
     *
     * @return string
     */
    public function getWeeklyHoursOfWork()
    {
        if (!is_null($this->weeklyHoursOfWork)) {
            $retval = $this->weeklyHoursOfWork;
        } elseif (!is_null($this->parent)){
            $retval = $this->parent->getWeeklyHoursOfWork();
        } else {
            $retval = null;
        }
        return $retval;
    }

    /**
     * Set standby
     *
     * @param boolean $standby
     *
     * @return Contract
     */
    public function setStandby($standby)
    {
        if (!is_null($this->parent) && $this->parent->getStandby() === $standby) {
            $this->standby = null;
        } else {
            $this->standby = $standby;
        }

        return $this;
    }

    /**
     * Get standby
     *
     * @return boolean
     */
    public function getStandby()
    {
        if (!is_null($this->standby)) {
            $retval = $this->standby;
        } elseif (!is_null($this->parent)){
            $retval = $this->parent->getStandby();
        } else {
            $retval = null;
        }
        return $retval;
    }

    /**
     * Set additionalExpenses8h
     *
     * @param string $additionalExpenses8h
     *
     * @return Contract
     */
    public function setAdditionalExpenses8h($additionalExpenses8h)
    {
        if (!is_null($this->parent) && $this->parent->getAdditionalExpenses8h() === $additionalExpenses8h) {
            $this->additionalExpenses8h = null;
        } else {
            $this->additionalExpenses8h = $additionalExpenses8h;
        }

        return $this;
    }

    /**
     * Get additionalExpenses8h
     *
     * @return string
     */
    public function getAdditionalExpenses8h()
    {
        if (!is_null($this->additionalExpenses8h)) {
            $retval = $this->additionalExpenses8h;
        } elseif (!is_null($this->parent)){
            $retval = $this->parent->getAdditionalExpenses8h();
        } else {
            $retval = null;
        }
        return $retval;
    }

    /**
     * Set additionalExpenses24h
     *
     * @param string $additionalExpenses24h
     *
     * @return Contract
     */
    public function setAdditionalExpenses24h($additionalExpenses24h)
    {
        if (!is_null($this->parent) && $this->parent->getAdditionalExpenses24h() === $additionalExpenses24h) {
            $this->additionalExpenses24h = null;
        } else {
            $this->additionalExpenses24h = $additionalExpenses24h;
        }

        return $this;
    }

    /**
     * Get additionalExpenses24h
     *
     * @return string
     */
    public function getAdditionalExpenses24h()
    {
        if (!is_null($this->additionalExpenses24h)) {
            $retval = $this->additionalExpenses24h;
        } elseif (!is_null($this->parent)){
            $retval = $this->parent->getAdditionalExpenses24h();
        } else {
            $retval = null;
        }
        return $retval;
    }

    /**
     * Set trainingOvertimePremium
     *
     * @param string $trainingOvertimePremium
     *
     * @return Contract
     */
    public function setTrainingOvertimePremium($trainingOvertimePremium)
    {
        if (!is_null($this->parent) && $this->parent->getTrainingOvertimePremium() === $trainingOvertimePremium) {
            $this->trainingOvertimePremium = null;
        } else {
            $this->trainingOvertimePremium = $trainingOvertimePremium;
        }

        return $this;
    }

    /**
     * Get trainingOvertimePremium
     *
     * @return string
     */
    public function getTrainingOvertimePremium()
    {
        if (!is_null($this->trainingOvertimePremium)) {
            $retval = $this->trainingOvertimePremium;
        } elseif (!is_null($this->parent)){
            $retval = $this->parent->getTrainingOvertimePremium();
        } else {
            $retval = null;
        }
        return $retval;
    }

    /**
     * Add child
     *
     * @param \EmployeeBundle\Entity\Contract $child
     *
     * @return Contract
     */
    public function addChild(\EmployeeBundle\Entity\Contract $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \EmployeeBundle\Entity\Contract $child
     */
    public function removeChild(\EmployeeBundle\Entity\Contract $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \EmployeeBundle\Entity\Contract $parent
     *
     * @return Contract
     */
    public function setParent(\EmployeeBundle\Entity\Contract $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \EmployeeBundle\Entity\Contract
     */
    public function getParent()
    {
        return $this->parent;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $workingtimes;


    /**
     * Add workingtime
     *
     * @param \EmployeeBundle\Entity\Workingtime $workingtime
     *
     * @return Contract
     */
    public function addWorkingtime(\EmployeeBundle\Entity\Workingtime $workingtime)
    {
        $this->workingtimes[] = $workingtime;

        return $this;
    }

    /**
     * Remove workingtime
     *
     * @param \EmployeeBundle\Entity\Workingtime $workingtime
     */
    public function removeWorkingtime(\EmployeeBundle\Entity\Workingtime $workingtime)
    {
        $this->workingtimes->removeElement($workingtime);
    }

    /**
     * Get workingtimes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWorkingtimes()
    {
        return $this->workingtimes;
    }
    /**
     * @var string
     */
    private $vacationalBenefit;


    /**
     * Set vacationalBenefit
     *
     * @param string $vacationalBenefit
     *
     * @return Contract
     */
    public function setVacationalBenefit($vacationalBenefit)
    {
        if (!is_null($this->parent) && $this->parent->getVacationalBenefit() === $vacationalBenefit) {
            $this->vacationalBenefit = null;
        } else {
            $this->vacationalBenefit = $vacationalBenefit;
        }

        return $this;
    }

    /**
     * Get vacationalBenefit
     *
     * @return string
     */
    public function getVacationalBenefit()
    {
        if (!is_null($this->vacationalBenefit)) {
            $retval = $this->vacationalBenefit;
        } elseif (!is_null($this->parent)){
            $retval = $this->parent->getVacationalBenefit();
        } else {
            $retval = null;
        }
        return $retval;
    }
}
