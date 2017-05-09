<?php

namespace PersonApi\test\PersonApiTest\Service;

use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Service\PersonQualificationStatusService;
use DvsaEntities\Repository\AuthorisationForTestingMotRepository;
use DvsaEntities\Repository\AuthorisationForTestingMotStatusRepository;
use DvsaEntities\Repository\VehicleClassRepository;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Entity\AuthorisationForTestingMotStatus;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;

class PersonQualificationStatusServiceTest extends \PHPUnit_Framework_TestCase
{
    private $authorisationForTestingMotRepository;
    private $authorisationForTestingMotStatusRepository;
    private $vehicleClassRepository;

    public function setUp()
    {
        $this->authorisationForTestingMotRepository = XMock::of(AuthorisationForTestingMotRepository::class);
        $this->authorisationForTestingMotStatusRepository = XMock::of(AuthorisationForTestingMotStatusRepository::class);
        $this->vehicleClassRepository = XMock::of(VehicleClassRepository::class);
    }

    /**
     * @dataProvider getVehicleClassCode
     */
    public function testChangeStatusCreateNewStatusesIfDoNotExist($vehicleClassGroupCode)
    {
        $expects = $this->countClassesForGroup($vehicleClassGroupCode);
        $this->vehicleClassRepository->expects($this->exactly($expects))->method('getByCode');

        $person = new Person();
        $this->createService()->changeStatus($person, $vehicleClassGroupCode, AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED);
    }

    /**
     * @dataProvider getVehicleClassCode
     */
    public function testChangeStatusOverwriteExistingStatus($vehicleClassGroupCode)
    {
        $this->vehicleClassRepository->expects($this->exactly(0))->method('getByCode');

        $person = new Person();
        $person->setAuthorisationsForTestingMot($this->getAuthorisationForTestingMotForGroup($vehicleClassGroupCode));

        $this->createService()->changeStatus($person, $vehicleClassGroupCode, AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED);
    }

    /**
     * @dataProvider getVehicleClassCode
     */
    public function testRemoveStatusDeleteExistingStatus($vehicleClassGroupCode)
    {
        $expects = $this->countClassesForGroup($vehicleClassGroupCode);

        $this->authorisationForTestingMotRepository->expects($this->exactly($expects))->method('remove');
        $this->authorisationForTestingMotRepository->expects($this->once())->method('flush');

        $person = new Person();
        $person->setAuthorisationsForTestingMot($this->getAuthorisationForTestingMotForGroup($vehicleClassGroupCode));

        $this->createService()->removeStatus($person, $vehicleClassGroupCode);
    }

    /**
     * @dataProvider getVehicleClassCode
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveStatusThrowExceptionWhenTryRemoveNotExistingStatus($vehicleClassGroupCode)
    {
        $person = new Person();
        $this->createService()->removeStatus($person, $vehicleClassGroupCode);
    }

    public function getVehicleClassCode()
    {
        return [
            [
                VehicleClassGroupCode::BIKES,
            ],
            [
                VehicleClassGroupCode::CARS_ETC,
            ],
        ];
    }

    private function getAuthorisationForTestingMotForGroup($vehicleClassGroupCode)
    {
        $data = [];
        $classes = VehicleClassGroup::getClassesForGroup($vehicleClassGroupCode);
        foreach ($classes as $class) {
            $authorisationForTestingMot = new AuthorisationForTestingMot();
            $authorisationForTestingMot
                ->setVehicleClass((new VehicleClass())->setCode($class))
                ->setStatus((new AuthorisationForTestingMotStatus()))
                ;
            $data[] = $authorisationForTestingMot;
        }

        return $data;
    }

    private function createService()
    {
        return new PersonQualificationStatusService(
            $this->authorisationForTestingMotRepository,
            $this->authorisationForTestingMotStatusRepository,
            $this->vehicleClassRepository
        );
    }

    private function countClassesForGroup($vehicleClassGroupCode)
    {
        $classes = VehicleClassGroup::getClassesForGroup($vehicleClassGroupCode);

        return count($classes);
    }
}
