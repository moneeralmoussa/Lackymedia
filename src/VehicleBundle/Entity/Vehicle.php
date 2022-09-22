<?php

namespace VehicleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vehicle
 */
class Vehicle
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
    private $trimbleId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var int
     */
    private $pin;


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
     * @return Vehicle
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
     * Set trimbleId
     *
     * @param string $trimbleId
     * @return Vehicle
     */
    public function setTrimbleId($trimbleId)
    {
        $this->trimbleId = $trimbleId;

        return $this;
    }

    /**
     * Get trimbleId
     *
     * @return string
     */
    public function getTrimbleId()
    {
        return $this->trimbleId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Vehicle
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
     * Set type
     *
     * @param string $type
     * @return Vehicle
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Vehicle
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
     * @var \VehicleBundle\Entity\Vehicletype
     */
    private $vehicletype;


    /**
     * Set vehicletype
     *
     * @param \VehicleBundle\Entity\Vehicletype $vehicletype
     * @return Vehicle
     */
    public function setVehicletype(\VehicleBundle\Entity\Vehicletype $vehicletype = null)
    {
        $this->vehicletype = $vehicletype;

        return $this;
    }

    /**
     * Get vehicletype
     *
     * @return \VehicleBundle\Entity\Vehicletype
     */
    public function getVehicletype()
    {
        return $this->vehicletype;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $truckWorkdays;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $carWorkdays;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->truckWorkdays = new \Doctrine\Common\Collections\ArrayCollection();
        $this->carWorkdays = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add truckWorkday
     *
     * @param \ExpenseBundle\Entity\Workday $truckWorkday
     *
     * @return Vehicle
     */
    public function addTruckWorkday(\ExpenseBundle\Entity\Workday $truckWorkday)
    {
        $this->truckWorkdays[] = $truckWorkday;

        return $this;
    }

    /**
     * Remove truckWorkday
     *
     * @param \ExpenseBundle\Entity\Workday $truckWorkday
     */
    public function removeTruckWorkday(\ExpenseBundle\Entity\Workday $truckWorkday)
    {
        $this->truckWorkdays->removeElement($truckWorkday);
    }

    /**
     * Get truckWorkdays
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTruckWorkdays()
    {
        return $this->truckWorkdays;
    }

    /**
     * Add carWorkday
     *
     * @param \ExpenseBundle\Entity\Workday $carWorkday
     *
     * @return Vehicle
     */
    public function addCarWorkday(\ExpenseBundle\Entity\Workday $carWorkday)
    {
        $this->carWorkdays[] = $carWorkday;

        return $this;
    }

    /**
     * Remove carWorkday
     *
     * @param \ExpenseBundle\Entity\Workday $carWorkday
     */
    public function removeCarWorkday(\ExpenseBundle\Entity\Workday $carWorkday)
    {
        $this->carWorkdays->removeElement($carWorkday);
    }

    /**
     * Get carWorkdays
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCarWorkdays()
    {
        return $this->carWorkdays;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vehicleVehicleLogPositions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vehicleVehicleLogs;


    /**
     * Add vehicleVehicleLogPosition
     *
     * @param \VehicleLogBundle\Entity\VehicleLogPosition $vehicleVehicleLogPosition
     *
     * @return Vehicle
     */
    public function addVehicleVehicleLogPosition(\VehicleLogBundle\Entity\VehicleLogPosition $vehicleVehicleLogPosition)
    {
        $this->vehicleVehicleLogPositions[] = $vehicleVehicleLogPosition;

        return $this;
    }

    /**
     * Remove vehicleVehicleLogPosition
     *
     * @param \VehicleLogBundle\Entity\VehicleLogPosition $vehicleVehicleLogPosition
     */
    public function removeVehicleVehicleLogPosition(\VehicleLogBundle\Entity\VehicleLogPosition $vehicleVehicleLogPosition)
    {
        $this->vehicleVehicleLogPositions->removeElement($vehicleVehicleLogPosition);
    }

    /**
     * Get vehicleVehicleLogPositions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVehicleVehicleLogPositions()
    {
        return $this->vehicleVehicleLogPositions;
    }

    /**
     * Add vehicleVehicleLog
     *
     * @param \VehicleLogBundle\Entity\VehicleLog $vehicleVehicleLog
     *
     * @return Vehicle
     */
    public function addVehicleVehicleLog(\VehicleLogBundle\Entity\VehicleLog $vehicleVehicleLog)
    {
        $this->vehicleVehicleLogs[] = $vehicleVehicleLog;

        return $this;
    }

    /**
     * Remove vehicleVehicleLog
     *
     * @param \VehicleLogBundle\Entity\VehicleLog $vehicleVehicleLog
     */
    public function removeVehicleVehicleLog(\VehicleLogBundle\Entity\VehicleLog $vehicleVehicleLog)
    {
        $this->vehicleVehicleLogs->removeElement($vehicleVehicleLog);
    }

    /**
     * Get vehicleVehicleLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVehicleVehicleLogs()
    {
        return $this->vehicleVehicleLogs;
    }

    public function __toString(){
      return $this->name;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vehicleVehicleReservationPositions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vehicleVehicleReservations;


    /**
     * Add vehicleVehicleReservationPosition
     *
     * @param \VehicleLogBundle\Entity\VehicleReservationPosition $vehicleVehicleReservationPosition
     *
     * @return Vehicle
     */
    public function addVehicleVehicleReservationPosition(\VehicleLogBundle\Entity\VehicleReservationPosition $vehicleVehicleReservationPosition)
    {
        $this->vehicleVehicleReservationPositions[] = $vehicleVehicleReservationPosition;

        return $this;
    }

    /**
     * Remove vehicleVehicleReservationPosition
     *
     * @param \VehicleLogBundle\Entity\VehicleReservationPosition $vehicleVehicleReservationPosition
     */
    public function removeVehicleVehicleReservationPosition(\VehicleLogBundle\Entity\VehicleReservationPosition $vehicleVehicleReservationPosition)
    {
        $this->vehicleVehicleReservationPositions->removeElement($vehicleVehicleReservationPosition);
    }

    /**
     * Get vehicleVehicleReservationPositions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVehicleVehicleReservationPositions()
    {
        return $this->vehicleVehicleReservationPositions;
    }

    /**
     * Add vehicleVehicleReservation
     *
     * @param \VehicleLogBundle\Entity\VehicleReservation $vehicleVehicleReservation
     *
     * @return Vehicle
     */
    public function addVehicleVehicleReservation(\VehicleLogBundle\Entity\VehicleReservation $vehicleVehicleReservation)
    {
        $this->vehicleVehicleReservations[] = $vehicleVehicleReservation;

        return $this;
    }

    /**
     * Remove vehicleVehicleReservation
     *
     * @param \VehicleLogBundle\Entity\VehicleReservation $vehicleVehicleReservation
     */
    public function removeVehicleVehicleReservation(\VehicleLogBundle\Entity\VehicleReservation $vehicleVehicleReservation)
    {
        $this->vehicleVehicleReservations->removeElement($vehicleVehicleReservation);
    }

    /**
     * Get vehicleVehicleReservations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVehicleVehicleReservations()
    {
        return $this->vehicleVehicleReservations;
    }
    /**
     * @var string
     */
    private $serialNumber;


    /**
     * Set serialNumber
     *
     * @param string $serialNumber
     *
     * @return Vehicle
     */
    public function setSerialNumber($serialNumber)
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * Get serialNumber
     *
     * @return string
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    public function getManufactur()
    {
      $manufactur = "";
      switch (substr($this->serialNumber, 0, 3)) {
        case "YS2":
        case "VLU":
        case "XLE":
        case "YSR":
            $manufactur = "Scania";
            break;
        case "WMA":
            $manufactur = "MAN";
            break;
        default:
            $manufactur = "Andere";
            break;
      }
      return $manufactur;
    }

    /**
     * Set Pin
     *
     * @return VehicleLog
     */
    public function setPin($pin)
    {
        $this->pin = $pin;

        return $this;
    }

    /**
     * Get Pin
     *
     * @return boolean
     */
    public function getPin()
    {
        return $this->pin;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $consumption;


    /**
     * Add consumption
     *
     * @param \VehicleBundle\Entity\Consumption $consumption
     *
     * @return Vehicle
     */
    public function addConsumption(\VehicleBundle\Entity\Consumption $consumption)
    {
        $this->consumption[] = $consumption;

        return $this;
    }

    /**
     * Remove consumption
     *
     * @param \VehicleBundle\Entity\Consumption $consumption
     */
    public function removeConsumption(\VehicleBundle\Entity\Consumption $consumption)
    {
        $this->consumption->removeElement($consumption);
    }

    /**
     * Get consumption
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConsumption()
    {
        return $this->consumption;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $consumptions;


    /**
     * Get consumptions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConsumptions()
    {
        return $this->consumptions;
    }
    /**
     * @var \DateTime
     */
    private $deleted_at;


    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Vehicle
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tours;


    /**
     * Add tour
     *
     * @param \VehicleBundle\Entity\Tour $tour
     *
     * @return Vehicle
     */
    public function addTour(\VehicleBundle\Entity\Tour $tour)
    {
        $this->tours[] = $tour;

        return $this;
    }

    /**
     * Remove tour
     *
     * @param \VehicleBundle\Entity\Tour $tour
     */
    public function removeTour(\VehicleBundle\Entity\Tour $tour)
    {
        $this->tours->removeElement($tour);
    }

    /**
     * Get tours
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTours()
    {
        return $this->tours;
    }
}
