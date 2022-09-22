<?php

namespace EmployeeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkingHoursPause
 *
 * @ORM\Table(name="working_hours_pause")
 * @ORM\Entity(repositoryClass="EmployeeBundle\Repository\WorkingHoursPauseRepository")
 */
class WorkingHoursPause
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="from_time", type="decimal", precision=10, scale=2)
     */
    private $fromTime;

    /**
     * @var string
     *
     * @ORM\Column(name="to_time", type="decimal", precision=10, scale=2)
     */
    private $toTime;

    /**
     * @var string
     *
     * @ORM\Column(name="rate", type="decimal", precision=10, scale=2)
     */
    private $rate;


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
     * Set fromTime
     *
     * @param string $fromTime
     *
     * @return WorkingHoursPause
     */
    public function setFromTime($fromTime)
    {
        $this->fromTime = $fromTime;

        return $this;
    }

    /**
     * Get fromTime
     *
     * @return string
     */
    public function getFromTime()
    {
        return $this->fromTime;
    }

    /**
     * Set toTime
     *
     * @param string $toTime
     *
     * @return WorkingHoursPause
     */
    public function setToTime($toTime)
    {
        $this->toTime = $toTime;

        return $this;
    }

    /**
     * Get toTime
     *
     * @return string
     */
    public function getToTime()
    {
        return $this->toTime;
    }

    /**
     * Set rate
     *
     * @param string $rate
     *
     * @return WorkingHoursPause
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return string
     */
    public function getRate()
    {
        return $this->rate;
    }
}

