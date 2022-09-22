<?php

namespace EmployeeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Employeeworkingtimeeditgroup
 *
 * @ORM\Table(name="employeeworkingtimeeditgroup")
 * @ORM\Entity(repositoryClass="EmployeeBundle\Repository\EmployeeworkingtimeeditgroupRepository")
 */
class Employeeworkingtimeeditgroup
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
     * @ORM\Column(name="group_id", type="integer")
     */
    private $groupId;


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
     * @return Employeeworkingtimeeditgroup
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
     * Set groupId
     *
     * @param integer $groupId
     *
     * @return Employeeworkingtimeeditgroup
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    
        return $this;
    }

    /**
     * Get groupId
     *
     * @return integer
     */
    public function getGroupId()
    {
        return $this->groupId;
    }
}

