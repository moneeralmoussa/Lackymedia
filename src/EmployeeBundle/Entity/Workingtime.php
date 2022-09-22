<?php

namespace EmployeeBundle\Entity;

/**
 * Workingtime
 */
class Workingtime
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $dayOfWeek;

    /**
     * @var \DateTime
     */
    private $workBegin;

    /**
     * @var \DateTime
     */
    private $workEnd;

    /**
     * @var \DateTime
     */
    private $breakBegin;

    /**
     * @var \DateTime
     */
    private $breakEnd;

    /**
     * @var string
     */
    private $overtimePremium;

    /**
     * @var string
     */
    private $overtimePremiumPassenger;

    /**
     * @var bool
     */
    private $overtimePremiumIsBrutto;

    /**
     * @var string
     */
    private $specialProvision;

    /**
     * @var bool
     */
    private $school;

    /**
     * @var \DateTime
     */
    private $schoolBegin;

    /**
     * @var \DateTime
     */
    private $schoolEnd;


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
     * Set dayOfWeek
     *
     * @param integer $dayOfWeek
     *
     * @return Workingtime
     */
    public function setDayOfWeek($dayOfWeek)
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    /**
     * Get dayOfWeek
     *
     * @return int
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    /**
     * Get dayOfWeek
     *
     * @return int
     */
    public function getDayOfWeekText()
    {
        switch ($this->dayOfWeek) {
            case 1:
                $retval = 'Montag';
                break;
            case 2:
                $retval = 'Dienstag';
                break;
            case 3:
                $retval = 'Mittwoch';
                break;
            case 4:
                $retval = 'Donnerstag';
                break;
            case 5:
                $retval = 'Freitag';
                break;
            case 6:
                $retval = 'Samstag';
                break;
            case 7:
                $retval = 'Sonntag';
                break;
        }
        return $retval;
    }

    /**
     * Set workBegin
     *
     * @param \DateTime $workBegin
     *
     * @return Workingtime
     */
    public function setWorkBegin($workBegin)
    {
        $this->workBegin = $workBegin;

        return $this;
    }

    /**
     * Get workBegin
     *
     * @return \DateTime
     */
    public function getWorkBegin()
    {
        return $this->workBegin;
    }

    /**
     * Set workEnd
     *
     * @param \DateTime $workEnd
     *
     * @return Workingtime
     */
    public function setWorkEnd($workEnd)
    {
        $this->workEnd = $workEnd;

        return $this;
    }

    /**
     * Get workEnd
     *
     * @return \DateTime
     */
    public function getWorkEnd()
    {
        return $this->workEnd;
    }

    /**
     * Set breakBegin
     *
     * @param \DateTime $breakBegin
     *
     * @return Workingtime
     */
    public function setBreakBegin($breakBegin)
    {
        $this->breakBegin = $breakBegin;

        return $this;
    }

    /**
     * Get breakBegin
     *
     * @return \DateTime
     */
    public function getBreakBegin()
    {
        return $this->breakBegin;
    }

    /**
     * Set breakEnd
     *
     * @param \DateTime $breakEnd
     *
     * @return Workingtime
     */
    public function setBreakEnd($breakEnd)
    {
        $this->breakEnd = $breakEnd;

        return $this;
    }

    /**
     * Get breakEnd
     *
     * @return \DateTime
     */
    public function getBreakEnd()
    {
        return $this->breakEnd;
    }

    /**
     * Set overtimePremium
     *
     * @param string $overtimePremium
     *
     * @return Workingtime
     */
    public function setOvertimePremium($overtimePremium)
    {
        $this->overtimePremium = $overtimePremium;

        return $this;
    }

    /**
     * Get overtimePremium
     *
     * @return string
     */
    public function getOvertimePremium()
    {
        return $this->overtimePremium;
    }

    /**
     * Set overtimePremiumPassenger
     *
     * @param string $overtimePremiumPassenger
     *
     * @return Workingtime
     */
    public function setOvertimePremiumPassenger($overtimePremiumPassenger)
    {
        $this->overtimePremiumPassenger = $overtimePremiumPassenger;

        return $this;
    }

    /**
     * Get overtimePremiumPassenger
     *
     * @return string
     */
    public function getOvertimePremiumPassenger()
    {
        return $this->overtimePremiumPassenger;
    }

    /**
     * Set overtimePremiumIsBrutto
     *
     * @param boolean $overtimePremiumIsBrutto
     *
     * @return Workingtime
     */
    public function setOvertimePremiumIsBrutto($overtimePremiumIsBrutto)
    {
        $this->overtimePremiumIsBrutto = $overtimePremiumIsBrutto;

        return $this;
    }

    /**
     * Get overtimePremiumIsBrutto
     *
     * @return bool
     */
    public function getOvertimePremiumIsBrutto()
    {
        return $this->overtimePremiumIsBrutto;
    }

    /**
     * Set specialProvision
     *
     * @param string $specialProvision
     *
     * @return Workingtime
     */
    public function setSpecialProvision($specialProvision)
    {
        $this->specialProvision = $specialProvision;

        return $this;
    }

    /**
     * Get specialProvision
     *
     * @return string
     */
    public function getSpecialProvision()
    {
        return $this->specialProvision;
    }

    /**
     * Set school
     *
     * @param boolean $school
     *
     * @return Workingtime
     */
    public function setSchool($school)
    {
        $this->school = $school;

        return $this;
    }

    /**
     * Get school
     *
     * @return bool
     */
    public function getSchool()
    {
        return $this->school;
    }

    /**
     * Set schoolBegin
     *
     * @param \DateTime $schoolBegin
     *
     * @return Workingtime
     */
    public function setSchoolBegin($schoolBegin)
    {
        $this->schoolBegin = $schoolBegin;

        return $this;
    }

    /**
     * Get schoolBegin
     *
     * @return \DateTime
     */
    public function getSchoolBegin()
    {
        return $this->schoolBegin;
    }

    /**
     * Set schoolEnd
     *
     * @param \DateTime $schoolEnd
     *
     * @return Workingtime
     */
    public function setSchoolEnd($schoolEnd)
    {
        $this->schoolEnd = $schoolEnd;

        return $this;
    }

    /**
     * Get schoolEnd
     *
     * @return \DateTime
     */
    public function getSchoolEnd()
    {
        return $this->schoolEnd;
    }
    /**
     * @var \EmployeeBundle\Entity\Contract
     */
    private $contract;


    /**
     * Set contract
     *
     * @param \EmployeeBundle\Entity\Contract $contract
     *
     * @return Workingtime
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
