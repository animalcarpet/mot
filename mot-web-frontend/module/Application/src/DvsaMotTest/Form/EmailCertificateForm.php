<?php

namespace DvsaMotTest\Form;

use DvsaCommon\Validator\EmailAddressValidator;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Identical;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Hostname;

class EmailCertificateForm extends Form
{
    const FIELD_FIRST_NAME = 'firstName';
    const FIELD_FAMILY_NAME = 'familyName';
    const FIELD_EMAIL = 'email';
    const FIELD_RETYPE_EMAIL = 'retypeEmail';

    const FIRST_NAME_MAX_LENGTH = 45;
    const FAMILY_NAME_MAX_LENGTH = 45;
    const EMAIL_MAX_LENGTH = 255;

    const MSG_FIRST_NAME_IS_EMPTY = 'You must enter a First Name';
    const MSG_FIRST_NAME_TOO_LONG = 'First Name must be less than %d characters long';
    const MSG_FAMILY_NAME_IS_EMPTY = 'You must enter a Last Name';
    const MSG_FAMILY_NAME_TOO_LONG = 'Last Name must be less than %d characters long';
    const MSG_EMAIL_IS_EMPTY = 'You must enter an Email address';
    const MSG_EMAIL_TOO_LONG = 'Email must be less than %d characters long';
    const MSG_EMAIL_IS_INVALID = 'You must enter a valid Email address';
    const MSG_EMAIL_IS_NOT_IDENTICAL = "The emails you have entered don't match";

    public function __construct($options = [])
    {
        $name = 'emailCertificateForm';
        parent::__construct($name, $options);

        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name' => self::FIELD_FIRST_NAME,
                'attributes' => [
                    'type' => 'text',
                    'maxlength' => self::FIRST_NAME_MAX_LENGTH,
                ],
            ]
        );

        $this->add(
            [
                'name' => self::FIELD_FAMILY_NAME,
                'attributes' => [
                    'type' => 'text',
                    'maxlength' => self::FAMILY_NAME_MAX_LENGTH,
                ],
            ]
        );

        $this->add(
            [
                'name' => self::FIELD_EMAIL,
                'attributes' => [
                    'type' => 'text',
                    'maxlength' => self::EMAIL_MAX_LENGTH,
                ],
            ]
        );

        $this->add(
            [
                'name' => self::FIELD_RETYPE_EMAIL,
                'attributes' => [
                    'type' => 'text',
                    'maxlength' => self::EMAIL_MAX_LENGTH,
                ],
            ]
        );

        foreach ($this->getElements() as $element) {
            if (!$element->hasAttribute('id')) {
                $element->setAttribute('id', $element->getName());
            }
        }

        $this->setInputFilter($this->createInputFilter());
    }

    private function createInputFilter()
    {
        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => self::FIELD_FIRST_NAME,
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => self::MSG_FIRST_NAME_IS_EMPTY,
                        ],
                    ],
                ],
                [
                    'name' => StringLength::class,
                    'options' => [
                        'max' => self::FIRST_NAME_MAX_LENGTH,
                        'messages' => [
                            StringLength::TOO_LONG => sprintf(self::MSG_FIRST_NAME_TOO_LONG, self::FIRST_NAME_MAX_LENGTH),
                        ],
                    ],

                ],
            ],
        ]);

        $inputFilter->add([
            'name' => self::FIELD_FAMILY_NAME,
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => self::MSG_FAMILY_NAME_IS_EMPTY,
                        ],
                    ],
                ],
                [
                    'name' => StringLength::class,
                    'options' => [
                        'max' => self::FAMILY_NAME_MAX_LENGTH,
                        'messages' => [
                            StringLength::TOO_LONG => sprintf(self::MSG_FAMILY_NAME_TOO_LONG, self::FAMILY_NAME_MAX_LENGTH),
                        ],
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => self::FIELD_EMAIL,
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => self::MSG_EMAIL_IS_EMPTY,
                        ],
                    ],
                ],
                [
                    'name' => StringLength::class,
                    'options' => [
                        'max' => self::EMAIL_MAX_LENGTH,
                        'messages' => [
                            StringLength::TOO_LONG => sprintf(self::MSG_EMAIL_TOO_LONG, self::EMAIL_MAX_LENGTH),
                        ],
                    ],
                ],
                [
                    'name' => EmailAddressValidator::class,
                    'options' => [
                        'message' => self::MSG_EMAIL_IS_INVALID,
                        'hostnameValidator' => (new Hostname())->useIdnCheck(false)->setMessage(self::MSG_EMAIL_IS_INVALID),
                    ],
                ],
            ],
        ]);

        return $inputFilter;
    }

    public function isValid()
    {
        $isValid = parent::isValid();

        $retypeEmailField = $this->get(self::FIELD_RETYPE_EMAIL);
        $retypeEmailValue = $retypeEmailField->getValue();
        if ($retypeEmailValue) {
            $validator = new Identical($this->get(self::FIELD_EMAIL)->getValue());

            if (!$validator->isValid($retypeEmailValue)) {
                $retypeEmailField->setMessages([Identical::NOT_SAME => self::MSG_EMAIL_IS_NOT_IDENTICAL]);
                $isValid = false;
            }
        }

        //remove unnecessary error message
        $messages = $this->getMessages();
        if (isset($messages[self::FIELD_EMAIL][NotEmpty::IS_EMPTY])) {
            unset($messages[self::FIELD_EMAIL][NotEmpty::IS_EMPTY]);
            $this->setMessages($messages);
        }

        return $isValid;
    }
}
