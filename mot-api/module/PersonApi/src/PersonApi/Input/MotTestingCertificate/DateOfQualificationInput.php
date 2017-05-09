<?php

namespace PersonApi\Input\MotTestingCertificate;

use DvsaCommon\Date\DateTimeHolder;
use Zend\InputFilter\Input;
use Zend\Validator\NotEmpty;
use PersonApi\Service\Validator\MotTestingCertificate\DateOfQualificationValidator;

class DateOfQualificationInput extends Input
{
    const FIELD = 'dateOfQualification';

    public function __construct(DateTimeHolder $dateTimeHolder)
    {
        parent::__construct(self::FIELD);

        $emptyValidator = (new NotEmpty())
            ->setMessage(DateOfQualificationValidator::ERROR_IS_EMPTY, NotEmpty::IS_EMPTY);

        $dateValidator = new DateOfQualificationValidator($dateTimeHolder);

        $this
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($emptyValidator)
            ->attach($dateValidator);
    }
}
