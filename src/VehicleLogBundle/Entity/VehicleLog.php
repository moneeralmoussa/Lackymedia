<?php

namespace VehicleLogBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * VehicleLog
 */
class VehicleLog
{
    use SoftDeleteableEntity;
    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $vehicleLogBeginTime;

    /**
     * @var \DateTime
     */
    private $vehicleLogEndTime;

    /**
     * @var string
     */
    private $comment;


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
     * Set vehicleLogBeginTime
     *
     * @param \DateTime $vehicleLogBeginTime
     *
     * @return VehicleLog
     */
    public function setVehicleLogBeginTime($vehicleLogBeginTime)
    {
        $this->vehicleLogBeginTime = $vehicleLogBeginTime;

        return $this;
    }

    /**
     * Get vehicleLogBeginTime
     *
     * @return \DateTime
     */
    public function getVehicleLogBeginTime()
    {
        return $this->vehicleLogBeginTime;
    }

    /**
     * Set vehicleLogEndTime
     *
     * @param \DateTime $vehicleLogEndTime
     *
     * @return VehicleLog
     */
    public function setVehicleLogEndTime($vehicleLogEndTime)
    {
        $this->vehicleLogEndTime = $vehicleLogEndTime;

        return $this;
    }

    /**
     * Get vehicleLogEndTime
     *
     * @return \DateTime
     */
    public function getVehicleLogEndTime()
    {
        return $this->vehicleLogEndTime;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return VehicleLog
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
     * @var \VehicleLogBundle\Entity\VehicleLogPosition
     */
    private $vehicleLogBeginPosition;

    /**
     * @var \VehicleLogBundle\Entity\VehicleLogPosition
     */
    private $vehicleLogEndPosition;

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
     * Set vehicleLogBeginPosition
     *
     * @param \VehicleLogBundle\Entity\VehicleLogPosition $vehicleLogBeginPosition
     *
     * @return VehicleLog
     */
    public function setVehicleLogBeginPosition(\VehicleLogBundle\Entity\VehicleLogPosition $vehicleLogBeginPosition = null)
    {
        $this->vehicleLogBeginPosition = $vehicleLogBeginPosition;

        return $this;
    }

    /**
     * Get vehicleLogBeginPosition
     *
     * @return \VehicleLogBundle\Entity\VehicleLogPosition
     */
    public function getVehicleLogBeginPosition()
    {
        return $this->vehicleLogBeginPosition;
    }

    /**
     * Set vehicleLogEndPosition
     *
     * @param \VehicleLogBundle\Entity\VehicleLogPosition $vehicleLogEndPosition
     *
     * @return VehicleLog
     */
    public function setVehicleLogEndPosition(\VehicleLogBundle\Entity\VehicleLogPosition $vehicleLogEndPosition = null)
    {
        $this->vehicleLogEndPosition = $vehicleLogEndPosition;

        return $this;
    }

    /**
     * Get vehicleLogEndPosition
     *
     * @return \VehicleLogBundle\Entity\VehicleLogPosition
     */
    public function getVehicleLogEndPosition()
    {
        return $this->vehicleLogEndPosition;
    }

    /**
     * Set employee
     *
     * @param \EmployeeBundle\Entity\Employee $employee
     *
     * @return VehicleLog
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
     * @return VehicleLog
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
     * @return VehicleLog
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
    private $created_at;

    /**
     * @var \DateTime
     */
    private $updated_at;


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return VehicleLog
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
     * @return VehicleLog
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
     * @var boolean
     */
    private $vehicleClean = false;


    /**
     * Set vehicleClean
     *
     * @param boolean $vehicleClean
     *
     * @return VehicleLog
     */
    public function setVehicleClean($vehicleClean)
    {
        $this->vehicleClean = $vehicleClean;

        return $this;
    }

    /**
     * Get vehicleClean
     *
     * @return boolean
     */
    public function getVehicleClean()
    {
        return $this->vehicleClean;
    }
    /**
     * @var \DateTime
     */
    private $deleted_at;

    public function __toString()
    {
        return (String) $this->reason.' mit dem Fahrzeug '.$this->vehicle;
    }

         /**
         * @var \DateTime
         */
        private $vehiclecleandate;

         /**
         * @var \DateTime
         */
        private $vehiclerepairdate;

            /**
         * Set vehiclecleandate
         *
         * @param \DateTime $vehiclecleandate
         *
         * @return VehicleLog
         */
        public function setVehiclecleandate($vehiclecleandate)
        {
            $this->vehiclecleandate = $vehiclecleandate;

            return $this;
        }

        /**
         * Get vehiclecleandate
         *
         * @return \DateTime
         */
        public function getVehiclecleandate()
        {
            return $this->vehiclecleandate;
        }

            /**
         * Set vehiclerepairdate
         *
         * @param \DateTime $vehiclerepairdate
         *
         * @return VehicleLog
         */
        public function setVehiclerepairdate($vehiclerepairdate)
        {
            $this->vehiclerepairdate = $vehiclerepairdate;

            return $this;
        }

        /**
         * Get vehiclerepairdate
         *
         * @return \DateTime
         */
        public function getVehiclerepairdate()
        {
            return $this->vehiclerepairdate;
        }






        /**
         * @var boolean
         */
        private $vehiclerepair = false;


        /**
         * Set vehiclerepair
         *
         * @param boolean $vehiclerepair
         *
         * @return VehicleLog
         */
        public function setVehiclerepair($vehiclerepair)
        {
            $this->vehiclerepair = $vehiclerepair;

            return $this;
        }

        /**
         * Get vehiclerepair
         *
         * @return boolean
         */
        public function getVehiclerepair()
        {
            return $this->vehiclerepair;
        }

          /**
         * @var string
         */
        private $commentrepair;


        /**
         * Set commentrepair
         *
         * @param string $commentrepair
         *
         * @return VehicleLog
         */
        public function setCommentrepair($commentrepair)
        {
            $this->commentrepair = $commentrepair;

            return $this;
        }

        /**
         * Get commentrepair
         *
         * @return string
         */
        public function getCommentrepair()
        {
            return $this->commentrepair;
        }

        /**
         * @var integer
         */
        private $expensereason;

       /**
         * Set expensereason
         *
         * @param integer $expensereason
         *
         * @return VehicleReservation
         */
        public function setExpensereason($expensereason)
        {
            $this->expensereason = $expensereason;

            return $this;
        }

        /**
         * Get expensereason
         *
         * @return integer
         */
        public function getExpensereason()
        {
            return $this->expensereason;
        }    

}
