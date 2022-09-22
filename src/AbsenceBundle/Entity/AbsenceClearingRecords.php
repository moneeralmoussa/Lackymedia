<?php

namespace AbsenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AbsenceClearingRecords
 *
 * @ORM\Table(name="absence_clearing_records")
 * @ORM\Entity(repositoryClass="AbsenceBundle\Repository\AbsenceClearingRecordsRepository")
 */
class AbsenceClearingRecords
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
     * @ORM\Column(name="employee_id", type="integer")
     */
    private $employeeId;

    /**
     * @var int
     *
     * @ORM\Column(name="absence_clearing_id", type="integer")
     */
    private $absenceClearingId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="value", type="integer")
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=512)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime")
     */
    private $createAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
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
     * @return AbsenceClearingRecords
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
     * Set absenceClearingId
     *
     * @param integer $absenceClearingId
     *
     * @return AbsenceClearingRecords
     */
    public function setAbsenceClearingId($absenceClearingId)
    {
        $this->absenceClearingId = $absenceClearingId;

        return $this;
    }

    /**
     * Get absenceClearingId
     *
     * @return int
     */
    public function getAbsenceClearingId()
    {
        return $this->absenceClearingId;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return AbsenceClearingRecords
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set value
     *
     * @param integer $value
     *
     * @return AbsenceClearingRecords
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return AbsenceClearingRecords
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
     * Set createAt
     *
     * @param \DateTime $createAt
     *
     * @return AbsenceClearingRecords
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
     * @return AbsenceClearingRecords
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
     * @var \EmployeeBundle\Entity\Employee
     */
    private $employee;

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
     * @var \EmployeeBundle\Entity\AbsenceClearing
     */
    private $absenceClearing;

    /**
     * Set absenceClearing
     *
     * @param \EmployeeBundle\Entity\AbsenceClearing $absenceClearing
     *
     * @return AbsenceClearing
     */
    public function setAbsenceClearing(\EmployeeBundle\Entity\AbsenceClearing $absenceClearing = null)
    {
        $this->absenceClearing = $absenceClearing;
        return $this;
    }

    /**
     * Get absenceClearing
     *
     * @return \EmployeeBundle\Entity\AbsenceClearing
     */
    public function getAbsenceClearing()
    {
        return $this->absenceClearing;
    }
}
