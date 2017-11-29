<?php

namespace App\Form;

use App\Validator;
use Zend\Form\Element\File;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * Class Verify
 * @package App\Form
 */
class Verify extends AbstractForm
{
    public function __construct(array $options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  Csrf field
        $this->addCsrfElement($inputFilter);

        //  Spreadsheet file
        $field = new File('spreadsheet');
        $input = new Input($field->getName());

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);
    }
}