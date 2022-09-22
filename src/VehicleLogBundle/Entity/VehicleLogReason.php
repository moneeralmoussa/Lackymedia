<?php

namespace VehicleLogBundle\Entity;

/**
 * VehicleLogReason
 */
class VehicleLogReason
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;


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
     * Set name
     *
     * @param string $name
     *
     * @return VehicleLogReason
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vehicleLogs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->vehicleLogs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add vehicleLog
     *
     * @param \VehicleLogBundle\Entity\VehicleLog $vehicleLog
     *
     * @return VehicleLogReason
     */
    public function addVehicleLog(\VehicleLogBundle\Entity\VehicleLog $vehicleLog)
    {
        $this->vehicleLogs[] = $vehicleLog;

        return $this;
    }

    /**
     * Remove vehicleLog
     *
     * @param \VehicleLogBundle\Entity\VehicleLog $vehicleLog
     */
    public function removeVehicleLog(\VehicleLogBundle\Entity\VehicleLog $vehicleLog)
    {
        $this->vehicleLogs->removeElement($vehicleLog);
    }

    /**
     * Get vehicleLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVehicleLogs()
    {
        return $this->vehicleLogs;
    }

    public function __toString(){
      return $this->name;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vehicleReservations;


    /**
     * Add vehicleReservation
     *
     * @param \VehicleLogBundle\Entity\VehicleReservation $vehicleReservation
     *
     * @return VehicleLogReason
     */
    public function addVehicleReservation(\VehicleLogBundle\Entity\VehicleReservation $vehicleReservation)
    {
        $this->vehicleReservations[] = $vehicleReservation;

        return $this;
    }

    /**
     * Remove vehicleReservation
     *
     * @param \VehicleLogBundle\Entity\VehicleReservation $vehicleReservation
     */
    public function removeVehicleReservation(\VehicleLogBundle\Entity\VehicleReservation $vehicleReservation)
    {
        $this->vehicleReservations->removeElement($vehicleReservation);
    }

    /**
     * Get vehicleReservations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVehicleReservations()
    {
        return $this->vehicleReservations;
    }
}
