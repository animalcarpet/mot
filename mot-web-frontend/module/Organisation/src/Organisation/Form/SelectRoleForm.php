<?php

namespace Organisation\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterAwareInterface;

/**
 * Generates radio buttons and submit button for roles.
 */
class SelectRoleForm extends Form implements InputFilterAwareInterface
{
    public function __construct($name, $roles)
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name' => 'roleId',
                'type' => 'radio',
                'options' => [
                    'value_options' => $roles,
                    'label_attributes' => [
                        'class' => 'block-label label-clear',
                    ],
                ],
            ]
        );

        $this->add(
            [
                'type' => 'Zend\Form\Element\Csrf',
                'name' => 'csrf',
            ]
        );

        $this->add(
            [
                'name' => 'submit',
                'options' => [

                ],
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Choose role',
                    'class' => 'btn btn-primary button',
                    'id' => 'assign-role-button',
                ],
            ]
        );
    }

    /**
     * @return Element\Radio
     */
    public function getRoleId()
    {
        return $this->get('roleId');
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'roleId',
                'options' => [
                    'messages' => [
                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please choose a role',
                    ],
                ],
            ],
        ];
    }
}
