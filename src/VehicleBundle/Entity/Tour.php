<?php

namespace VehicleBundle\Entity;

/**
 * Tour
 */
class Tour
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $tourid;

    /**
     * @var string
     */
    private $tournummer;

    /**
     * @var string
     */
    private $gewicht;

    /**
     * @var string
     */
    private $rechnungsempfaenger;

    /**
     * @var string
     */
    private $beladeort;

    /**
     * @var string
     */
    private $entladeort;

    /**
     * @var string
     */
    private $empfangsorte;

    /**
     * @var int
     */
    private $anzahlauftraege;

    /**
     * @var int
     */
    private $geprueft;

    /**
     * @var string
     */
    private $beschreibung;


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
     * Set tourid
     *
     * @param integer $tourid
     *
     * @return Tour
     */
    public function setTourid($tourid)
    {
        $this->tourid = $tourid;

        return $this;
    }

    /**
     * Get tourid
     *
     * @return int
     */
    public function getTourid()
    {
        return $this->tourid;
    }

    /**
     * Set tournummer
     *
     * @param string $tournummer
     *
     * @return Tour
     */
    public function setTournummer($tournummer)
    {
        $this->tournummer = $tournummer;

        return $this;
    }

    /**
     * Get tournummer
     *
     * @return string
     */
    public function getTournummer()
    {
        return $this->tournummer;
    }

    /**
     * Set gewicht
     *
     * @param string $gewicht
     *
     * @return Tour
     */
    public function setGewicht($gewicht)
    {
        $this->gewicht = $gewicht;

        return $this;
    }

    /**
     * Get gewicht
     *
     * @return string
     */
    public function getGewicht()
    {
        return $this->gewicht;
    }

    /**
     * Set rechnungsempfaenger
     *
     * @param string $rechnungsempfaenger
     *
     * @return Tour
     */
    public function setRechnungsempfaenger($rechnungsempfaenger)
    {
        $this->rechnungsempfaenger = $rechnungsempfaenger;

        return $this;
    }

    /**
     * Get rechnungsempfaenger
     *
     * @return string
     */
    public function getRechnungsempfaenger()
    {
        return $this->rechnungsempfaenger;
    }

    /**
     * Set beladeort
     *
     * @param string $beladeort
     *
     * @return Tour
     */
    public function setBeladeort($beladeort)
    {
        $this->beladeort = $beladeort;

        return $this;
    }

    /**
     * Get beladeort
     *
     * @return string
     */
    public function getBeladeort()
    {
        return $this->beladeort;
    }

    /**
     * Set entladeort
     *
     * @param string $entladeort
     *
     * @return Tour
     */
    public function setEntladeort($entladeort)
    {
        $this->entladeort = $entladeort;

        return $this;
    }

    /**
     * Get entladeort
     *
     * @return string
     */
    public function getEntladeort()
    {
        return $this->entladeort;
    }

    /**
     * Set empfangsorte
     *
     * @param string $empfangsorte
     *
     * @return Tour
     */
    public function setEmpfangsorte($empfangsorte)
    {
        $this->empfangsorte = $empfangsorte;

        return $this;
    }

    /**
     * Get empfangsorte
     *
     * @return string
     */
    public function getEmpfangsorte()
    {
        return $this->empfangsorte;
    }

    /**
     * Set anzahlauftraege
     *
     * @param integer $anzahlauftraege
     *
     * @return Tour
     */
    public function setAnzahlauftraege($anzahlauftraege)
    {
        $this->anzahlauftraege = $anzahlauftraege;

        return $this;
    }

    /**
     * Get anzahlauftraege
     *
     * @return int
     */
    public function getAnzahlauftraege()
    {
        return $this->anzahlauftraege;
    }

    /**
     * Set geprueft
     *
     * @param integer $geprueft
     *
     * @return Tour
     */
    public function setGeprueft($geprueft)
    {
        $this->geprueft = $geprueft;

        return $this;
    }

    /**
     * Get geprueft
     *
     * @return int
     */
    public function getGeprueft()
    {
        return $this->geprueft;
    }

    /**
     * Set beschreibung
     *
     * @param string $beschreibung
     *
     * @return Tour
     */
    public function setBeschreibung($beschreibung)
    {
        $this->beschreibung = $beschreibung;

        return $this;
    }

    /**
     * Get beschreibung
     *
     * @return string
     */
    public function getBeschreibung()
    {
        return $this->beschreibung;
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
     * @var integer
     */
    private $trimbleTourid;

    /**
     * @var \VehicleBundle\Entity\Tourdetail
     */
    private $originalDetail;

    /**
     * @var \VehicleBundle\Entity\Tourdetail
     */
    private $currentDetail;

    /**
     * @var \VehicleBundle\Entity\Vehicle
     */
    private $vehicle;


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Tour
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
     * @return Tour
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
     * @return Tour
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

    /**
     * Set trimbleTourid
     *
     * @param integer $trimbleTourid
     *
     * @return Tour
     */
    public function setTrimbleTourid($trimbleTourid)
    {
        $this->trimbleTourid = $trimbleTourid;

        return $this;
    }

    /**
     * Get trimbleTourid
     *
     * @return integer
     */
    public function getTrimbleTourid()
    {
        return $this->trimbleTourid;
    }

    /**
     * Set originalDetail
     *
     * @param \VehicleBundle\Entity\Tourdetail $originalDetail
     *
     * @return Tour
     */
    public function setOriginalDetail(\VehicleBundle\Entity\Tourdetail $originalDetail = null)
    {
        $this->originalDetail = $originalDetail;

        return $this;
    }

    /**
     * Get originalDetail
     *
     * @return \VehicleBundle\Entity\Tourdetail
     */
    public function getOriginalDetail()
    {
        return $this->originalDetail;
    }

    /**
     * Set currentDetail
     *
     * @param \VehicleBundle\Entity\Tourdetail $currentDetail
     *
     * @return Tour
     */
    public function setCurrentDetail(\VehicleBundle\Entity\Tourdetail $currentDetail = null)
    {
        $this->currentDetail = $currentDetail;

        return $this;
    }

    /**
     * Get currentDetail
     *
     * @return \VehicleBundle\Entity\Tourdetail
     */
    public function getCurrentDetail()
    {
        return $this->currentDetail;
    }

    /**
     * Set vehicle
     *
     * @param \VehicleBundle\Entity\Vehicle $vehicle
     *
     * @return Tour
     */
    public function setVehicle(\VehicleBundle\Entity\Vehicle $vehicle = null)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * Get vehicle
     *
     * @return \VehicleBundle\Entity\Vehicle
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }
}
