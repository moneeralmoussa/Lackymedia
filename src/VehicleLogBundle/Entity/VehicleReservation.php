<?php

namespace VehicleLogBundle\Entity;

/**
 * VehicleReservation
 */
class VehicleReservation
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
    private $vehicleReservationBeginTime;

    /**
     * @var \DateTime
     */
    private $vehicleReservationEndTime;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var \VehicleLogBundle\Entity\VehicleReservationPosition
     */
    private $vehicleReservationBeginPosition;

    /**
     * @var \VehicleLogBundle\Entity\VehicleReservationPosition
     */
    private $vehicleReservationEndPosition;

    /**
     * @var \EmployeeBundle\Entity\Employee
     */
    private $employee;

    /**
     * @var \VehicleBundle\Entity\Vehicle
     */
    private $vehicle;

    /**
     * @var \VehicleLogBundle\Entity\VehicleLogReason
     */
    private $reason;


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
     * @return VehicleReservation
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
     * @return VehicleReservation
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
     * Set vehicleReservationBeginTime
     *
     * @param \DateTime $vehicleReservationBeginTime
     *
     * @return VehicleReservation
     */
    public function setVehicleReservationBeginTime($vehicleReservationBeginTime)
    {
        $this->vehicleReservationBeginTime = $vehicleReservationBeginTime;

        return $this;
    }

    /**
     * Get vehicleReservationBeginTime
     *
     * @return \DateTime
     */
    public function getVehicleReservationBeginTime()
    {
        return $this->vehicleReservationBeginTime;
    }

    /**
     * Set vehicleReservationEndTime
     *
     * @param \DateTime $vehicleReservationEndTime
     *
     * @return VehicleReservation
     */
    public function setVehicleReservationEndTime($vehicleReservationEndTime)
    {
        $this->vehicleReservationEndTime = $vehicleReservationEndTime;

        return $this;
    }

    /**
     * Get vehicleReservationEndTime
     *
     * @return \DateTime
     */
    public function getVehicleReservationEndTime()
    {
        return $this->vehicleReservationEndTime;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return VehicleReservation
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
     * Set vehicleReservationBeginPosition
     *
     * @param \VehicleLogBundle\Entity\VehicleReservationPosition $vehicleReservationBeginPosition
     *
     * @return VehicleReservation
     */
    public function setVehicleReservationBeginPosition(\VehicleLogBundle\Entity\VehicleReservationPosition $vehicleReservationBeginPosition = null)
    {
        $this->vehicleReservationBeginPosition = $vehicleReservationBeginPosition;

        return $this;
    }

    /**
     * Get vehicleReservationBeginPosition
     *
     * @return \VehicleLogBundle\Entity\VehicleReservationPosition
     */
    public function getVehicleReservationBeginPosition()
    {
        return $this->vehicleReservationBeginPosition;
    }

    /**
     * Set vehicleReservationEndPosition
     *
     * @param \VehicleLogBundle\Entity\VehicleReservationPosition $vehicleReservationEndPosition
     *
     * @return VehicleReservation
     */
    public function setVehicleReservationEndPosition(\VehicleLogBundle\Entity\VehicleReservationPosition $vehicleReservationEndPosition = null)
    {
        $this->vehicleReservationEndPosition = $vehicleReservationEndPosition;

        return $this;
    }

    /**
     * Get vehicleReservationEndPosition
     *
     * @return \VehicleLogBundle\Entity\VehicleReservationPosition
     */
    public function getVehicleReservationEndPosition()
    {
        return $this->vehicleReservationEndPosition;
    }

    /**
     * Set employee
     *
     * @param \EmployeeBundle\Entity\Employee $employee
     *
     * @return VehicleReservation
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
     * Set vehicle
     *
     * @param \VehicleBundle\Entity\Vehicle $vehicle
     *
     * @return VehicleReservation
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

    /**
     * Set reason
     *
     * @param \VehicleLogBundle\Entity\VehicleLogReason $reason
     *
     * @return VehicleReservation
     */
    public function setReason(\VehicleLogBundle\Entity\VehicleLogReason $reason = null)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return \VehicleLogBundle\Entity\VehicleLogReason
     */
    public function getReason()
    {
        return $this->reason;
    }
    /**
     * @var \DateTime
     */
    private $deletedAt;


    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return VehicleReservation
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
