<?php

namespace TrimbleSoapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tracedata
 */
class Tracedata
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $source;

    /**
     * @var \DateTime
     */
    private $time;

    /**
     * @var string
     */
    private $lat;

    /**
     * @var string
     */
    private $lon;

    /**
     * @var int
     */
    private $mileage;

    /**
     * @var int
     */
    private $heading;

    /**
     * @var int
     */
    private $speed;


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
     * Set type
     *
     * @param integer $type
     * @return Tracedata
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set source
     *
     * @param string $source
     * @return Tracedata
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set time
     *
     * @param \DateTime $time
     * @return Tracedata
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime 
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set lat
     *
     * @param string $lat
     * @return Tracedata
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return string 
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lon
     *
     * @param string $lon
     * @return Tracedata
     */
    public function setLon($lon)
    {
        $this->lon = $lon;

        return $this;
    }

    /**
     * Get lon
     *
     * @return string 
     */
    public function getLon()
    {
        return $this->lon;
    }

    /**
     * Set mileage
     *
     * @param integer $mileage
     * @return Tracedata
     */
    public function setMileage($mileage)
    {
        $this->mileage = $mileage;

        return $this;
    }

    /**
     * Get mileage
     *
     * @return integer 
     */
    public function getMileage()
    {
        return $this->mileage;
    }

    /**
     * Set heading
     *
     * @param integer $heading
     * @return Tracedata
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * Get heading
     *
     * @return integer 
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * Set speed
     *
     * @param integer $speed
     * @return Tracedata
     */
    public function setSpeed($speed)
    {
        $this->speed = $speed;

        return $this;
    }

    /**
     * Get speed
     *
     * @return integer 
     */
    public function getSpeed()
    {
        return $this->speed;
    }
    /**
     * @var \TrimbleSoapBundle\Entity\Tracepolldata
     */
    private $tracepolldata;


    /**
     * Set tracepolldata
     *
     * @param \TrimbleSoapBundle\Entity\Tracepolldata $tracepolldata
     * @return Tracedata
     */
    public function setTracepolldata(\TrimbleSoapBundle\Entity\Tracepolldata $tracepolldata = null)
    {
        $this->tracepolldata = $tracepolldata;

        return $this;
    }

    /**
     * Get tracepolldata
     *
     * @return \TrimbleSoapBundle\Entity\Tracepolldata 
     */
    public function getTracepolldata()
    {
        return $this->tracepolldata;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tracedataproperties;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tracedataproperties = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add tracedataproperties
     *
     * @param \TrimbleSoapBundle\Entity\Tracedataproperty $tracedataproperties
     * @return Tracedata
     */
    public function addTracedataproperty(\TrimbleSoapBundle\Entity\Tracedataproperty $tracedataproperties)
    {
        $this->tracedataproperties[] = $tracedataproperties;

        return $this;
    }

    /**
     * Remove tracedataproperties
     *
     * @param \TrimbleSoapBundle\Entity\Tracedataproperty $tracedataproperties
     */
    public function removeTracedataproperty(\TrimbleSoapBundle\Entity\Tracedataproperty $tracedataproperties)
    {
        $this->tracedataproperties->removeElement($tracedataproperties);
    }

    /**
     * Get tracedataproperties
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTracedataproperties()
    {
        return $this->tracedataproperties;
    }
    /**
     * @var \TrimbleSoapBundle\Entity\Tracetype
     */
    private $tracetype;


    /**
     * Set tracetype
     *
     * @param \TrimbleSoapBundle\Entity\Tracetype $tracetype
     * @return Tracedata
     */
    public function setTracetype(\TrimbleSoapBundle\Entity\Tracetype $tracetype = null)
    {
        $this->tracetype = $tracetype;

        return $this;
    }

    /**
     * Get tracetype
     *
     * @return \TrimbleSoapBundle\Entity\Tracetype 
     */
    public function getTracetype()
    {
        return $this->tracetype;
    }
    /**
     * @var string
     */
    private $did;


    /**
     * Set did
     *
     * @param string $did
     *
     * @return Tracedata
     */
    public function setDid($did)
    {
        $this->did = $did;

        return $this;
    }

    /**
     * Get did
     *
     * @return string
     */
    public function getDid()
    {
        return $this->did;
    }
}
