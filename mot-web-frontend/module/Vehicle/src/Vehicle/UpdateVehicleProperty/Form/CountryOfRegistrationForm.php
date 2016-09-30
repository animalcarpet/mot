<?php

namespace Vehicle\UpdateVehicleProperty\Form;

use Core\Catalog\CountryOfRegistration\CountryOfRegistration;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;
use Zend\Form\Element\Select;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;

class CountryOfRegistrationForm extends Form
{
    const FIELD_COUNTRY_OF_REGISTRATION = 'country-of-registration';

    private $countryOfRegistrationElement;

    public function getCountryOfRegistrationElement()
    {
        return $this->countryOfRegistrationElement;
    }

    /**
     * CountryOfRegistrationForm constructor.
     * @param CountryOfRegistration[] $countries
     */
    public function __construct($countries)
    {
        parent::__construct();

        $countrySelectValue = ArrayUtils::mapWithKeys(
            $countries,
            function ($key, CountryOfRegistration $country) {
                return $country->getCode();
            },
            function ($key, CountryOfRegistration $country) {
                return $country->getName();
            }
        );

        TypeCheck::assertCollectionOfClass($countries, CountryOfRegistration::class);

        $this->countryOfRegistrationElement = new Select();

        $this->countryOfRegistrationElement
            ->setDisableInArrayValidator(true)
            ->setAttribute('type', 'select')
            ->setValueOptions($countrySelectValue)
            ->setName(self::FIELD_COUNTRY_OF_REGISTRATION)
            ->setLabel('Country of registration')
            ->setAttribute('id', 'countryOfRegistration')
            ->setAttribute('required', true)
            ->setAttribute('group', true);

        $this->add($this->countryOfRegistrationElement);

        $countryValidator = (new NotEmpty())->setMessage(" you must choose a country", NotEmpty::IS_EMPTY);
        $countryInArrayValidator = (new InArray())
            ->setHaystack(array_keys($countrySelectValue));

        $countryInArrayValidator->setMessage(" you must choose a country");
        $countryInput = new Input(self::FIELD_COUNTRY_OF_REGISTRATION);
        $countryInput
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($countryValidator)
            ->attach($countryInArrayValidator);

        $filter = new InputFilter();
        $filter->add($countryInput);

        $this->setInputFilter($filter);
    }
}
