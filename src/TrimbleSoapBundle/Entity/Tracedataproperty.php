<?php

namespace TrimbleSoapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tracedataproperty
 */
class Tracedataproperty
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $propertyKey;

    /**
     * @var string
     */
    private $propertyValue;


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
     * Set propertyKey
     *
     * @param string $propertyKey
     * @return Tracedataproperty
     */
    public function setPropertyKey($propertyKey)
    {
        $this->propertyKey = $propertyKey;

        return $this;
    }

    /**
     * Get propertyKey
     *
     * @return string 
     */
    public function getPropertyKey()
    {
        return $this->propertyKey;
    }

    /**
     * Set propertyValue
     *
     * @param string $propertyValue
     * @return Tracedataproperty
     */
    public function setPropertyValue($propertyValue)
    {
        $this->propertyValue = $propertyValue;

        return $this;
    }

    /**
     * Get propertyValue
     *
     * @return string 
     */
    public function getPropertyValue()
    {
        return $this->propertyValue;
    }
    /**
     * @var \TrimbleSoapBundle\Entity\Tracedata
     */
    private $tracedata;


    /**
     * Set tracedata
     *
     * @param \TrimbleSoapBundle\Entity\Tracedata $tracedata
     * @return Tracedataproperty
     */
    public function setTracedata(\TrimbleSoapBundle\Entity\Tracedata $tracedata = null)
    {
        $this->tracedata = $tracedata;

        return $this;
    }

    /**
     * Get tracedata
     *
     * @return \TrimbleSoapBundle\Entity\Tracedata 
     */
    public function getTracedata()
    {
        return $this->tracedata;
    }
}
