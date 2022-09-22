<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TelephoneList
 *
 * @ORM\Table(name="telephonelist")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TelephoneListRepository")
 */
class TelephoneList
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
     * @ORM\Column(name="Groupname", type="string", length=32, nullable=true)
     */
    private $groupname;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="symbol", type="integer")
     */
    private $symbol;

    /**
     * @var string
     *
     * @ORM\Column(name="telephone", type="string", length=32)
     */
    private $telephone;


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
     * Set groupname
     *
     * @param string $groupname
     *
     * @return TelephoneList
     */
    public function setGroupname($groupname)
    {
        $this->groupname = $groupname;
    
        return $this;
    }

    /**
     * Get groupname
     *
     * @return string
     */
    public function getGroupname()
    {
        return $this->groupname;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return TelephoneList
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
     * Set symbol
     *
     * @param integer $symbol
     *
     * @return TelephoneList
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;
    
        return $this;
    }

    /**
     * Get symbol
     *
     * @return integer
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     *
     * @return TelephoneList
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    
        return $this;
    }

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }
}

