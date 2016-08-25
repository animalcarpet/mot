<?php

namespace Dvsa\Mot\Frontend\PersonModule\Form;

use Zend\Form\Element\Csrf;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class QualificationDetailsForm extends Form
{
    const FIELD_CERT_NUMBER = 'cert-number';
    const FIELD_DATE_DAY = 'date-day';
    const FIELD_DATE_MONTH = 'date-month';
    const FIELD_DATE_YEAR = 'date-year';
    const FIELD_VTS_ID = 'vts-id';

    public function __construct(InputFilter $inputFilter)
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
            ->setName(self::FIELD_VTS_ID)
            ->setLabel('VTS ID')
            ->setAttribute('help', 'For example, V12345')
            ->setAttribute('inputModifier', '1-4')
            ->setAttribute('group', true)
            ->setAttribute('id', self::FIELD_VTS_ID)
        );

        $this->setInputFilter($inputFilter);
    }

    //todo UT check if messages are populated if inputfilter has messages
    public function isValid()
    {
        $valid = parent::isValid();

        if(!$valid) {
            $messages = $this->getInputFilter()->getMessages();

            $parsedMessages = [];
            foreach($messages as $m) {
                $parsedMessages[$m['field']] = $m;
            }

            foreach($parsedMessages as $dtoField => $m) {
                $this->get($this->translateDtoFieldToForm($dtoField))->setMessages($m['displayMessage']);
            }
        }

        return $valid;
    }

    protected function translateDtoFieldToForm($key)
    {
        $dtoToForm = [
            'certificateNumber' => QualificationDetailsForm::FIELD_CERT_NUMBER,
            'dateOfQualification' => QualificationDetailsForm::FIELD_DATE_DAY,
            'siteNumber' => QualificationDetailsForm::FIELD_VTS_ID,
        ];

        if(!array_key_exists($key, $dtoToForm)) {
            throw new \Exception("Wrong key: " . $key);
        }

        return $dtoToForm[$key];
    }
}