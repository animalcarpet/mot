<?php

namespace DvsaCommon\Input\AuthorisedExaminerPrincipal;

use Zend\InputFilter\Input;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class CountryInput extends Input
{
    const FIELD = 'country';
    const MSG_TOO_LONG = "must be %max% characters or less";
    const MAX_LENGTH = 50;

    public function __construct($name = null)
    {
        parent::__construct(self::FIELD);

        $lengthValidator = (new StringLength())
            ->setMax(self::MAX_LENGTH)
            ->setMessage(self::MSG_TOO_LONG, StringLength::TOO_LONG);

        $this
            ->setRequired(false)
            ->setAllowEmpty(true)
            ->getValidatorChain()
            ->attach($lengthValidator);
    }
}