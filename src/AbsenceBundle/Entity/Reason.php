<?php

namespace AbsenceBundle\Entity;

/**
 * Reason
 */
class Reason
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
     * @var string
     */
    private $abbr;


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
     * @return Reason
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
     * Set abbr
     *
     * @param string $abbr
     *
     * @return Reason
     */
    public function setAbbr($abbr)
    {
        $this->abbr = $abbr;

        return $this;
    }

    /**
     * Get abbr
     *
     * @return string
     */
    public function getAbbr()
    {
        return $this->abbr;
    }

    public function __toString(){
      return $this->name;
    }
    /**
     * @var string
     */
    private $color;


    /**
     * Set color
     *
     * @param string $color
     *
     * @return Reason
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }
    /**
     * @var boolean
     */
    private $use_as_holidays;


    /**
     * Set useAsHolidays
     *
     * @param boolean $useAsHolidays
     *
     * @return Reason
     */
    public function setUseAsHolidays($useAsHolidays)
    {
        $this->use_as_holidays = $useAsHolidays;

        return $this;
    }

    /**
     * Get useAsHolidays
     *
     * @return boolean
     */
    public function getUseAsHolidays()
    {
        return $this->use_as_holidays;
    }
}
