<?php

namespace Organisation\UpdateAeProperty\Process\Form;

use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine1Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine2Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine3Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\CountryInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\DateOfBirthInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\FamilyNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\FirstNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\MiddleNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\PostcodeInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\TownInput;
use DvsaCommon\InputFilter\AuthorisedExaminerPrincipal\CreateAepInputFilter;
use Zend\Form\Element\Text;
use Zend\Form\Form;

class AepForm extends Form
{
    const FIELD_DOB_DAY = 'dobDay';
    const FIELD_DOB_MONTH = 'dobMonth';
    const FIELD_DOB_YEAR = 'dobYear';

    public function __construct()
    {
        parent::__construct();

        $this->add((new Text())
            ->setName(FirstNameInput::FIELD)
            ->setLabel('First name')
            ->setAttribute('id', 'first-name')
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group-compound')
        );

        $this->add((new Text())
            ->setName(MiddleNameInput::FIELD)
            ->setLabel('Middle name (optional)')
            ->setAttribute('id', 'middle-name')
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group-compound')
        );

        $this->add((new Text())
            ->setName(FamilyNameInput::FIELD)
            ->setLabel('Last name')
            ->setAttribute('id', 'last-name')
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group-compound')
        );

        $this->add((new Text())
            ->setName(self::FIELD_DOB_DAY)
            ->setLabel('Day')
            ->setAttribute('id', 'dob-day')
            ->setAttribute('required', true)
            ->setAttribute('divModifier', 'form-group-compound')
        );

        $this->add((new Text())
            ->setName(self::FIELD_DOB_MONTH)
            ->setLabel('Month')
            ->setAttribute('id', 'dob-month')
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group-compound')
        );

        $this->add((new Text())
            ->setName(self::FIELD_DOB_YEAR)
            ->setLabel('Year')
            ->setAttribute('id', 'dob-year')
            ->setAttribute('required', true)
            ->setAttribute('group', true)
        );

        $this->add((new Text())
            ->setName(AddressLine1Input::FIELD)
            ->setLabel('Address')
            ->setAttribute('id', 'address-line1')
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group-compound')
        );

        $this->add((new Text())
            ->setName(AddressLine2Input::FIELD)
            ->setAttribute('id', 'address-line2')
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group-compound')
        );

        $this->add((new Text())
            ->setName(AddressLine3Input::FIELD)
            ->setAttribute('id', 'address-line3')
            ->setAttribute('required', false)
            ->setAttribute('group', true)
        );

        $this->add((new Text())
            ->setName(TownInput::FIELD)
            ->setLabel('Town or city')
            ->setAttribute('id', 'town')
            ->setAttribute('required', true)
            ->setAttribute('group', true)
        );

        $this->add((new Text())
            ->setName(CountryInput::FIELD)
            ->setLabel('Country (optional)')
            ->setAttribute('id', 'country')
            ->setAttribute('required', false)
            ->setAttribute('group', true)
        );
        $this->add((new Text())
            ->setName(PostcodeInput::FIELD)
            ->setLabel('Postcode')
            ->setAttribute('id', 'postcode')
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('inputModifier', '1-8')
        );

        $filter = new CreateAepInputFilter();
        $filter->init();
        $filter->remove(DateOfBirthInput::FIELD);

        $this->setInputFilter($filter);
    }

    public function isValid()
    {
        $isFormValid = parent::isValid();
        $isDateOfBirthValid = $this->isValidDateOfBirth();

        $isValid = $isFormValid && $isDateOfBirthValid;
        if (!$isValid) {
            $this->showLabelOnError(AddressLine2Input::FIELD, 'Address Line 2');
            $this->showLabelOnError(AddressLine3Input::FIELD, 'Address Line 3');
            $this->showLabelOnError(self::FIELD_DOB_DAY, 'Date of birth');
        }

        return $isValid;
    }

    private function isValidDateOfBirth()
    {
        $dobInput = new DateOfBirthInput();
        $dobInput->setValue($this->getDob());

        $isValid = $dobInput->isValid();
        if ($isValid === false) {
            $this->getDobDayElement()->setMessages($dobInput->getMessages());
        }

        return $isValid;
    }

    private function getDob()
    {
        $year = $this->getDobYearElement()->getValue();
        $month = $this->getDobMonthElement()->getValue();
        $day = $this->getDobDayElement()->getValue();

        if (empty($year) && empty($month) && empty($day)) {
            return '';
        }

        return implode('-', [$year, $month, $day]);
    }

    private function showLabelOnError($field, $label)
    {
        if (count($this->getMessages($field))) {
            $this->get($field)->setLabel($label);
        }
    }

    public function getFirstNameElement()
    {
        return $this->get(FirstNameInput::FIELD);
    }

    public function getMiddleNameElement()
    {
        return $this->get(MiddleNameInput::FIELD);
    }

    public function getLastNameElement()
    {
        return $this->get(FamilyNameInput::FIELD);
    }

    public function getDobDayElement()
    {
        return $this->get(self::FIELD_DOB_DAY);
    }

    public function getDobMonthElement()
    {
        return $this->get(self::FIELD_DOB_MONTH);
    }

    public function getDobYearElement()
    {
        return $this->get(self::FIELD_DOB_YEAR);
    }

    public function getAddressLine1Element()
    {
        return $this->get(AddressLine1Input::FIELD);
    }

    public function getAddressLine2Element()
    {
        return $this->get(AddressLine2Input::FIELD);
    }

    public function getAddressLine3Element()
    {
        return $this->get(AddressLine3Input::FIELD);
    }

    public function getTownElement()
    {
        return $this->get(TownInput::FIELD);
    }

    public function getCountryElement()
    {
        return $this->get(CountryInput::FIELD);
    }

    public function getPostcodeElement()
    {
        return $this->get(PostcodeInput::FIELD);
    }
}
