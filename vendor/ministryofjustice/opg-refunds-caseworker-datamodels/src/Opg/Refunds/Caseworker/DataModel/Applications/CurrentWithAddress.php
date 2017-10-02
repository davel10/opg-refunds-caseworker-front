<?php

namespace Opg\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Common\Address;

class CurrentWithAddress extends Current
{
    /**
     * @var Address
     */
    protected $address;

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
            case 'address':
                return (($value instanceof Address || is_null($value)) ? $value : new Address($value));
            default:
                return parent::map($property, $value);
        }
    }
}