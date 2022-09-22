<?php

namespace TrimbleSoapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tracetype
 */
class Tracetype
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
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Tracetype
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
     * Set id
     *
     * @param integer $id
     * @return Tracetype
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tracedatas;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tracedatas = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add tracedatas
     *
     * @param \TrimbleSoapBundle\Entity\Tracedata $tracedatas
     * @return Tracetype
     */
    public function addTracedata(\TrimbleSoapBundle\Entity\Tracedata $tracedatas)
    {
        $this->tracedatas[] = $tracedatas;

        return $this;
    }

    /**
     * Remove tracedatas
     *
     * @param \TrimbleSoapBundle\Entity\Tracedata $tracedatas
     */
    public function removeTracedata(\TrimbleSoapBundle\Entity\Tracedata $tracedatas)
    {
        $this->tracedatas->removeElement($tracedatas);
    }

    /**
     * Get tracedatas
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTracedatas()
    {
        return $this->tracedatas;
    }
}
