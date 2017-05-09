<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Form;

use Dvsa\Mot\Frontend\AuthenticationModule\Validator\PasswordValidator;
use Dvsa\Mot\Frontend\AuthenticationModule\Validator\UsernameValidator;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class LoginForm extends Form
{
    const USERNAME = 'IDToken1';
    const PASSWORD = 'IDToken2';

    const MSG_AUTH_FAIL = 'There was a problem with your User ID or password';

    public function __construct()
    {
        parent::__construct();
        $this->add((new Text())
            ->setName(self::USERNAME)
            ->setLabel('User ID')
            ->setAttribute('id', self::USERNAME)
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('inputModifier', '1-2')
            ->setAttribute('type', 'text')
        );
        $this->add((new Text())
            ->setName(self::PASSWORD)
            ->setLabel('Password')
            ->setAttribute('id', self::PASSWORD)
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('inputModifier', '1-2')
            ->setAttribute('type', 'password')
        );

        $filter = new InputFilter();

        $filter->add([
            'name' => self::USERNAME,
            'required' => true,
            'validators' => [
                [
                    'name' => UsernameValidator::class,
                ],
            ],
            'continue_if_empty' => true,
            'allow_empty' => true,
        ]);

        $filter->add([
            'name' => self::PASSWORD,
            'required' => false,
            'validators' => [
                [
                    'name' => PasswordValidator::class,
                ],
            ],
            'continue_if_empty' => true,
            'allow_empty' => true,
        ]);

        $this->setInputFilter($filter);
    }

    public function getUsernameField()
    {
        return $this->get(self::USERNAME);
    }

    public function getPasswordField()
    {
        return $this->get(self::PASSWORD);
    }

    public function resetPassword()
    {
        $this->getPasswordField()->setValue(null);
    }

    /**
     * @param $field
     * @param $error
     */
    public function setCustomError($field, $error)
    {
        $field->setMessages([$error]);
    }

    public function showLabelOnError($field, $label)
    {
        if (count($this->getMessages($field))) {
            $this->getElements()[$field]->setLabel($label);
        }
    }
}
