<?php

namespace VehicleBundle\Entity;

/**
 * Tourdetail
 */
class Tourdetail
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $beginn;

    /**
     * @var \DateTime
     */
    private $ende;

    /**
     * @var string
     */
    private $leerkm;

    /**
     * @var string
     */
    private $lastkm;

    /**
     * @var string
     */
    private $gesamtkm;

    /**
     * @var string
     */
    private $leerzeit;

    /**
     * @var string
     */
    private $lastzeit;

    /**
     * @var string
     */
    private $gesamtzeit;

    /**
     * @var string
     */
    private $leerkosten;

    /**
     * @var string
     */
    private $lastkosten;

    /**
     * @var string
     */
    private $gesamtkosten;

    /**
     * @var string
     */
    private $erloes;


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
     * Set beginn
     *
     * @param \DateTime $beginn
     *
     * @return Tourdetail
     */
    public function setBeginn($beginn)
    {
        $this->beginn = $beginn;

        return $this;
    }

    /**
     * Get beginn
     *
     * @return \DateTime
     */
    public function getBeginn()
    {
        return $this->beginn;
    }

    /**
     * Set ende
     *
     * @param \DateTime $ende
     *
     * @return Tourdetail
     */
    public function setEnde($ende)
    {
        $this->ende = $ende;

        return $this;
    }

    /**
     * Get ende
     *
     * @return \DateTime
     */
    public function getEnde()
    {
        return $this->ende;
    }

    /**
     * Set leerkm
     *
     * @param string $leerkm
     *
     * @return Tourdetail
     */
    public function setLeerkm($leerkm)
    {
        $this->leerkm = $leerkm;

        return $this;
    }

    /**
     * Get leerkm
     *
     * @return string
     */
    public function getLeerkm()
    {
        return $this->leerkm;
    }

    /**
     * Set lastkm
     *
     * @param string $lastkm
     *
     * @return Tourdetail
     */
    public function setLastkm($lastkm)
    {
        $this->lastkm = $lastkm;

        return $this;
    }

    /**
     * Get lastkm
     *
     * @return string
     */
    public function getLastkm()
    {
        return $this->lastkm;
    }

    /**
     * Set gesamtkm
     *
     * @param string $gesamtkm
     *
     * @return Tourdetail
     */
    public function setGesamtkm($gesamtkm)
    {
        $this->gesamtkm = $gesamtkm;

        return $this;
    }

    /**
     * Get gesamtkm
     *
     * @return string
     */
    public function getGesamtkm()
    {
        return $this->gesamtkm;
    }

    /**
     * Set leerzeit
     *
     * @param string $leerzeit
     *
     * @return Tourdetail
     */
    public function setLeerzeit($leerzeit)
    {
        $this->leerzeit = $leerzeit;

        return $this;
    }

    /**
     * Get leerzeit
     *
     * @return string
     */
    public function getLeerzeit()
    {
        return $this->leerzeit;
    }

    /**
     * Set lastzeit
     *
     * @param string $lastzeit
     *
     * @return Tourdetail
     */
    public function setLastzeit($lastzeit)
    {
        $this->lastzeit = $lastzeit;

        return $this;
    }

    /**
     * Get lastzeit
     *
     * @return string
     */
    public function getLastzeit()
    {
        return $this->lastzeit;
    }

    /**
     * Set gesamtzeit
     *
     * @param string $gesamtzeit
     *
     * @return Tourdetail
     */
    public function setGesamtzeit($gesamtzeit)
    {
        $this->gesamtzeit = $gesamtzeit;

        return $this;
    }

    /**
     * Get gesamtzeit
     *
     * @return string
     */
    public function getGesamtzeit()
    {
        return $this->gesamtzeit;
    }

    /**
     * Set leerkosten
     *
     * @param string $leerkosten
     *
     * @return Tourdetail
     */
    public function setLeerkosten($leerkosten)
    {
        $this->leerkosten = $leerkosten;

        return $this;
    }

    /**
     * Get leerkosten
     *
     * @return string
     */
    public function getLeerkosten()
    {
        return $this->leerkosten;
    }

    /**
     * Set lastkosten
     *
     * @param string $lastkosten
     *
     * @return Tourdetail
     */
    public function setLastkosten($lastkosten)
    {
        $this->lastkosten = $lastkosten;

        return $this;
    }

    /**
     * Get lastkosten
     *
     * @return string
     */
    public function getLastkosten()
    {
        return $this->lastkosten;
    }

    /**
     * Set gesamtkosten
     *
     * @param string $gesamtkosten
     *
     * @return Tourdetail
     */
    public function setGesamtkosten($gesamtkosten)
    {
        $this->gesamtkosten = $gesamtkosten;

        return $this;
    }

    /**
     * Get gesamtkosten
     *
     * @return string
     */
    public function getGesamtkosten()
    {
        return $this->gesamtkosten;
    }

    /**
     * Set erloes
     *
     * @param string $erloes
     *
     * @return Tourdetail
     */
    public function setErloes($erloes)
    {
        $this->erloes = $erloes;

        return $this;
    }

    /**
     * Get erloes
     *
     * @return string
     */
    public function getErloes()
    {
        return $this->erloes;
    }
    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @var \DateTime
     */
    private $deleted_at;


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Tourdetail
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Tourdetail
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Tourdetail
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deleted_at = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }
}
