<?php

namespace ExpenseBundle\Entity;

/**
 * Countryspecificexpenses
 */
class Countryspecificexpenses
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $countryISO;

    /**
     * @var string
     */
    private $countryName;

    /**
     * @var string
     */
    private $expenses8h;

    /**
     * @var string
     */
    private $expenses24h;


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
     * Set countryISO
     *
     * @param string $countryISO
     *
     * @return Countryspecificexpenses
     */
    public function setCountryISO($countryISO)
    {
        $this->countryISO = $countryISO;

        return $this;
    }

    /**
     * Get countryISO
     *
     * @return string
     */
    public function getCountryISO()
    {
        return $this->countryISO;
    }

    /**
     * Set countryName
     *
     * @param string $countryName
     *
     * @return Countryspecificexpenses
     */
    public function setCountryName($countryName)
    {
        $this->countryName = $countryName;

        return $this;
    }

    /**
     * Get countryName
     *
     * @return string
     */
    public function getCountryName()
    {
        return $this->countryName;
    }

    /**
     * Set expenses8h
     *
     * @param string $expenses8h
     *
     * @return Countryspecificexpenses
     */
    public function setExpenses8h($expenses8h)
    {
        $this->expenses8h = $expenses8h;

        return $this;
    }

    /**
     * Get expenses8h
     *
     * @return string
     */
    public function getExpenses8h()
    {
        return $this->expenses8h;
    }

    /**
     * Set expenses24h
     *
     * @param string $expenses24h
     *
     * @return Countryspecificexpenses
     */
    public function setExpenses24h($expenses24h)
    {
        $this->expenses24h = $expenses24h;

        return $this;
    }

    /**
     * Get expenses24h
     *
     * @return string
     */
    public function getExpenses24h()
    {
        return $this->expenses24h;
    }
}
