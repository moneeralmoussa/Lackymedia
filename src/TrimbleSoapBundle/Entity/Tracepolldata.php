<?php

namespace TrimbleSoapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tracepolldata
 */
class Tracepolldata
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $mark;

    /**
     * @var bool
     */
    private $more;

    /**
     * @var bool
     */
    private $active;


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
     * Set mark
     *
     * @param string $mark
     * @return Tracepolldata
     */
    public function setMark($mark)
    {
        $this->mark = $mark;

        return $this;
    }

    /**
     * Get mark
     *
     * @return string 
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * Set more
     *
     * @param boolean $more
     * @return Tracepolldata
     */
    public function setMore($more)
    {
        $this->more = $more;

        return $this;
    }

    /**
     * Get more
     *
     * @return boolean 
     */
    public function getMore()
    {
        return $this->more;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Tracepolldata
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
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
     * @return Tracepolldata
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
