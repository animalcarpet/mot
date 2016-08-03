<?php

namespace Dvsa\Mot\Frontend\PersonModule\Form;

use DvsaCommon\Validator\DateInPastValidator;
use Zend\Filter\DateTimeFormatter;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Between;
use Zend\Validator\Digits;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\I18n\Validator\IsInt;

class AnnualAssessmentCertificatesForm extends Form
{
    const FIELD_CERT_NUMBER = 'cert-number';
    const FIELD_DATE_AWARDED = 'Date awarded';
    const FIELD_DATE_DAY = 'date-day';
    const FIELD_DATE_MONTH = 'date-month';
    const FIELD_DATE_YEAR = 'date-year';
    const FIELD_SCORE = 'score';
    const MSG_TOO_LONG = 'must be %max% characters or less';
    const MSG_BETWEEN = 'must be between 0 and 100';
    const MSG_DIGITS = 'enter whole numbers only';
    const MSG_CERTIFICATE_EMPTY = 'you must enter a certificate number';
    const MSG_SCORE_EMPTY = "Score must not be empty";
    const MSG_DATE_EMPTY = 'you must enter a date';
    const SCORE_MIN = 0;
    const SCORE_MAX = 100;

    public function __construct()
    {
        parent::__construct();

        $this->add((new Text())
            ->setName(self::FIELD_CERT_NUMBER)
            ->setLabel('Certificate number')
            ->setAttribute('id', self::FIELD_CERT_NUMBER)
            ->setAttribute('help', 'For example, ABC12345')
            ->setAttribute('inputModifier', '3-4')
            ->setAttribute('group', true)
            ->setAttribute('required', true)
        );

        $this->add((new Text())
            ->setName(self::FIELD_DATE_DAY)
            ->setLabel('Day')
            ->setAttribute('id', self::FIELD_DATE_DAY)
            ->setAttribute('required', true)
        );

        $this->add((new Text())
            ->setName(self::FIELD_DATE_MONTH)
            ->setLabel('Month')
            ->setAttribute('id', self::FIELD_DATE_MONTH)
            ->setAttribute('required', true)
        );

        $this->add((new Text())
            ->setName(self::FIELD_DATE_YEAR)
            ->setLabel('Year')
            ->setAttribute('id', self::FIELD_DATE_YEAR)
            ->setAttribute('required', true)
        );

        $this->add((new Text())
            ->setName(self::FIELD_SCORE)
            ->setLabel('Score achieved')
            ->setAttribute('help', 'For example, 75%')
            ->setAttribute('inputModifier', '1-8 a-r')
            ->setAttribute('group', true)
            ->setAttribute('id', self::FIELD_SCORE)
        );

        $emptyCertificateValidator = (new NotEmpty())->setMessage(self::MSG_CERTIFICATE_EMPTY, NotEmpty::IS_EMPTY);
        $certNumberLengthValidator = (new StringLength())->setMax(50)->setMessage(self::MSG_TOO_LONG, StringLength::TOO_LONG);
        $certNumberInput = new Input(self::FIELD_CERT_NUMBER);
        $certNumberInput
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($emptyCertificateValidator)
            ->attach($certNumberLengthValidator);

        $emptyScoreValidator = (new NotEmpty())->setMessage(self::MSG_SCORE_EMPTY, NotEmpty::IS_EMPTY);
        $scoreDigitsValidator = (new Digits())->setMessage(self::MSG_DIGITS, Digits::NOT_DIGITS);
        $scoreLengthValidator = (new Between(['min' => 0, 'max' => 100]))->setInclusive(true)->setMessage(self::MSG_BETWEEN, Between::NOT_BETWEEN);

        $intValidator = (new IsInt())
            ->setMessages([
                IsInt::INVALID => self::MSG_DIGITS,
                IsInt::NOT_INT => self::MSG_DIGITS,
            ])
        ;

        $scoreInput = new Input(self::FIELD_SCORE);
        $scoreInput
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($emptyScoreValidator)
            ->attach($scoreDigitsValidator)
            ->attach($intValidator)
            ->attach($scoreLengthValidator);

        $filter = new InputFilter();
        $filter
            ->add($certNumberInput)
            ->add($scoreInput);

        $this->setInputFilter($filter);
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
            $this->get(self::FIELD_DATE_DAY)->setMessages($dobInput->getMessages());
        }

        return $isValid;
    }

    public function isValid()
    {
        $valid = parent::isValid();

        $isDateValid = $this->isValidDate();
        $valid = $valid && $isDateValid;

        if (!$valid) {
            $messages = $this->getInputFilter()->getMessages();

            $parsedMessages = [];
            foreach ($messages as $m) {
                $parsedMessages[$m['field']] = $m;
            }
        }

        return $valid;
    }

    protected function translateDtoFieldToForm($key)
    {
        $dtoToForm = [
            'certificateNumber' => AnnualAssessmentCertificatesForm::FIELD_CERT_NUMBER,
            'dateOfQualification' => AnnualAssessmentCertificatesForm::FIELD_DATE_DAY,
            'siteNumber' => AnnualAssessmentCertificatesForm::FIELD_SCORE,
        ];

        if (!array_key_exists($key, $dtoToForm)) {
            throw new \Exception("Wrong key: " . $key);
        }

        return $dtoToForm[$key];
    }
}