<?php

namespace TrimbleSoapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tracedata
 */
class Expenseview
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $tracedataId;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tracedataId
     *
     * @param integer $tracedataId
     * @return Tracedata
     */
    public function setTracedataId($tracedataId)
    {
        $this->tracedataId = $tracedataId;

        return $this;
    }

    /**
     * Get tracedataId
     *
     * @return integer 
     */
    public function getTracedataId()
    {
        return $this->tracedataId;
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
     * @var integer
     */
    private $did;

    /**
     * Set did
     *
     * @param integer $did
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
     * @return integer
     */
    public function getDid()
    {
        return $this->did;
    }
}
