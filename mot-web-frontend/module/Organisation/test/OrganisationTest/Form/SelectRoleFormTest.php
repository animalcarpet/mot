<?php

namespace OrganisationTest\Form;

use Organisation\Form\SelectRoleForm;

/**
 * Class SelectRoleFormTest.
 */
class SelectRoleFormTest extends \PHPUnit_Framework_TestCase
{
    public function testSelectRoleTest()
    {
        $form = new SelectRoleForm('name', ['someRole']);

        $this->assertInstanceOf(\Zend\Form\Element\Radio::class, $form->getRoleId());
        $this->assertEquals($this->getInputFilterSpecification(), $form->getInputFilterSpecification());
    }

    private function getInputFilterSpecification()
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
