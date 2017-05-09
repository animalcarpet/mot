<?php

namespace Site\UpdateVtsProperty\Process\Form;

use Core\Catalog\CountryCatalog;
use DvsaCommon\Model\CountryOfVts;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;
use Zend\Form\Element\Radio;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

class CountryPropertyForm extends Form
{
    const FIELD_COUNTRY = UpdateVtsPropertyAction::VTS_COUNTRY_PROPERTY;

    const COUNTRY_EMPTY_MSG = 'you must choose a country';

    private $countryElement;

    public function __construct(CountryCatalog $countryCatalog)
    {
        parent::__construct(self::FIELD_COUNTRY);

        $countryOptions = [];

        foreach (CountryOfVts::getPossibleCountryCodes() as $countryCode) {
            $vtsCountry = $countryCatalog->getByCode($countryCode);
            $countryOptions[] = [
                'label' => $vtsCountry->getName(),
                'value' => $vtsCountry->getCode(),
                'key' => $vtsCountry->getName(),
                'inputName' => self::FIELD_COUNTRY,
            ];
        }

        $this->countryElement = new Radio();
        $this->countryElement
            ->setValueOptions($countryOptions)
            ->setName(self::FIELD_COUNTRY)
            ->setLabel('Country')
            ->setAttribute('id', 'vtsCountry')
            ->setAttribute('required', true)
            ->setAttribute('group', true);

        $this->add($this->countryElement);

        $countryEmptyValidator = new NotEmpty();
        $countryInput = new Input(self::FIELD_COUNTRY);
        $countryInput
            ->setRequired(true)
            ->setErrorMessage(self::COUNTRY_EMPTY_MSG)
            ->getValidatorChain()
            ->attach($countryEmptyValidator);

        $filter = new InputFilter();
        $filter->add($countryInput);

        $this->setInputFilter($filter);
    }

    public function getCountryElement()
    {
        return $this->countryElement;
    }
}
