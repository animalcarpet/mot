<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace DvsaCommon\InputFilter\Account;

use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class SecurityQuestionAnswersInputFilter extends InputFilter
{
    const FIELD_NAME_FIRST_QUESTION_ID = 'first_security_question_id';

    const FIELD_NAME_FIRST_ANSWER = 'first_question';

    const FIELD_NAME_SECOND_QUESTION_ID = 'second_security_question_id';

    const FIELD_NAME_SECOND_ANSWER = 'second_question';

    const FIELD_NAME_NUMBER_OF_ATTEMPTS = 'number_of_attempts';

    const MAX_LENGTH = 70;

    const MSG_TOO_LONG = 'answer must be shorter than %d characters';

    const MSG_IS_EMPTY = 'enter your memorable answer';

    const MSG_INVALID_TYPE = 'Invalid type given';

    const MSG_FAILED_VERIFICATION = 'your answer wasn’t right';

    const MSG_LAST_ATTEMPT_WARNING = 'You have 1 more try';

    const QUESTION_ANSWER_FIELD_PAIR_MAP = [
      self::FIELD_NAME_FIRST_QUESTION_ID => self::FIELD_NAME_FIRST_ANSWER,
      self::FIELD_NAME_SECOND_QUESTION_ID => self::FIELD_NAME_SECOND_ANSWER,
    ];

    /**
     * SecurityQuestionAnswersInputFilter constructor.
     */
    public function __construct()
    {
        foreach ([self::FIELD_NAME_FIRST_ANSWER, self::FIELD_NAME_SECOND_ANSWER] as $name) {
            $this->add($this->createCommonInput($name));
        }
    }

    /**
     * @param string $name
     * @return Input
     */
    private function createCommonInput($name)
    {
        $stringLength = (new StringLength())
            ->setMax(self::MAX_LENGTH)
            ->setMessages([
                StringLength::TOO_LONG => sprintf(self::MSG_TOO_LONG, self::MAX_LENGTH + 1),
                StringLength::INVALID => self::MSG_INVALID_TYPE,
            ]);

        $notEmpty = (new NotEmpty())->setMessages([NotEmpty::IS_EMPTY => self::MSG_IS_EMPTY]);

        $input = new Input($name);
        $input->setRequired(true)
            ->getValidatorChain()
            ->attach($notEmpty, true)
            ->attach($stringLength, true);

        return $input;
    }
}
