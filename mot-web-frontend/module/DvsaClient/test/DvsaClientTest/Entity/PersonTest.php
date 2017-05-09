<?php

namespace DvsaClientTest\Entity;

use DvsaClient\Entity\Person;

/**
 * Class PersonTest.
 */
class PersonTest extends BaseEntityTestCase
{
    public function testSetProperties()
    {
        $expectedProperties = [
            'firstName',
            'middleName',
            'familyName',
            'contactDetails',
            'contacts',
            'id',
            'uuid',
        ];
        $this->checkGettersAndSetters($expectedProperties, new Person());
    }

    /**
     * @dataProvider provideNameData
     */
    public function testGetFullNameReturnsFullName($firstName, $middleName, $surname, $concatenatedName)
    {
        $person = new Person();
        $person->setFirstName($firstName);
        $person->setMiddleName($middleName);
        $person->setFamilyName($surname);

        $this->assertEquals($concatenatedName, $person->getFullName(), 'Full name returned is incorrect');
    }

    public function provideNameData()
    {
        return [
            ['John', 'Middle', 'Last', 'John Middle Last'],
            ['John', '', 'Nomiddle', 'John Nomiddle'],
        ];
    }
}
