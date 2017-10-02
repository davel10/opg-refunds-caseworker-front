<?php

namespace Opg\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Opg\Refunds\Caseworker\DataModel\Common\Name;
use DateTime;

/**
 * Class Donor
 * @package Opg\Refunds\Caseworker\DataModel\Applications
 */
class Donor extends AbstractDataModel
{
    /**
     * @var Current
     */
    protected $current;

    /**
     * @var Poa
     */
    protected $poa;

    /**
     * @return Current
     */
    public function getCurrent(): Current
    {
        return $this->current;
    }

    /**
     * @param Current $current
     * @return $this
     */
    public function setCurrent(Current $current): Donor
    {
        $this->current = $current;

        return $this;
    }

    /**
     * @return Poa
     */
    public function getPoa(): Poa
    {
        return $this->poa;
    }

    /**
     * @param Poa $poa
     * @return Donor
     */
    public function setPoa(Poa $poa): Donor
    {
        $this->poa = $poa;

        return $this;
    }

    /**
     * Map properties to correct types
     *
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    protected function map($property, $value)
    {
        switch ($property) {
            case 'current':
                return (($value instanceof Current || is_null($value)) ? $value : new Current($value));
            case 'poa':
                return (($value instanceof Poa || is_null($value)) ? $value : new Poa($value));
            default:
                return parent::map($property, $value);
        }
    }
}