<?php

namespace DvsaClientTest\Entity;

use DvsaClient\Entity\Role;

/**
 * Class RoleTest.
 */
class RoleTest extends BaseEntityTestCase
{
    public function testSetProperties()
    {
        $expectedProperties = [
            'name',
            'id',
            'fullName',
            'shortName',
        ];
        $this->checkGettersAndSetters($expectedProperties, new Role());
    }

    public function testGetDisplayValueReturnsRoleInCorrectForm()
    {
        //given
        $role = new Role();
        $role->setFullName('Long Name');
        $role->setShortName('LN');

        //when
        $result = $role->getNameForDisplay();

        //then
        $this->assertEquals('Long Name (LN)', $result);
    }

//    public function testGetRolesAsArrayWithNoRolesReturnsEmptyArray()
//    {
//        //given
//        $roles = [];

//        //when
//        $rolesArray = Role::getRolesAsArray($roles);

//        //then
//        $this->assertEquals([], $rolesArray);
//    }

//    public function testGetRolesAsArrayWithRoleReturnsArrayWithKeyAndDisplayValue()
//    {
//        //given
//        $role = new Role();
//        $role->setName('keyName');
//        $role->setFullName('Full Name');
//        $role->setShortName('AB');

//        //when
//        $rolesArray = Role::getRolesAsArray([$role]);

//        //then
//        $this->assertCount(1, $rolesArray);
//        $this->assertEquals(['keyName' => 'Full Name (AB)'], $rolesArray);
//    }
}
