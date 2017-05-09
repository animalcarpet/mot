<?php

namespace PersonApiTest\Dto;

use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use DvsaEntities\Entity\AuthorisationForTestingMotStatus;
use DvsaEntities\Entity\VehicleClass;
use PersonApi\Dto\MotTestingAuthorisationCollector;

/**
 * Unit tests for MotTestingAuthorisationCollectorTest dto.
 */
class MotTestingAuthorisationCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function test_toArray_emptyAuthorisationList_shouldReturnArrayWithNulls()
    {
        $auth = new MotTestingAuthorisationCollector([]);
        $this->assertEquals(
            [
                'class1' => null,
                'class2' => null,
                'class3' => null,
                'class4' => null,
                'class5' => null,
                'class7' => null,
            ],
            $auth->toArray()
        );
    }

    public function test_toArray_class1and2Qualified_shouldReturnCorrectArray()
    {
        $auth = self::createAuthorisedForVehicleClassesCollector(
            [
                1 => AuthorisationForTestingMotStatusCode::QUALIFIED,
                2 => AuthorisationForTestingMotStatusCode::QUALIFIED,
            ]
        );

        $this->assertEquals(
            [
                'class1' => AuthorisationForTestingMotStatusCode::QUALIFIED,
                'class2' => AuthorisationForTestingMotStatusCode::QUALIFIED,
                'class3' => null,
                'class4' => null,
                'class5' => null,
                'class7' => null,
            ],
            $auth->toArray()
        );
    }

    public function test_toArray_class3to7Qualified_shouldReturnCorrectArray()
    {
        $auth = self::createAuthorisedForVehicleClassesCollector(
            [
                3 => AuthorisationForTestingMotStatusCode::QUALIFIED,
                4 => AuthorisationForTestingMotStatusCode::QUALIFIED,
                5 => AuthorisationForTestingMotStatusCode::QUALIFIED,
                7 => AuthorisationForTestingMotStatusCode::QUALIFIED,
            ]
        );

        $this->assertEquals(
            [
                'class1' => null,
                'class2' => null,
                'class3' => AuthorisationForTestingMotStatusCode::QUALIFIED,
                'class4' => AuthorisationForTestingMotStatusCode::QUALIFIED,
                'class5' => AuthorisationForTestingMotStatusCode::QUALIFIED,
                'class7' => AuthorisationForTestingMotStatusCode::QUALIFIED,
            ],
            $auth->toArray()
        );
    }

    public function test_toArray_everyClassInDifferentStatus_shouldReturnCorrectArray()
    {
        $auth = self::createAuthorisedForVehicleClassesCollector(
            [
                1 => AuthorisationForTestingMotStatusCode::QUALIFIED,
                2 => AuthorisationForTestingMotStatusCode::UNKNOWN,
                3 => AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
                4 => AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED,
                5 => AuthorisationForTestingMotStatusCode::QUALIFIED,
                7 => AuthorisationForTestingMotStatusCode::SUSPENDED,
            ]
        );

        $this->assertEquals(
            [
                'class1' => AuthorisationForTestingMotStatusCode::QUALIFIED,
                'class2' => AuthorisationForTestingMotStatusCode::UNKNOWN,
                'class3' => AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
                'class4' => AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED,
                'class5' => AuthorisationForTestingMotStatusCode::QUALIFIED,
                'class7' => AuthorisationForTestingMotStatusCode::SUSPENDED,
            ],
            $auth->toArray()
        );
    }

    public static function createAuthorisedForVehicleClassesCollector($setup)
    {
        $authorisations = [];

        foreach ($setup as $class => $statusCode) {
            $vehicleClass = new VehicleClass();
            $vehicleClass->setCode($class);
            $status = new AuthorisationForTestingMotStatus();
            $status->setCode($statusCode);
            $authorisation = new AuthorisationForTestingMot();
            $authorisation->setVehicleClass($vehicleClass);
            $authorisation->setStatus($status);

            $authorisations[] = $authorisation;
        }

        return new MotTestingAuthorisationCollector($authorisations);
    }
}
