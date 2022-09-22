<?php

namespace AbsenceBundle\Entity;

/**
 * Vacationlock
 */
class Vacationlock
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $year;

    /**
     * @var array
     */
    private $days;


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
     * Set year
     *
     * @param integer $year
     *
     * @return Vacationlock
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set days
     *
     * @param array $days
     *
     * @return Vacationlock
     */
    public function setDays($days)
    {
        $this->days = $days;

        return $this;
    }

    /**
     * Get days
     *
     * @return array
     */
    public function getDays()
    {
        return $this->days;
    }

    public function __toString()
    {
        return (String) $this->year;
    }
}
