<?php

namespace EmployeeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * employeeworkday
 *
 * @ORM\Table(name="employeeworkday")
 * @ORM\Entity(repositoryClass="EmployeeBundle\Repository\employeeworkdayRepository")
 */
class Employeeworkday
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
     * @ORM\Column(name="begin_employeeposition_id", type="integer")
     */
    private $beginEmployeepositionId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="new_begin_employeeposition_date", type="datetime", nullable=true)
     */
    private $newBeginEmployeepositionDate;
    
    /**
     * @var string
     *
     * @ORM\Column(name="new_begin_employeeposition_comment", type="string", length=255, nullable=true)
     */
    private $newBeginEmployeepositionComment;

    /**
     * @var int
     *
     * @ORM\Column(name="new_begin_employeeposition_status_id", type="integer")
     */
    private $newBeginEmployeepositionStatusId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="new_end_employeeposition_date", type="datetime", nullable=true)
     */
    private $newEndEmployeepositionDate;
    
    /**
     * @var string
     *
     * @ORM\Column(name="new_end_employeeposition_comment", type="string", length=255, nullable=true)
     */
    private $newEndEmployeepositionComment;

    /**
     * @var int
     *
     * @ORM\Column(name="new_end_employeeposition_status_id", type="integer")
     */
    private $newEndEmployeepositionStatusId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="begin_employeeposition_date", type="datetime", nullable=true)
     */
    private $beginEmployeepositionDate;

    /**
     * @var int
     *
     * @ORM\Column(name="end_employeeposition_id", type="integer", nullable=true)
     */
    private $endEmployeepositionId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_employeeposition_date", type="datetime", nullable=true)
     */
    private $endEmployeepositionDate;

    /**
     * @var int
     *
     * @ORM\Column(name="begin_login_type_id", type="integer")
     */
    private $beginLoginTypeId;

    /**
     * @var int
     *
     * @ORM\Column(name="end_login_type_id", type="integer", nullable=true)
     */
    private $endLoginTypeId;

    /**
     * @var int
     *
     * @ORM\Column(name="begin_edit_by_employee_id", type="integer")
     */
    private $beginEditByEmployeeId;

    /**
     * @var int
     *
     * @ORM\Column(name="status_begin_id", type="integer", nullable=true)
     */
    private $statusBeginId;

    /**
     * @var int
     *
     * @ORM\Column(name="end_edit_by_employee_id", type="integer", nullable=true)
     */
    private $endEditByEmployeeId;

    /**
     * @var int
     *
     * @ORM\Column(name="status_end_id", type="integer", nullable=true)
     */
    private $statusEndId;

    /**
     * @var int
     *
     * @ORM\Column(name="sum", type="integer", nullable=true)
     */
    private $sum;

    /**
     * @var string
     *
     * @ORM\Column(name="begin_webbrowser", type="string", length=255, nullable=true)
     */
    private $beginWebbrowser;

    /**
     * @var string
     *
     * @ORM\Column(name="begin_ip", type="string", length=50, nullable=true)
     */
    private $beginIp;

    /**
     * @var string
     *
     * @ORM\Column(name="end_webbrowser", type="string", length=255, nullable=true)
     */
    private $endWebbrowser;

    /**
     * @var string
     *
     * @ORM\Column(name="end_ip", type="string", length=50, nullable=true)
     */
    private $endIp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime")
     */
    private $createAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_at", type="datetime")
     */
    private $updateAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
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
     * @return employeeworkday
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
     * Set beginEmployeepositionId
     *
     * @param integer $beginEmployeepositionId
     *
     * @return employeeworkday
     */
    public function setBeginEmployeepositionId($beginEmployeepositionId)
    {
        $this->beginEmployeepositionId = $beginEmployeepositionId;
    
        return $this;
    }

    /**
     * Get beginEmployeepositionId
     *
     * @return integer
     */
    public function getBeginEmployeepositionId()
    {
        return $this->beginEmployeepositionId;
    }

    /**
     * Set beginEmployeepositionDate
     *
     * @param \DateTime $beginEmployeepositionDate
     *
     * @return employeeworkday
     */
    public function setBeginEmployeepositionDate($beginEmployeepositionDate)
    {
        $this->beginEmployeepositionDate = $beginEmployeepositionDate;
    
        return $this;
    }

    /**
     * Get beginEmployeepositionDate
     *
     * @return \DateTime
     */
    public function getBeginEmployeepositionDate()
    {
        return $this->beginEmployeepositionDate;
    }

     /**
     * Set newBeginEmployeepositionDate
     *
     * @param \DateTime $newBeginEmployeepositionDate
     *
     * @return employeeworkday
     */
    public function setNewBeginEmployeepositionDate($newBeginEmployeepositionDate)
    {
        $this->newBeginEmployeepositionDate = $newBeginEmployeepositionDate;
    
        return $this;
    }

    /**
     * Get newBeginEmployeepositionDate
     *
     * @return \DateTime
     */
    public function getNewBeginEmployeepositionDate()
    {
        return $this->newBeginEmployeepositionDate;
    }

     /**
     * Set newEndEmployeepositionDate
     *
     * @param \DateTime $newEndEmployeepositionDate
     *
     * @return employeeworkday
     */
    public function setNewEndEmployeepositionDate($newEndEmployeepositionDate)
    {
        $this->newEndEmployeepositionDate = $newEndEmployeepositionDate;
    
        return $this;
    }

    /**
     * Get newEndEmployeepositionDate
     *
     * @return \DateTime
     */
    public function getNewEndEmployeepositionDate()
    {
        return $this->newEndEmployeepositionDate;
    }


    /**
     * Set newBeginEmployeepositionStatusId
     *
     * @param integer $newBeginEmployeepositionStatusId
     *
     * @return employeeworkday
     */
    public function setNewBeginEmployeepositionStatusId($newBeginEmployeepositionStatusId)
    {
        $this->newBeginEmployeepositionStatusId = $newBeginEmployeepositionStatusId;
        return $this;
    }

    /**
     * Get newBeginEmployeepositionStatusId
     *
     * @return integer
     */
    public function getNewBeginEmployeepositionStatusId()
    {
        return $this->newBeginEmployeepositionStatusId;
    }    

     /**
     * Set newEndEmployeepositionStatusId
     *
     * @param integer $newEndEmployeepositionStatusId
     *
     * @return employeeworkday
     */
    public function setNewEndEmployeepositionStatusId($newEndEmployeepositionStatusId)
    {
        $this->newEndEmployeepositionStatusId = $newEndEmployeepositionStatusId;
        return $this;
    }

    /**
     * Get newEndEmployeepositionStatusId
     *
     * @return integer
     */
    public function getNewEndEmployeepositionStatusId()
    {
        return $this->newEndEmployeepositionStatusId;
    }    

	 /**
     * Set newBeginEmployeepositionComment
     *
     * @param string $newBeginEmployeepositionComment
     *
     * @return employeeworkday
     */
    public function setNewBeginEmployeepositionComment($newBeginEmployeepositionComment)
    {
        $this->newBeginEmployeepositionComment = $newBeginEmployeepositionComment;
    
        return $this;
    }

    /**
     * Get newBeginEmployeepositionComment
     *
     * @return string
     */
    public function getNewBeginEmployeepositionComment()
    {
        return $this->newBeginEmployeepositionComment;
    }
        
	 /**
     * Set newEndEmployeepositionComment
     *
     * @param string $newEndEmployeepositionComment
     *
     * @return employeeworkday
     */
    public function setNewEndEmployeepositionComment($newEndEmployeepositionComment)
    {
        $this->newEndEmployeepositionComment = $newEndEmployeepositionComment;
    
        return $this;
    }

    /**
     * Get newEndEmployeepositionComment
     *
     * @return string
     */
    public function getNewEndEmployeepositionComment()
    {
        return $this->newEndEmployeepositionComment;
    }

    /**
     * Set endEmployeepositionId
     *
     * @param integer $endEmployeepositionId
     *
     * @return employeeworkday
     */
    public function setEndEmployeepositionId($endEmployeepositionId)
    {
        $this->endEmployeepositionId = $endEmployeepositionId;
    
        return $this;
    }

    /**
     * Get endEmployeepositionId
     *
     * @return integer
     */
    public function getEndEmployeepositionId()
    {
        return $this->endEmployeepositionId;
    }

    /**
     * Set endEmployeepositionDate
     *
     * @param \DateTime $endEmployeepositionDate
     *
     * @return employeeworkday
     */
    public function setEndEmployeepositionDate($endEmployeepositionDate)
    {
        $this->endEmployeepositionDate = $endEmployeepositionDate;
    
        return $this;
    }

    /**
     * Get endEmployeepositionDate
     *
     * @return \DateTime
     */
    public function getEndEmployeepositionDate()
    {
        return $this->endEmployeepositionDate;
    }

    /**
     * Set beginLoginTypeId
     *
     * @param integer $beginLoginTypeId
     *
     * @return employeeworkday
     */
    public function setBeginLoginTypeId($beginLoginTypeId)
    {
        $this->beginLoginTypeId = $beginLoginTypeId;
    
        return $this;
    }

    /**
     * Get beginLoginTypeId
     *
     * @return integer
     */
    public function getBeginLoginTypeId()
    {
        return $this->beginLoginTypeId;
    }

    /**
     * Set endLoginTypeId
     *
     * @param integer $endLoginTypeId
     *
     * @return employeeworkday
     */
    public function setEndLoginTypeId($endLoginTypeId)
    {
        $this->endLoginTypeId = $endLoginTypeId;
    
        return $this;
    }

    /**
     * Get endLoginTypeId
     *
     * @return integer
     */
    public function getEndLoginTypeId()
    {
        return $this->endLoginTypeId;
    }

    /**
     * Set beginEditByEmployeeId
     *
     * @param integer $beginEditByEmployeeId
     *
     * @return employeeworkday
     */
    public function setBeginEditByEmployeeId($beginEditByEmployeeId)
    {
        $this->beginEditByEmployeeId = $beginEditByEmployeeId;
    
        return $this;
    }

    /**
     * Get beginEditByEmployeeId
     *
     * @return integer
     */
    public function getBeginEditByEmployeeId()
    {
        return $this->beginEditByEmployeeId;
    }

    /**
     * Set statusBeginId
     *
     * @param integer $statusBeginId
     *
     * @return employeeworkday
     */
    public function setStatusBeginId($statusBeginId)
    {
        $this->statusBeginId = $statusBeginId;
    
        return $this;
    }

    /**
     * Get statusBeginId
     *
     * @return integer
     */
    public function getStatusBeginId()
    {
        return $this->statusBeginId;
    }

    /**
     * Set endEditByEmployeeId
     *
     * @param integer $endEditByEmployeeId
     *
     * @return employeeworkday
     */
    public function setEndEditByEmployeeId($endEditByEmployeeId)
    {
        $this->endEditByEmployeeId = $endEditByEmployeeId;
    
        return $this;
    }

    /**
     * Get endEditByEmployeeId
     *
     * @return integer
     */
    public function getEndEditByEmployeeId()
    {
        return $this->endEditByEmployeeId;
    }

    /**
     * Set statusEndId
     *
     * @param integer $statusEndId
     *
     * @return employeeworkday
     */
    public function setStatusEndId($statusEndId)
    {
        $this->statusEndId = $statusEndId;
    
        return $this;
    }

    /**
     * Get statusEndId
     *
     * @return integer
     */
    public function getStatusEndId()
    {
        return $this->statusEndId;
    }

    /**
     * Set sum
     *
     * @param integer $sum
     *
     * @return employeeworkday
     */
    public function setSum($sum)
    {
        $this->sum = $sum;
    
        return $this;
    }

    /**
     * Get sum
     *
     * @return integer
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * Set beginWebbrowser
     *
     * @param string $beginWebbrowser
     *
     * @return employeeworkday
     */
    public function setBeginWebbrowser($beginWebbrowser)
    {
        $this->beginWebbrowser = $beginWebbrowser;
    
        return $this;
    }

    /**
     * Get beginWebbrowser
     *
     * @return string
     */
    public function getBeginWebbrowser()
    {
        return $this->beginWebbrowser;
    }

    /**
     * Set beginIp
     *
     * @param string $beginIp
     *
     * @return employeeworkday
     */
    public function setBeginIp($beginIp)
    {
        $this->beginIp = $beginIp;
    
        return $this;
    }

    /**
     * Get beginIp
     *
     * @return string
     */
    public function getBeginIp()
    {
        return $this->beginIp;
    }

    /**
     * Set endWebbrowser
     *
     * @param string $endWebbrowser
     *
     * @return employeeworkday
     */
    public function setEndWebbrowser($endWebbrowser)
    {
        $this->endWebbrowser = $endWebbrowser;
    
        return $this;
    }

    /**
     * Get endWebbrowser
     *
     * @return string
     */
    public function getEndWebbrowser()
    {
        return $this->endWebbrowser;
    }

    /**
     * Set endIp
     *
     * @param string $endIp
     *
     * @return employeeworkday
     */
    public function setEndIp($endIp)
    {
        $this->endIp = $endIp;
    
        return $this;
    }

    /**
     * Get endIp
     *
     * @return string
     */
    public function getEndIp()
    {
        return $this->endIp;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     *
     * @return employeeworkday
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
     * Set updateAt
     *
     * @param \DateTime $updateAt
     *
     * @return employeeworkday
     */
    public function setUpdateAt($updateAt)
    {
        $this->updateAt = $updateAt;
    
        return $this;
    }

    /**
     * Get updateAt
     *
     * @return \DateTime
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return employeeworkday
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
}

