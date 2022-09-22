<?php

namespace EmployeeBundle\Entity;

/**
 * EmployeeImport
 */
class EmployeeImport
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var integer
     */
    private $komalogId;

    /**
     * @var string
     */
    private $trimbleId;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $prename;

    /**
     * @var string
     */
    private $salutation;

    /**
     * @var string
     */
    private $street;

    /**
     * @var string
     */
    private $zipCode;

    /**
     * @var string
     */
    private $town;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $fax;

    /**
     * @var string
     */
    private $mobile;

    /**
     * @var \DateTime
     */
    private $birthday;

    /**
     * @var \DateTime
     */
    private $entry_date;

    /**
     * @var string
     */
    private $initial;

    /**
     * @var string
     */
    private $email;

    /**
     * @var \DateTime
     */
    private $deleted_at;


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
     * Set komalogId
     *
     * @param integer $komalogId
     *
     * @return EmployeeImport
     */
    public function setKomalogId($komalogId)
    {
        $this->komalogId = $komalogId;

        return $this;
    }

    /**
     * Get komalogId
     *
     * @return integer
     */
    public function getKomalogId()
    {
        return $this->komalogId;
    }

    /**
     * Set trimbleId
     *
     * @param string $trimbleId
     *
     * @return EmployeeImport
     */
    public function setTrimbleId($trimbleId)
    {
        $this->trimbleId = $trimbleId;

        return $this;
    }

    /**
     * Get trimbleId
     *
     * @return string
     */
    public function getTrimbleId()
    {
        return $this->trimbleId;
    }

    /**
     * Set countryCode
     *
     * @param string $countryCode
     *
     * @return EmployeeImport
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return EmployeeImport
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
     * Set prename
     *
     * @param string $prename
     *
     * @return EmployeeImport
     */
    public function setPrename($prename)
    {
        $this->prename = $prename;

        return $this;
    }

    /**
     * Get prename
     *
     * @return string
     */
    public function getPrename()
    {
        return $this->prename;
    }

    /**
     * Set salutation
     *
     * @param string $salutation
     *
     * @return EmployeeImport
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;

        return $this;
    }

    /**
     * Get salutation
     *
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return EmployeeImport
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set zipCode
     *
     * @param string $zipCode
     *
     * @return EmployeeImport
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set town
     *
     * @param string $town
     *
     * @return EmployeeImport
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * Get town
     *
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return EmployeeImport
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set fax
     *
     * @param string $fax
     *
     * @return EmployeeImport
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     *
     * @return EmployeeImport
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     *
     * @return EmployeeImport
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set entryDate
     *
     * @param \DateTime $entryDate
     *
     * @return EmployeeImport
     */
    public function setEntryDate($entryDate)
    {
        $this->entry_date = $entryDate;

        return $this;
    }

    /**
     * Get entryDate
     *
     * @return \DateTime
     */
    public function getEntryDate()
    {
        return $this->entry_date;
    }

    /**
     * Set initial
     *
     * @param string $initial
     *
     * @return EmployeeImport
     */
    public function setInitial($initial)
    {
        $this->initial = $initial;

        return $this;
    }

    /**
     * Get initial
     *
     * @return string
     */
    public function getInitial()
    {
        return $this->initial;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return EmployeeImport
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return EmployeeImport
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

    public function isDeleted()
    {
        if (!is_null($this->deleted_at) && $this->deleted_at < (new \DateTime())) {
            return true;
        }
        return false;
    }
}
