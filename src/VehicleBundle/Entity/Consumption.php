<?php

namespace VehicleBundle\Entity;

/**
 * Consumption
 */
class Consumption
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @var \DateTime
     */
    private $deleted_at;

    /**
     * @var \DateTime
     */
    private $consumptionBeginTime;

    /**
     * @var \DateTime
     */
    private $consumptionEndTime;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var integer
     */
    private $trimbleId;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $consumptionDetails;

    /**
     * @var \VehicleBundle\Entity\Vehicle
     */
    private $vehicle;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->consumptionDetails = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Consumption
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Consumption
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Consumption
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

    /**
     * Set consumptionBeginTime
     *
     * @param \DateTime $consumptionBeginTime
     *
     * @return Consumption
     */
    public function setConsumptionBeginTime($consumptionBeginTime)
    {
        $this->consumptionBeginTime = $consumptionBeginTime;

        return $this;
    }

    /**
     * Get consumptionBeginTime
     *
     * @return \DateTime
     */
    public function getConsumptionBeginTime()
    {
        return $this->consumptionBeginTime;
    }

    /**
     * Set consumptionEndTime
     *
     * @param \DateTime $consumptionEndTime
     *
     * @return Consumption
     */
    public function setConsumptionEndTime($consumptionEndTime)
    {
        $this->consumptionEndTime = $consumptionEndTime;

        return $this;
    }

    /**
     * Get consumptionEndTime
     *
     * @return \DateTime
     */
    public function getConsumptionEndTime()
    {
        return $this->consumptionEndTime;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Consumption
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
     * Add consumptionDetail
     *
     * @param \VehicleBundle\Entity\ConsumptionDetail $consumptionDetail
     *
     * @return Consumption
     */
    public function addConsumptionDetail(\VehicleBundle\Entity\ConsumptionDetail $consumptionDetail)
    {
        $this->consumptionDetails[] = $consumptionDetail;

        return $this;
    }

    /**
     * Remove consumptionDetail
     *
     * @param \VehicleBundle\Entity\ConsumptionDetail $consumptionDetail
     */
    public function removeConsumptionDetail(\VehicleBundle\Entity\ConsumptionDetail $consumptionDetail)
    {
        $this->consumptionDetails->removeElement($consumptionDetail);
    }

    /**
     * Get consumptionDetails
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConsumptionDetails()
    {
        return $this->consumptionDetails;
    }

    /**
     * Set vehicle
     *
     * @param \VehicleBundle\Entity\Vehicle $vehicle
     *
     * @return Consumption
     */
    public function setVehicle(\VehicleBundle\Entity\Vehicle $vehicle = null)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * Get vehicle
     *
     * @return \VehicleBundle\Entity\Vehicle
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    public function getDetailByLabel($label)
    {
        foreach($this->getConsumptionDetails() as $detail) {
            if($detail->getLabel() == $label) {
                return $detail;
            }
        }

        return null;
    }
    /**
     * @var \EmployeeBundle\Entity\Employee
     */
    private $driver;


    /**
     * Set driver
     *
     * @param \EmployeeBundle\Entity\Employee $driver
     *
     * @return Consumption
     */
    public function setDriver(\EmployeeBundle\Entity\Employee $driver = null)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Get driver
     *
     * @return \EmployeeBundle\Entity\Employee
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Set trimbleId
     *
     * @param integer $trimbleId
     *
     * @return Consumption
     */
    public function setTrimbleId($trimbleId)
    {
        $this->trimbleId = $trimbleId;

        return $this;
    }

    /**
     * Get trimbleId
     *
     * @return integer
     */
    public function getTrimbleId()
    {
        return $this->trimbleId;
    }
}
