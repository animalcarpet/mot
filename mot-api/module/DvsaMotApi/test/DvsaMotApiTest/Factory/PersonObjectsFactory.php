<?php

namespace DvsaMotApiTest\Factory;

use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaEntities\Entity\Person;

/**
 * Class PersonObjectsFactory.
 */
class PersonObjectsFactory extends AbstractServiceTestCase
{
    private static $testInstance = null;

    private static function getInstance()
    {
        if (self::$testInstance === null) {
            self::$testInstance = new self();
        }

        return self::$testInstance;
    }

    public static function person($params = [])
    {
        $test = self::getInstance();

        /** @var Person $person */
        $person = $test->getMockWithDisabledConstructor(Person::class);

        foreach ($params as $key => $value) {
            if (method_exists($person, $key)) {
                $test->setupMockForSingleCall($person, $key, true);
            } else {
                $person->{'set'.ucfirst($key)}($value);
            }
        }

        return $person;
    }
}
