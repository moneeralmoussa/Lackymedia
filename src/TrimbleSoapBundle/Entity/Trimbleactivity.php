<?php

namespace TrimbleSoapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trimbleactivity
 */
class Trimbleactivity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $activityKey;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $messageType;


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
     * Set activityKey
     *
     * @param string $activityKey
     * @return Trimbleactivity
     */
    public function setActivityKey($activityKey)
    {
        $this->activityKey = $activityKey;

        return $this;
    }

    /**
     * Get activityKey
     *
     * @return string 
     */
    public function getActivityKey()
    {
        return $this->activityKey;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Trimbleactivity
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
     * Set messageType
     *
     * @param string $messageType
     * @return Trimbleactivity
     */
    public function setMessageType($messageType)
    {
        $this->messageType = $messageType;

        return $this;
    }

    /**
     * Get messageType
     *
     * @return string 
     */
    public function getMessageType()
    {
        return $this->messageType;
    }
}
