<?php

namespace AbsenceBundle\Entity;

/**
 * AbsenceDetailClearing
 */
class AbsenceDetailClearing
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $employee;


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
     * Set employee
     *
     * @param integer $employee
     *
     * @return AbsenceDetailClearing
     */
    public function setEmployee($employee)
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * Get employee
     *
     * @return int
     */
    public function getEmployee()
    {
        return $this->employee;
    }
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $dayDetail = 0;


    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return AbsenceDetailClearing
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
     * Set dayDetail
     *
     * @param string $dayDetail
     *
     * @return AbsenceDetailClearing
     */
    public function setDayDetail($dayDetail)
    {
        $this->dayDetail = $dayDetail;

        return $this;
    }

    /**
     * Get dayDetail
     *
     * @return string
     */
    public function getDayDetail()
    {
        return $this->dayDetail;
    }

    public function transformCheckboxToDayDetail($checkbox)
    {
        switch ($checkbox) {
            case "1":
                return 1;
                break;
            case "2":
                return 0.5;
                break;
            case "3":
                return 0;
                break;
        }
    }
    /**
     * @var \DateTime
     */
    private $createdat;

    /**
     * @var \DateTime
     */
    private $updatedat;

    /**
     * @var \AbsenceBundle\Entity\Reason
     */
    private $reason;


    /**
     * Set createdat
     *
     * @param \DateTime $createdat
     *
     * @return AbsenceDetailClearing
     */
    public function setCreatedat($createdat)
    {
        $this->createdat = $createdat;

        return $this;
    }

    /**
     * Get createdat
     *
     * @return \DateTime
     */
    public function getCreatedat()
    {
        return $this->createdat;
    }

    /**
     * Set updatedat
     *
     * @param \DateTime $updatedat
     *
     * @return AbsenceDetailClearing
     */
    public function setUpdatedat($updatedat)
    {
        $this->updatedat = $updatedat;

        return $this;
    }

    /**
     * Get updatedat
     *
     * @return \DateTime
     */
    public function getUpdatedat()
    {
        return $this->updatedat;
    }

    /**
     * Set reason
     *
     * @param \AbsenceBundle\Entity\Reason $reason
     *
     * @return AbsenceDetailClearing
     */
    public function setReason(\AbsenceBundle\Entity\Reason $reason = null)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return \AbsenceBundle\Entity\Reason
     */
    public function getReason()
    {
        return $this->reason;
    }
    /**
     * @var \AbsenceBundle\Entity\Absence
     */
    private $absence;


    /**
     * Set absence
     *
     * @param \AbsenceBundle\Entity\Absence $absence
     *
     * @return AbsenceDetailClearing
     */
    public function setAbsence(\AbsenceBundle\Entity\Absence $absence = null)
    {
        $this->absence = $absence;

        return $this;
    }

    /**
     * Get absence
     *
     * @return \AbsenceBundle\Entity\Absence
     */
    public function getAbsence()
    {
        return $this->absence;
    }
    /**
     * @var boolean
     */
    private $UseAsHolidays;


    /**
     * Set useAsHolidays
     *
     * @param boolean $useAsHolidays
     *
     * @return AbsenceDetailClearing
     */
    public function setUseAsHolidays($useAsHolidays)
    {
        $this->UseAsHolidays = $useAsHolidays;

        return $this;
    }

    /**
     * Get useAsHolidays
     *
     * @return boolean
     */
    public function getUseAsHolidays()
    {
        return $this->UseAsHolidays;
    }

    public function __toString()
    {
        return (String) $this->reason.' am '.$this->date->format('d.m.Y').' für '.(String)$this->employee;
    }
}
