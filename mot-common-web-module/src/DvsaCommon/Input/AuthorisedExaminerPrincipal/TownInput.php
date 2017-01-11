<?php

namespace DvsaCommon\Input\AuthorisedExaminerPrincipal;

use Zend\InputFilter\Input;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class TownInput extends Input
{
    const FIELD = 'town';
    const MSG_EMPTY= 'you must enter a town or city';
    const MSG_TOO_LONG = "must be %max% characters or less";
    const MAX_LENGTH = 50;

    public function __construct($name = null)
    {
        parent::__construct(self::FIELD);

        $emptyValidator = (new NotEmpty())
            ->setMessage(self::MSG_EMPTY, NotEmpty::IS_EMPTY);

        $lengthValidator = (new StringLength())
            ->setMax(self::MAX_LENGTH)
            ->setMessage(self::MSG_TOO_LONG, StringLength::TOO_LONG);

        $this
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($emptyValidator)
            ->attach($lengthValidator);
    }
}