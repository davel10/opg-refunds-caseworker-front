<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use ArrayObject;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Verification as VerificationModel;
use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * Class Poa
 * @package App\Form
 */
class Poa extends AbstractForm
{
    /**
     * Poa constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  Csrf field
        $this->addCsrfElement($inputFilter);

        //  System field
        $field = new Element\Hidden('system');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Case number field
        $field = new Element\Text('case-number');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Received Date
        $receivedDate = new Fieldset\ReceivedDate();

        $this->add($receivedDate);
        $inputFilter->add($receivedDate->getInputFilter(), 'received-date');

        //  Original payment amount
        $field = new Element\Radio('original-payment-amount');
        $input = new Input($field->getName());

        $input->getValidatorChain()->attach(new Validator\NotEmpty);

        $field->setValueOptions([
            'orMore'   => 'orMore',
            'lessThan' => 'lessThan',
            'noRefund' => 'noRefund',
        ]);

        $this->add($field);
        $inputFilter->add($input);

        //  Validation
        //  Attorney details (always present)
        $field = new Element\Radio('attorney');
        $input = new Input($field->getName());

        $input->getValidatorChain()->attach(new Validator\NotEmpty);

        $field->setValueOptions([
            'yes' => 'yes',
            'no'  => 'no',
        ]);

        $this->add($field);
        $inputFilter->add($input);

        if (isset($options['claim'])) {
            /** @var ClaimModel $claim */
            $claim = $options['claim'];

            //  Donor postcode
            if ($claim->getApplication()->getPostcodes()->getDonorPostcode() !== null) {
                $field = new Element\Radio('donor-postcode');
                $input = new Input($field->getName());

                $input->getValidatorChain()->attach(new Validator\NotEmpty);

                $field->setValueOptions([
                    'yes' => 'yes',
                    'no' => 'no',
                ]);

                $this->add($field);
                $inputFilter->add($input);
            }

            //  Donor postcode
            if ($claim->getApplication()->getPostcodes()->getAttorneyPostcode() !== null) {
                $field = new Element\Radio('attorney-postcode');
                $input = new Input($field->getName());

                $input->getValidatorChain()->attach(new Validator\NotEmpty);

                $field->setValueOptions([
                    'yes' => 'yes',
                    'no' => 'no',
                ]);

                $this->add($field);
                $inputFilter->add($input);
            }
        }
    }

    /**
     * Get form data for creating model object
     *
     * @return array
     */
    public function getModelData()
    {
        $formData = $this->getData();

        //  If it exists transfer the received date array into a string
        if (array_key_exists('received-date', $formData)) {
            $receivedDateDateArr = $formData['received-date'];
            $receivedDateDateStr = null;
            if (!empty($receivedDateDateArr['year']) && !empty($receivedDateDateArr['month']) && !empty($receivedDateDateArr['day'])) {
                $receivedDateDateStr = $receivedDateDateArr['year'] . '-' . $receivedDateDateArr['month'] . '-' . $receivedDateDateArr['day'];
            }
            $formData['received-date'] = $receivedDateDateStr;
        }

        $verifications = [];
        if (array_key_exists('attorney', $formData)) {
            $verifications[] = [
                'type'   => VerificationModel::TYPE_ATTORNEY,
                'passes' => $formData['attorney'] === 'yes',
            ];
        }
        if (array_key_exists('donor-postcode', $formData)) {
            $verifications[] = [
                'type'   => VerificationModel::TYPE_DONOR_POSTCODE,
                'passes' => $formData['donor-postcode'] === 'yes',
            ];
        }
        if (array_key_exists('attorney-postcode', $formData)) {
            $verifications[] = [
                'type'   => VerificationModel::TYPE_ATTORNEY_POSTCODE,
                'passes' => $formData['attorney-postcode'] === 'yes',
            ];
        }

        $formData['verifications'] = $verifications;

        return $formData;
    }

    public function bindModelData(PoaModel $poa)
    {
        $poaArray = $poa->getArrayCopy();
        unset($poaArray['id']);
        unset($poaArray['system']);
        unset($poaArray['verifications']);

        $receivedDate = $poa->getReceivedDate();
        $poaArray['received-date'] = [
            'day' => $receivedDate->format('d'),
            'month' => $receivedDate->format('m'),
            'year' => $receivedDate->format('Y')
        ];

        foreach ($poa->getVerifications() as $verification) {
            //Case number verification is automatic and is not displayed on the page so do not include it
            if ($verification->getType() !== VerificationModel::TYPE_CASE_NUMBER) {
                $poaArray[$verification->getType()] = $verification->isPasses() ? 'yes' : 'no';
            }
        }

        parent::bind(new ArrayObject($poaArray));
    }
}