<?php

namespace Dashboard\Form;

use Zend\Form\Form;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use Core\Service\MotFrontendIdentityProviderInterface;

class PasswordForm extends Form
{
    const FIELD_PASSWORD = PasswordInputFilter::FIELD_PASSWORD;
    const FIELD_RETYPE_PASSWORD = PasswordInputFilter::FIELD_PASSWORD_CONFIRM;

    const LABEL_PASSWORD = 'New password';
    const LABEL_RETYPE_PASSWORD = 'Re-type your new password ';

    const PASSWORD_MAX_LENGTH = 32;
    const PASSWORD_MIN_LENGTH = 8;

    private $identityProvider;

    public function __construct(MotFrontendIdentityProviderInterface $identityProvider, $options = [])
    {
        $name = 'passwordForm';
        parent::__construct($name, $options);

        $this->identityProvider = $identityProvider;

        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name' => self::FIELD_PASSWORD,
                'options' => [
                    'label' => self::LABEL_PASSWORD,
                ],
                'type' => 'password',
                'attributes' => [
                    'maxlength' => self::PASSWORD_MAX_LENGTH,
                    'group' => true,
                ],
            ]
        );

        $this->add(
            [
                'name' => self::FIELD_RETYPE_PASSWORD,
                'options' => [
                    'label' => self::LABEL_RETYPE_PASSWORD,
                ],
                'type' => 'password',
                'attributes' => [
                    'maxlength' => self::PASSWORD_MAX_LENGTH,
                    'group' => true,
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
        $inputFilter = new PasswordInputFilter($this->identityProvider);
        $inputFilter->init();

        return $inputFilter;
    }

    public function clearValues()
    {
        foreach ($this->getElements() as $element) {
            $element->setValue(null);
        }
    }
}
