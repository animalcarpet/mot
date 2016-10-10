<?php

namespace Vehicle\UpdateVehicleProperty\Form;

use DvsaCommon\Validator\DateInPastValidator;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;

class FirstUsedDateForm extends Form
{
    const FIELD_DATE_DAY = 'date-day';
    const FIELD_DATE_MONTH = 'date-month';
    const FIELD_DATE_YEAR = 'date-year';
    const MSG_DATE_EMPTY = 'you must enter a date';
    const FIELD_DATE_LABEL = 'Date of first use';

    /** Text $dayElement */
    private $dayElement;

    /** Text $monthElement */
    private $monthElement;

    /** Text $yearElement */
    private $yearElement;

    /**
     * FirstUsedDateForm constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->dayElement = $this->setTextElement('Day', self::FIELD_DATE_DAY, 2);
        $this->monthElement = $this->setTextElement('Month', self::FIELD_DATE_MONTH, 2);
        $this->yearElement = $this->setTextElement('Year', self::FIELD_DATE_YEAR, 4);

        $this->add($this->dayElement);
        $this->add($this->monthElement);
        $this->add($this->yearElement);
    }

    public function isValid()
    {
        $isDateValid = $this->isValidDate();

        if (!$isDateValid) {
            $messages = $this->getInputFilter()->getMessages();

            $parsedMessages = [];
            foreach ($messages as $m) {
                $parsedMessages[$m['field']] = $m;
            }
        }

        return $isDateValid;
    }

    /**
     * @return Text
     */
    public function getDayElement()
    {
        return $this->dayElement;
    }

    /**
     * @return Text
     */
    public function getMonthElement()
    {
        return $this->monthElement;
    }

    /**
     * @return Text
     */
    public function getYearElement()
    {
        return $this->yearElement;
    }

    private function getDate()
    {
        $year = $this->get(self::FIELD_DATE_YEAR)->getValue();
        $month = $this->get(self::FIELD_DATE_MONTH)->getValue();
        $day = $this->get(self::FIELD_DATE_DAY)->getValue();

        if (empty($year) || empty($month) || empty($day)) {
            return "";
        }

        return join("-", [$year, $month, $day]);
    }

    /**
     * @param string $elementLabel
     * @param string $elementId
     * @param $maxLength
     * @return Text
     */
    private function setTextElement($elementLabel, $elementId, $maxLength)
    {
        $element = new Text();
        return $element
            ->setName($elementId)
            ->setLabel($elementLabel)
            ->setAttribute('id', $elementId)
            ->setAttribute('required', true)
            ->setAttribute('maxLength', $maxLength);
    }

    private function isValidDate()
    {
        $emptyDateValidator = (new NotEmpty())->setMessage(self::MSG_DATE_EMPTY, NotEmpty::IS_EMPTY);
        $dobValidator = (new DateInPastValidator());

        $dobInput = new Input(self::FIELD_DATE_DAY);
        $dobInput->setValue($this->getDate());
        $dobInput
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($dobValidator)
            ->attach($emptyDateValidator);

        $isValid = $dobInput->isValid();
        if ($isValid === false) {
            $this->get(self::FIELD_DATE_DAY)->setLabel(self::FIELD_DATE_LABEL)->setMessages($dobInput->getMessages());
        }

        return $isValid;
    }
}