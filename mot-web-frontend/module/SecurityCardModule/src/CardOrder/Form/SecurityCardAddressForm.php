<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Form;

use Zend\Form\Element\Radio;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\I18n\Validator\PostCode;

class SecurityCardAddressForm extends Form
{
    const ADDRESS_LINE_1 = 'address1';
    const ADDRESS_LINE_2 = 'address2';
    const ADDRESS_LINE_3 = 'address3';
    const TOWN = 'townOrCity';
    const POSTCODE = 'postcode';
    const ADDRESS_RADIOS = 'addressChoice';
    const ADDRESS_MAX_LENGTH = 50;
    const CUSTOM_ADDRESS_VALUE = 'addressChoiceCustom';

    const NAME_FIELD_KEY = 'name';

    const ADDRESS_LINE_1_INVALID_MESSAGE = 'Please enter the first line of your address';
    const EXCEEDING_50_CHARACTERS_MESSAGE = 'must be 50, or less, characters long';
    const TOWN_INVALID_MESSAGE = 'Please enter the town or city';

    const MSG_POST_CODE_IS_EMPTY = "Please enter your postcode";
    const MSG_INVALID_POST_CODE = "Please enter a valid postcode";

    const MSG_INVALID_ADDRESS_CHOICE = "Select an existing address or enter a new one";

    public function __construct($homeAndSites)
    {
        parent::__construct();

        $this->add((new Radio())
            ->setName(self::ADDRESS_RADIOS)
            ->setLabel(self::ADDRESS_RADIOS)
            ->setAttribute('required', true)
            ->setValueOptions($this->getRadioOptions($homeAndSites))
            ->setOption('label_attributes', ['class' => 'block-label']));

        $this->add((new Text())
            ->setName(self::ADDRESS_LINE_1)
            ->setLabel('Address')
            ->setAttribute('id', self::ADDRESS_LINE_1)
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('autoCompleteOff', true)
        );
        $this->add((new Text())
            ->setName(self::ADDRESS_LINE_2)
            ->setAttribute('id', self::ADDRESS_LINE_2)
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('autoCompleteOff', true)
        );
        $this->add((new Text())
            ->setName(self::ADDRESS_LINE_3)
            ->setAttribute('id', self::ADDRESS_LINE_3)
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('autoCompleteOff', true)
        );
        $this->add((new Text())
            ->setName(self::TOWN)
            ->setLabel('Town or City')
            ->setAttribute('id', self::TOWN)
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('autoCompleteOff', true)
        );
        $this->add((new Text())
            ->setName(self::POSTCODE)
            ->setLabel('Postcode')
            ->setAttribute('id', self::POSTCODE)
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('autoCompleteOff', true)
        );
    }

    public function isValid()
    {
        $isValid = parent::isValid();
        $fieldsValid = true;
        $addressRadios = $this->getAddressRadios();

        if ($addressRadios->getValue() === self::CUSTOM_ADDRESS_VALUE) {
            $addressLine1 = $this->getAddressLine1();
            $addressLine2 = $this->getAddressLine2();
            $addressLine3 = $this->getAddressLine3();
            $town = $this->getTown();
            $postcode = $this->getPostcode();
            $postcode->setValue(strtoupper($postcode->getValue()));

            $postcodeValidator = new PostCode();
            $postcodeValidator->setLocale('en_GB');

            if ($addressLine1->getValue() == '') {
                $this->setCustomError($addressLine1, self::ADDRESS_LINE_1_INVALID_MESSAGE);
                $fieldsValid = false;
            } else if (strlen($addressLine1->getValue()) > self::ADDRESS_MAX_LENGTH) {
                $this->setCustomError($addressLine1, self::EXCEEDING_50_CHARACTERS_MESSAGE);
                $fieldsValid = false;
            }

            if (strlen($addressLine2->getValue()) > self::ADDRESS_MAX_LENGTH) {
                $this->setCustomError($addressLine2, self::EXCEEDING_50_CHARACTERS_MESSAGE);
                $fieldsValid = false;
            }

            if (strlen($addressLine3->getValue()) > self::ADDRESS_MAX_LENGTH) {
                $this->setCustomError($addressLine3, self::EXCEEDING_50_CHARACTERS_MESSAGE);
                $fieldsValid = false;
            }

            if ($town->getValue() == '') {
                $this->setCustomError($town, self::TOWN_INVALID_MESSAGE);
                $fieldsValid = false;
            } else if (strlen($town->getValue()) > self::ADDRESS_MAX_LENGTH) {
                $this->setCustomError($town, self::EXCEEDING_50_CHARACTERS_MESSAGE);
                $fieldsValid = false;
            }

            if ($postcode->getValue() == '') {
                $this->setCustomError($postcode, self::MSG_POST_CODE_IS_EMPTY);
                $fieldsValid = false;
            }

            if (!$postcodeValidator->isValid($postcode->getValue())) {
                $this->setCustomError($postcode, self::MSG_INVALID_POST_CODE);
                $fieldsValid = false;
            }

            $this->showLabelOnError(self::ADDRESS_LINE_1, 'Address Line 1');
            $this->showLabelOnError(self::ADDRESS_LINE_2, 'Address Line 2');
            $this->showLabelOnError(self::ADDRESS_LINE_3, 'Address Line 3');
            $this->showLabelOnError(self::TOWN, 'Town');
            $this->showLabelOnError(self::POSTCODE, 'Postcode');
        } else if ($addressRadios->getValue() == '') {
            $this->setCustomError($addressRadios, "Address Choice");
            $this->showLabelOnError(self::ADDRESS_RADIOS, self::MSG_INVALID_ADDRESS_CHOICE);
            $fieldsValid = false;
        }

        return $isValid && $fieldsValid;
    }

    private function showLabelOnError($field, $label)
    {
        if (count($this->getMessages($field))) {
            $this->getElements()[$field]->setLabel($label);
        }
    }

    /**
     * @param $field
     * @param $error
     */
    public function setCustomError($field, $error)
    {
        $field->setMessages([$error]);
    }

    public function getAddressLine1()
    {
        return $this->get(self::ADDRESS_LINE_1);
    }

    public function getAddressLine2()
    {
        return $this->get(self::ADDRESS_LINE_2);
    }

    public function getAddressLine3()
    {
        return $this->get(self::ADDRESS_LINE_3);
    }

    public function getTown()
    {
        return $this->get(self::TOWN);
    }

    public function getPostcode()
    {
        return $this->get(self::POSTCODE);
    }

    /**
     * @return \Zend\Form\ElementInterface
     */
    public function getAddressRadios()
    {
        return $this->get(self::ADDRESS_RADIOS);
    }

    public function isCustomAddressChosen()
    {
        return $this->getAddressRadios()->getValue() === self::CUSTOM_ADDRESS_VALUE;
    }

    private function getRadioOptions($homeAndSiteAddresses)
    {
        $addresses = [];

        foreach($homeAndSiteAddresses as $key => $address) {
            $addresses[$key] = [
                'value' => $key,
                'meta' => $address['addressString'],
                'name' => 'addressChoice',
                'title' => $address['name'],
                'id' => 'addressChoice' . $key,
            ];
        }

        array_push($addresses, array(
            'value' => self::CUSTOM_ADDRESS_VALUE,
            'name' => 'addressChoice',
            'title' => 'Enter new Address',
            'ariaContainer' => 'customAddressContainer',
            'id' => self::CUSTOM_ADDRESS_VALUE,
        ));

        return $addresses;
    }
}
