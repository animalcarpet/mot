<?php

namespace PersonApi\Input\MotTestingCertificate;

use Zend\InputFilter\Input;
use Zend\Validator\Between;
use Zend\Validator\Digits;
use Zend\Validator\NotEmpty;
use Zend\I18n\Validator\IsInt;

class ScoreInput extends Input
{
    const FIELD = 'score';
    const ERROR_IS_EMPTY = 'Score must not be empty';
    const ERROR_NOT_BETWEEN = 'Score must be between 0 and 100';
    const ERROR_NOT_DIGITS = 'Enter whole numbers only';

    public function __construct()
    {
        parent::__construct(self::FIELD);

        $emptyValidator = (new NotEmpty())
            ->setMessage(self::ERROR_IS_EMPTY, NotEmpty::IS_EMPTY);

        $digitsValidator = (new Digits())
            ->setMessages([
                Digits::STRING_EMPTY => self::ERROR_IS_EMPTY,
                Digits::INVALID => self::ERROR_NOT_DIGITS,
                Digits::NOT_DIGITS => self::ERROR_NOT_DIGITS,
            ]);

        $intValidator = (new IsInt())
            ->setMessages([
                IsInt::INVALID => self::ERROR_NOT_DIGITS,
                IsInt::NOT_INT => self::ERROR_NOT_DIGITS,
            ])
        ;

        $rangeValidator = (new Between(['min' => 0, 'max' => 100]))
            ->setMessage(self::ERROR_NOT_BETWEEN, Between::NOT_BETWEEN);

        $this
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($emptyValidator)
            ->attach($digitsValidator)
            ->attach($intValidator)
            ->attach($rangeValidator);
    }
}
