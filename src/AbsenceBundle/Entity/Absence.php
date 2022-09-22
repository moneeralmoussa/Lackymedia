<?php

namespace AbsenceBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Absence
 */
class Absence
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $note;

    /**
     * @var \DateTime
     */
    private $fromDate;

    /**
     * @var \DateTime
     */
    private $toDate;

    /**
     * @var \EmployeeBundle\Entity\Employee
     */
    private $employee;

    /**
     * @var \EmployeeBundle\Entity\Employee
     */
    private $approvedBy;

    /**
     * @var \AbsenceBundle\Entity\Reason
     */
    private $reason;

    /**
     * @var \AbsenceBundle\Entity\Status
     */
    private $status;

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
     * Set note
     *
     * @param string $note
     *
     * @return Absence
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set fromDate
     *
     * @param \DateTime $fromDate
     *
     * @return Absence
     */
    public function setFromDate($fromDate)
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    /**
     * Get fromDate
     *
     * @return \DateTime
     */
    public function getFromDate()
    {
        return $this->fromDate;
    }

    /**
     * Set toDate
     *
     * @param \DateTime $toDate
     *
     * @return Absence
     */
    public function setToDate($toDate)
    {
        $this->toDate = $toDate;

        return $this;
    }

    /**
     * Get toDate
     *
     * @return \DateTime
     */
    public function getToDate()
    {
        return $this->toDate;
    }

    /**
     * Set employee
     *
     * @param \EmployeeBundle\Entity\Employee $employee
     *
     * @return Absence
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
     * @return Absence
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

    /**
     * Set reason
     *
     * @param \AbsenceBundle\Entity\Reason $reason
     *
     * @return Absence
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
     * Set status
     *
     * @param \AbsenceBundle\Entity\Status $status
     *
     * @return Absence
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
     * @var integer
     */
    private $day;


    /**
     * Set day
     *
     * @param integer $day
     *
     * @return Absence
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get day
     *
     * @return integer
     */
    public function getDay()
    {
        return $this->day;
    }
    /**
     * @var string
     */
    private $color;


    /**
     * Set color
     *
     * @param string $color
     *
     * @return Absence
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
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
     * Set createdat
     *
     * @param \DateTime $createdat
     *
     * @return Absence
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
     * @return Absence
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

    // public function getCalculatedDay()
    // {
    //     $toImmutable = \DateTimeImmutable::createFromMutable($this->getToDate());
    //
    //     $diff = $this->getFromDate()->diff($toImmutable->modify('+1 day'));
    //
    //     if ($diff->d < 1) {
    //         return 0.5;
    //     }
    //
    //     return $diff->d;
    // }

    public function getCalculatedDay()
    {
        global $kernel;
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        return $em->getRepository('AbsenceBundle:AbsenceDetailClearing')->findAllSumWithAbsenceAbsenceDetailClearings($this->getEmployee(), $this);
    }

    public function validateDays(ExecutionContextInterface $context)
    {
        $days = $this->getCalculatedDay();

        //Sonderfall: halber Tag
        if ($this->getDay() < 1 && $days < 1) {
            return;
        }

        if ($days < $this->getDay()) {
            $context->buildViolation('Invalid Days.')
            ->addViolation();
        }
    }
    /**
     * @var integer
     */
    private $employeeID;


    /**
     * Set employeeID
     *
     * @param integer $employeeID
     *
     * @return Absence
     */
    public function setEmployeeID($employeeID)
    {
        $this->employeeID = $employeeID;

        return $this;
    }

    /**
     * Get employeeID
     *
     * @return integer
     */
    public function getEmployeeID()
    {
        return $this->employeeID;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->employee = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add employee
     *
     * @param \EmployeeBundle\Entity\Employee $employee
     *
     * @return Absence
     */
    public function addEmployee(\EmployeeBundle\Entity\Employee $employee)
    {
        $this->employee[] = $employee;

        return $this;
    }

    /**
     * Remove employee
     *
     * @param \EmployeeBundle\Entity\Employee $employee
     */
    public function removeEmployee(\EmployeeBundle\Entity\Employee $employee)
    {
        $this->employee->removeElement($employee);
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
     * @return Absence
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

    public function __toString()
    {
        if ($this->fromDate && $this->toDate != null) {
            return (String) $this->reason.' von '.$this->fromDate->format('d.m.Y').' bis '.$this->toDate->format('d.m.Y').' für '.(String)$this->employee;
        }
        return (String) $this->reason;
    }
}
