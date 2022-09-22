<?php

namespace VehicleBundle\Entity;

/**
 * Vehicletypetype
 */
class Vehicletypetype
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
     * @return Vehicletypetype
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
    private $vehicleTypes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->vehicleTypes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add vehicleType
     *
     * @param \VehicleBundle\Entity\Vehicletype $vehicleType
     *
     * @return Vehicletypetype
     */
    public function addVehicleType(\VehicleBundle\Entity\Vehicletype $vehicleType)
    {
        $this->vehicleTypes[] = $vehicleType;

        return $this;
    }

    /**
     * Remove vehicleType
     *
     * @param \VehicleBundle\Entity\Vehicletype $vehicleType
     */
    public function removeVehicleType(\VehicleBundle\Entity\Vehicletype $vehicleType)
    {
        $this->vehicleTypes->removeElement($vehicleType);
    }

    /**
     * Get vehicleTypes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVehicleTypes()
    {
        return $this->vehicleTypes;
    }
}
