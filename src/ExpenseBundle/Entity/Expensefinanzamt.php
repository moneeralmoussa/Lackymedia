<?php

namespace ExpenseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Expensefinanzamt
 *
 * @ORM\Table(name="expensefinanzamt")
 * @ORM\Entity(repositoryClass="ExpenseBundle\Repository\ExpensefinanzamtRepository")
 */
class Expensefinanzamt
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
     * @var \EmployeeBundle\Entity\Employee
     */
    private $employee;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var int
     *
     * @ORM\Column(name="expenses1", type="integer")
     */
    private $expenses1;

    /**
     * @var int
     *
     * @ORM\Column(name="expenses2", type="integer")
     */
    private $expenses2;

    /**
     * @var int
     *
     * @ORM\Column(name="expenses3", type="integer")
     */
    private $expenses3;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime")
     */
    private $createAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime")
     */
    private $deletedAt;


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
     * Set employeeId
     *
     * @param integer $employeeId
     *
     * @return Expensefinanzamt
     */
    public function setEmployeeId($employeeId)
    {
        $this->employeeId = $employeeId;
    
        return $this;
    }

    /**
     * Get employeeId
     *
     * @return integer
     */
    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Expensefinanzamt
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
     * Set expenses1
     *
     * @param integer $expenses1
     *
     * @return Expensefinanzamt
     */
    public function setExpenses1($expenses1)
    {
        $this->expenses1 = $expenses1;
    
        return $this;
    }

    /**
     * Get expenses1
     *
     * @return integer
     */
    public function getExpenses1()
    {
        return $this->expenses1;
    }

    /**
     * Set expenses2
     *
     * @param integer $expenses2
     *
     * @return Expensefinanzamt
     */
    public function setExpenses2($expenses2)
    {
        $this->expenses2 = $expenses2;
    
        return $this;
    }

    /**
     * Get expenses2
     *
     * @return integer
     */
    public function getExpenses2()
    {
        return $this->expenses2;
    }

    /**
     * Set expenses3
     *
     * @param integer $expenses3
     *
     * @return Expensefinanzamt
     */
    public function setExpenses3($expenses3)
    {
        $this->expenses3 = $expenses3;
    
        return $this;
    }

    /**
     * Get expenses3
     *
     * @return integer
     */
    public function getExpenses3()
    {
        return $this->expenses3;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Expensefinanzamt
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     *
     * @return Expensefinanzamt
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
     * @return Expensefinanzamt
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
     * Set employee
     *
     * @param \EmployeeBundle\Entity\Employee $employee
     *
     * @return Expensefinanzamt
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
     * @var \EmployeeBundle\Entity\Employee
     */
    private $byemployee;

     /**
     * Set byemployee
     *
     * @param \EmployeeBundle\Entity\Employee $byemployee
     *
     * @return Expensefinanzamt
     */
    public function setByEmployee(\EmployeeBundle\Entity\Employee $byemployee = null)
    {
        $this->byemployee = $byemployee;

        return $this;
    }

    /**
     * Get byemployee
     *
     * @return \EmployeeBundle\Entity\Employee
     */
    public function getByEmployee()
    {
        return $this->byemployee;
    }

}

