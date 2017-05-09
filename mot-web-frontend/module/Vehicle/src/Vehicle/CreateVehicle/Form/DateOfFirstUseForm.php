<?php

namespace Vehicle\CreateVehicle\Form;

use DvsaCommon\Date\DateUtils;
use Zend\Form\ElementInterface;
use Zend\Form\Form;
use Zend\Form\Element\Text;
use Zend\Validator\Date;

class DateOfFirstUseForm extends Form
{
    const FIELD_DAY = 'dateDay';
    const FIELD_MONTH = 'dateMonth';
    const FIELD_YEAR = 'dateYear';

    const ERROR_MUST_BE_NUMERIC = 'Can only contain numbers';
    const ERROR_ENTER_VALID_DATE = 'Enter a valid date';
    const ERROR_DATE_IS_IN_FUTURE = 'Enter a date in the past';

    private $errorMessages = [];

    /**
     * @var Text
     */
    private $dayElement;

    /**
     * @var Text
     */
    private $monthElement;

    /**
     * @var Text
     */
    private $yearElement;

    public function __construct($dataToPrePopulate)
    {
        parent::__construct();

        $this->dayElement = $this->createTextElement('Day', self::FIELD_DAY, 2, $dataToPrePopulate[self::FIELD_DAY]);
        $this->monthElement = $this->createTextElement('Month', self::FIELD_MONTH, 2, $dataToPrePopulate[self::FIELD_MONTH]);
        $this->yearElement = $this->createTextElement('Year', self::FIELD_YEAR, 4, $dataToPrePopulate[self::FIELD_YEAR]);

        $this->add($this->dayElement);
        $this->add($this->monthElement);
        $this->add($this->yearElement);
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * @return Text
     */
    public function getDayElement()
    {
        return $this->get(self::FIELD_DAY);
    }

    /**
     * @return Text
     */
    public function getMonthElement()
    {
        return $this->get(self::FIELD_MONTH);
    }

    /**
     * @return Text
     */
    public function getYearElement()
    {
        return $this->get(self::FIELD_YEAR);
    }

    public function isValid()
    {
        $isValid = $this->areAnyElementsEmpty();

        if ($isValid) {
            $isValid = $this->areFieldsNumericAndCorrectLength();
        }

        if ($isValid) {
            $isValid = $this->isDateValid($this->getDate());
        }

        return $isValid;
    }

    private function areFieldsNumericAndCorrectLength()
    {
        $year = $this->get(self::FIELD_YEAR)->getValue();
        $month = $this->get(self::FIELD_MONTH)->getValue();
        $day = $this->get(self::FIELD_DAY)->getValue();

        if (!is_numeric($day) || !is_numeric($month) || !is_numeric($year)) {
            $this->addErrorMessage('Date of first use - can only contain numbers');
            $this->setCustomError($this->getDayElement(), self::ERROR_MUST_BE_NUMERIC);

            return false;
        }

        if (strlen($year) != 4) {
            $this->addErrorMessage('Date of first use - enter a valid date');
            $this->setCustomError($this->getDayElement(), self::ERROR_ENTER_VALID_DATE);

            return false;
        }

        return true;
    }

    private function areAnyElementsEmpty()
    {
        $year = $this->get(self::FIELD_YEAR)->getValue();
        $month = $this->get(self::FIELD_MONTH)->getValue();
        $day = $this->get(self::FIELD_DAY)->getValue();

        if (empty($day) || empty($month) || empty($year)) {
            $this->addErrorMessage('Date of first use - enter a valid date');
            $this->setCustomError($this->getDayElement(), self::ERROR_ENTER_VALID_DATE);

            return false;
        }

        return true;
    }

    public function isDateValid($value)
    {
        try {
            $dateFormatValidator = new Date();
            $dateFormatValidator->setFormat('Y-m-d');
            $isValidFormat = $dateFormatValidator->isValid($value);

            if (!$isValidFormat) {
                $this->addErrorMessage('Date of first use - enter a valid date');
                $this->setCustomError($this->getDayElement(), self::ERROR_ENTER_VALID_DATE);

                return false;
            }

            $convertedDate = DateUtils::toUserTz(new \DateTime($value));
            $today = DateUtils::today();

            if ($convertedDate > $today) {
                $this->addErrorMessage('Date of first use - enter a date in the past');
                $this->setCustomError($this->getDayElement(), self::ERROR_DATE_IS_IN_FUTURE);

                return false;
            }
        } catch (\Exception $e) {
            $this->addErrorMessage('Date of first use - enter a valid date');
            $this->setCustomError($this->getDayElement(), self::ERROR_ENTER_VALID_DATE);

            return false;
        }

        return true;
    }

    private function getDate()
    {
        $year = $this->get(self::FIELD_YEAR)->getValue();
        $month = $this->get(self::FIELD_MONTH)->getValue();
        $day = $this->get(self::FIELD_DAY)->getValue();

        if (empty($year) || empty($month) || empty($day)) {
            return '';
        }

        return implode('-', [$year, $month, $day]);
    }

    private function createTextElement($elementLabel, $elementId, $maxLength, $value)
    {
        $element = new Text();

        return $element
            ->setName($elementId)
            ->setLabel($elementLabel)
            ->setValue($value)
            ->setAttribute('id', $elementId)
            ->setAttribute('class', 'form-control')
            ->setAttribute('required', true)
            ->setAttribute('maxLength', $maxLength);
    }

    private function setCustomError(ElementInterface $field, $error)
    {
        $field->setMessages([$error]);
    }

    private function addErrorMessage($errorMessage)
    {
        array_push($this->errorMessages, $errorMessage);
    }
}
