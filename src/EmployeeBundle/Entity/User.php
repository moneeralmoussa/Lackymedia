<?php

namespace EmployeeBundle\Entity;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 */
class User extends BaseUser
{
	protected $id;

	public function __construct()
	{
		parent::__construct();
		// your own logic
	}

  /**
   * @var \EmployeeBundle\Entity\Employee
   */
	protected $employee;

	protected $groups;

  /**
   * Set employee
   *
   * @param \EmployeeBundle\Entity\Employee $employee
   * @return User
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
     * @var \DateTime
     */
    private $lastLogout;


    /**
     * Set lastLogout
     *
     * @param \DateTime $lastLogout
     *
     * @return User
     */
    public function setLastLogout($lastLogout)
    {
        $this->lastLogout = $lastLogout;

        return $this;
    }

    /**
     * Get lastLogout
     *
     * @return \DateTime
     */
    public function getLastLogout()
    {
        return $this->lastLogout;
    }
}
