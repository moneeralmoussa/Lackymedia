<?php

namespace VehicleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vehicletype
 */
class Vehicletype
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $komalogId;

    /**
     * @var string
     */
    private $name;


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
     * Set komalogId
     *
     * @param integer $komalogId
     * @return Vehicletype
     */
    public function setKomalogId($komalogId)
    {
        $this->komalogId = $komalogId;

        return $this;
    }

    /**
     * Get komalogId
     *
     * @return integer 
     */
    public function getKomalogId()
    {
        return $this->komalogId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Vehicletype
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
    private $vehicles;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->vehicles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add vehicles
     *
     * @param \VehicleBundle\Entity\Vehicle $vehicles
     * @return Vehicletype
     */
    public function addVehicle(\VehicleBundle\Entity\Vehicle $vehicles)
    {
        $this->vehicles[] = $vehicles;

        return $this;
    }

    /**
     * Remove vehicles
     *
     * @param \VehicleBundle\Entity\Vehicle $vehicles
     */
    public function removeVehicle(\VehicleBundle\Entity\Vehicle $vehicles)
    {
        $this->vehicles->removeElement($vehicles);
    }

    /**
     * Get vehicles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVehicles()
    {
        return $this->vehicles;
    }
    /**
     * @var \VehicleBundle\Entity\Vehicletypetype
     */
    private $vehicletypetype;


    /**
     * Set vehicletypetype
     *
     * @param \VehicleBundle\Entity\Vehicletypetype $vehicletypetype
     *
     * @return Vehicletype
     */
    public function setVehicletypetype(\VehicleBundle\Entity\Vehicletypetype $vehicletypetype = null)
    {
        $this->vehicletypetype = $vehicletypetype;

        return $this;
    }

    /**
     * Get vehicletypetype
     *
     * @return \VehicleBundle\Entity\Vehicletypetype
     */
    public function getVehicletypetype()
    {
        return $this->vehicletypetype;
    }
}
