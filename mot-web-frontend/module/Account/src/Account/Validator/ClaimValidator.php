<?php

namespace Account\Validator;

use Account\Controller\ClaimController;
use DvsaCommon\InputFilter\Account\SetSecurityQuestionsAndAnswersInputFilter;
use DvsaCommon\Validator\PasswordValidator;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Identical;

class ClaimValidator
{
    // Step 1 (Confirm password) validation messages for Reset Account Security process
    const ERR_MSG_PASSWORD_EMPTY = 'enter a password';
    const ERR_MSG_PASSWORD_SAME_AS_USERNAME = 'password must not match your username';
    const ERR_MSG_PASSWORD_CONFIRM_EMPTY = 're-type your password';
    const ERR_MSG_PASSWORD_CONFIRM_MATCH = 'the passwords you have entered don\'t match';
    const MIN_PASSWORD_LENGTH = 8;
    const MAX_PASSWORD_LENGTH = 32;

    // Step 2 (Set security questions) validation messages for Reset Account Security process
    const ERR_MSG_ANSWER_MAX = 'must be less than %s characters long';
    const MAX_ANSWER = 70;

    private $isValid;

    private $messages = [];
    private $failedFields = [];
    private $fieldMessages = [];

    /**
     * @param array $data
     * @param bool $forceToResetFlag
     * @return bool
     */
    public function validatePassword($data, $forceToResetFlag = false)
    {
        $this->setIsValid($forceToResetFlag);

        $firstStepFilter = $this->getFirstStepInputFilter($data);

        if (!$firstStepFilter->isValid()) {
            $this->addMessages($firstStepFilter->getMessages());
            $this->setIsInvalid();
        }

        return $this->isValid();
    }

    /**
     * @param array $data
     * @param bool $forceToResetFlag
     * @return bool
     */
    public function validateSetSecurityQuestion($data, $forceToResetFlag = false)
    {
        $this->setIsValid($forceToResetFlag);

        $filterInput = new SetSecurityQuestionsAndAnswersInputFilter();
        $filterInput->setData($data);

        if (!$filterInput->isValid()) {
            $this->addMessages($filterInput->getMessages());
            $this->setIsInvalid();
        }

        return $this->isValid();
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return (bool) $this->isValid;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getFailedFields()
    {
        return $this->failedFields;
    }

    public function getFieldMessages()
    {
        return $this->fieldMessages;
    }

    public function validateGeneratePin($data)
    {
    }

    private function getFirstStepInputFilter($data)
    {
        $filter = new InputFilter();

        $filter->add(
            [
                'name' => 'password',
                'required' => true,
                'validators' => [
                    [
                        'name' => 'not_empty',
                        'options' => [
                            'message' => self::ERR_MSG_PASSWORD_EMPTY,
                        ],
                    ],
                    [
                        'name' => PasswordValidator::class,
                    ],
                    [
                        'name' => 'callback',
                        'options' => [
                            'callback' => function ($value) use ($data) {
                                return $data['username'] !== $value;
                            },
                            'message' => self::ERR_MSG_PASSWORD_SAME_AS_USERNAME,
                        ],
                    ],
                ],
            ]
        );

        if (!empty($data['password'])) {
            $filter->add(
                [
                    'name' => 'confirm_password',
                    'required' => true,
                    'validators' => [
                        [
                            'name' => 'not_empty',
                            'options' => [
                                'message' => self::ERR_MSG_PASSWORD_CONFIRM_EMPTY,
                            ],
                        ],
                        [
                            'name' => 'identical',
                            'options' => [
                                'token' => 'password',
                                'message' => self::ERR_MSG_PASSWORD_CONFIRM_MATCH,
                            ],
                        ],
                    ],
                ]
            );
        }

        $filter->setData($data);

        return $filter;
    }

    private function addMessages($messages)
    {
        $this->setIsInvalid();
        $this->messages += $messages;
    }

    private function setIsValid($force = false)
    {
        if ($force || is_null($this->isValid)) {
            $this->isValid = true;
        }
    }

    private function setIsInvalid()
    {
        $this->isValid = false;
    }
}
