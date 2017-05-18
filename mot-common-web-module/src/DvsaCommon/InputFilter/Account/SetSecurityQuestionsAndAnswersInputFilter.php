<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace DvsaCommon\InputFilter\Account;

use Zend\InputFilter\Input;
use Zend\Validator\NotEmpty;

class SetSecurityQuestionsAndAnswersInputFilter extends SecurityQuestionAnswersInputFilter
{
    const FIELD_NAME_FIRST_QUESTION = 'first_security_question';

    const FIELD_NAME_SECOND_QUESTION = 'second_security_question';

    const FIELD_NAME_FIRST_ANSWER = 'first_memorable_answer';

    const FIELD_NAME_SECOND_ANSWER = 'second_memorable_answer';

    const MSG_IS_EMPTY = 'choose a question from the list';

    const MSG_TOO_LONG = 'must be shorter than %d characters';

    public function __construct()
    {
        parent::__construct();

        foreach ([self::FIELD_NAME_FIRST_QUESTION, self::FIELD_NAME_SECOND_QUESTION] as $name) {
            $this->add($this->createCommonInput($name));
        }

        $this->renameAbstractedInput(parent::FIELD_NAME_FIRST_ANSWER, self::FIELD_NAME_FIRST_ANSWER);

        $this->renameAbstractedInput(parent::FIELD_NAME_SECOND_ANSWER, self::FIELD_NAME_SECOND_ANSWER);
    }

    /**
     * @param string $name
     * @return Input
     */
    private function createCommonInput($name)
    {
        $notEmpty = (new NotEmpty())->setMessages([NotEmpty::IS_EMPTY => self::MSG_IS_EMPTY]);

        $input = new Input($name);
        $input->setRequired(true)
            ->getValidatorChain()
            ->attach($notEmpty, true, 100);

        return $input;
    }
}
