<?php

namespace EmployeeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkingHours
 *
 * @ORM\Table(name="working_hours")
 * @ORM\Entity(repositoryClass="EmployeeBundle\Repository\WorkingHoursRepository")
 */
class WorkingHours
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="employeeId", type="integer")
     */
    private $employeeId;

    /**
     * @var int
     *
     * @ORM\Column(name="dayOfWeek", type="integer")
     */
    private $dayOfWeek;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="workBegin", type="time", nullable=true)
     */
    private $workBegin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="workEnd", type="time", nullable=true)
     */
    private $workEnd;

    /**
     * @var bool
     *
     * @ORM\Column(name="autoBreak", type="boolean")
     */
    private $autoBreak;

    /**
     * @var bool
     *
     * @ORM\Column(name="school", type="boolean")
     */
    private $school;

    /**
     * @var bool
     *
     * @ORM\Column(name="allowOvertime", type="boolean")
     */
    private $allowOvertime;

    /**
     * @var string
     *
     * @ORM\Column(name="overtimeHourlyRate", type="decimal", precision=10, scale=2)
     */
    private $overtimeHourlyRate;

    /**
     * @var string
     *
     * @ORM\Column(name="overtime", type="decimal", precision=10, scale=1)
     */
    private $overtime;

    /**
     * @var string
     *
     * @ORM\Column(name="hourly_rate", type="decimal", precision=10, scale=2)
     */
    private $hourlyRate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="BreakBegin", type="time")
     */
    private $breakBegin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="breakEnd", type="time")
     */
    private $breakEnd;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createAt", type="datetime")
     */
    private $createAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deletedAt", type="datetime")
     */
    private $deletedAt;


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
     * Set employeeId
     *
     * @param integer $employeeId
     *
     * @return WorkingHours
     */
    public function setEmployeeId($employeeId)
    {
        $this->employeeId = $employeeId;

        return $this;
    }

    /**
     * Get employeeId
     *
     * @return int
     */
    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    /**
     * Set dayOfWeek
     *
     * @param integer $dayOfWeek
     *
     * @return WorkingHours
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
     * Set workBegin
     *
     * @param \DateTime $workBegin
     *
     * @return WorkingHours
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
     * @return WorkingHours
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
     * Set autoBreak
     *
     * @param boolean $autoBreak
     *
     * @return WorkingHours
     */
    public function setAutoBreak($autoBreak)
    {
        $this->autoBreak = $autoBreak;

        return $this;
    }

    /**
     * Get autoBreak
     *
     * @return bool
     */
    public function getAutoBreak()
    {
        return $this->autoBreak;
    }

    /**
     * Set school
     *
     * @param boolean $school
     *
     * @return WorkingHours
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
     * Set allowOvertime
     *
     * @param boolean $allowOvertime
     *
     * @return WorkingHours
     */
    public function setAllowOvertime($allowOvertime)
    {
        $this->allowOvertime = $allowOvertime;

        return $this;
    }

    /**
     * Get allowOvertime
     *
     * @return bool
     */
    public function getAllowOvertime()
    {
        return $this->allowOvertime;
    }

    /**
     * Set overtimeHourlyRate
     *
     * @param string $overtimeHourlyRate
     *
     * @return WorkingHours
     */
    public function setOvertimeHourlyRate($overtimeHourlyRate)
    {
        $this->overtimeHourlyRate = $overtimeHourlyRate;

        return $this;
    }

    /**
     * Get overtimeHourlyRate
     *
     * @return string
     */
    public function getOvertimeHourlyRate()
    {
        return $this->overtimeHourlyRate;
    }

    /**
     * Set overtime
     *
     * @param string $overtime
     *
     * @return WorkingHours
     */
    public function setOvertime($overtime)
    {
        $this->overtime = $overtime;

        return $this;
    }

    /**
     * Get overtime
     *
     * @return string
     */
    public function getOvertime()
    {
        return $this->overtime;
    }

    /**
     * Set hourlyRate
     *
     * @param string $hourlyRate
     *
     * @return WorkingHours
     */
    public function setHourlyRate($hourlyRate)
    {
        $this->hourlyRate = $hourlyRate;

        return $this;
    }

    /**
     * Get hourlyRate
     *
     * @return string
     */
    public function getHourlyRate()
    {
        return $this->hourlyRate;
    }

    /**
     * Set breakBegin
     *
     * @param \DateTime $breakBegin
     *
     * @return WorkingHours
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
     * @return WorkingHours
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
     * Set createAt
     *
     * @param \DateTime $createAt
     *
     * @return WorkingHours
     */
    public function setCreateAt($createAt)
    {
        $this->createAt = $createAt;

        return $this;
    }

    /**
     * Get createAt
     *
     * @return \DateTime
     */
    public function getCreateAt()
    {
        return $this->createAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return WorkingHours
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
   * Get dayOfWeek
   *
   * @return int
   */
  public function getDayOfWeekText()
  {$retval = '--';
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
}
