<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Service\ReplacementCertificate;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\CountryOfRegistration;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Repository\ColourRepository;
use DvsaEntities\Repository\MakeRepository;
use DvsaEntities\Repository\ModelRepository;
use DvsaMotApi\Service\MotTestSecurityService;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateDraftCreator;
use DvsaMotApiTest\Factory\MotTestObjectsFactory;
use PHPUnit_Framework_TestCase;

/**
 * Class ReplacementCertificateDraftCreatorTest.
 */
class ReplacementCertificateDraftCreatorTest extends PHPUnit_Framework_TestCase
{
    const MAKE_NAME = 'Bat';
    const MAKE_CODE = 'BT';
    const MODEL_NAME = 'Mobil';
    const MODEL_CODE = 'MB';
    const VIN = '1M8GDM9AXKP042788';
    const REGISTRATION = 'FNZ6110';

    private $authService;
    private $motTestSecurityService;
    private $vehicleService;
    private $entityManager;

    public function setUp()
    {
        $this->authService = XMock::of('DvsaAuthorisation\Service\AuthorisationServiceInterface', ['isGranted', 'getUserId']);
        $this->motTestSecurityService = XMock::of(MotTestSecurityService::class);

        $mockVehicle = new DvsaVehicle($this->getVehicleData());

        $this->vehicleService = XMock::of(VehicleService::class);
        $this->vehicleService->expects($this->any())
            ->method('getDvsaVehicleByIdAndVersion')
            ->willReturn($mockVehicle);

        $mockMake = (new Make())->setName(self::MAKE_NAME)->setCode(self::MAKE_CODE);
        $mockModel = (new Model())->setName(self::MODEL_NAME)->setCode(self::MODEL_CODE)->setMake($mockMake);

        $this->entityManager = XMock::of(EntityManager::class);
        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturnCallback(function ($className) use ($mockModel) {
                switch ($className) {
                    case Make::class:
                        $expectedEntity = $mockModel->getMake();
                        $repo = XMock::of(MakeRepository::class);
                        $repo->expects($this->any())->method('get')->willReturn($expectedEntity);

                        return $repo;
                    case Model::class:
                        $expectedEntity = $mockModel;
                        $repo = XMock::of(ModelRepository::class);
                        $repo->expects($this->any())->method('get')->willReturn($expectedEntity);

                        return $repo;
                    case Colour::class:
                        $expectedEntity = new Colour();
                        $repo = XMock::of(ColourRepository::class);
                        $repo->expects($this->any())->method('getByCode')->willReturn($expectedEntity);

                        return $repo;
                    default:
                        throw new \RuntimeException(sprintf('Repository for "%s" was not specified.', $className));
                }
            });
    }

    public function testCreateGivenMotTestNotBeingCertificateShouldThrowForbiddenException()
    {
        $this->setExpectedException(ForbiddenException::class);

        $motTest = MotTestObjectsFactory::motTest()->setStatus($this->createMotTestActiveStatus());
        $this->userAssignedToVts();
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $this->createSut()->create($motTest);
    }

    public function testCreateGivenNotAdminAndTesterNotAllowedToSeeMotTestShouldThrowForbiddenException()
    {
        $this->setExpectedException(ForbiddenException::class);

        $motTest = MotTestObjectsFactory::motTest();
        $this->userAssignedToVts(false);
        $this->permissionsGranted([PermissionInSystem::CERTIFICATE_REPLACEMENT]);

        $this->createSut()->create($motTest);
    }

    public function testCreateGivenMotTestShouldCreateValidDraft()
    {
        $make = (new Make())->setCode(self::MAKE_CODE)->setName(self::MAKE_NAME);
        $model = (new Model())->setCode(self::MODEL_CODE)->setName(self::MODEL_NAME);
        $model->setMake($make);

        $modelDetail = new ModelDetail();
        $modelDetail->setModel($model);

        $vehicle = new Vehicle();
        $vehicle->setModelDetail($modelDetail);
        $vehicle->setCountryOfRegistration(new CountryOfRegistration());

        $motTest = MotTestObjectsFactory::motTest();
        $motTest->setVehicle($vehicle);
        $this->userAssignedToVts();
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $draft = $this->createSut()->create($motTest);

        $this->assertEquals($motTest->getVersion(), $draft->getMotTestVersion());
        $this->assertEquals($motTest->getExpiryDate(), $draft->getExpiryDate());
        $this->assertEquals($motTest->getOdometerValue(), $draft->getOdometerValue());
        $this->assertEquals($motTest->getOdometerUnit(), $draft->getOdometerUnit());
        $this->assertEquals($motTest->getOdometerResultType(), $draft->getOdometerResultType());
        $this->assertEquals($motTest->getMake(), $draft->getMake());
        $this->assertEquals($motTest->getModel(), $draft->getModel());
    }

    public function testCreatedGivenMotTestWithModelOtherAndMakeOtherShouldCreateValidDraft()
    {
        $make = (new Make())->setCode(self::MAKE_CODE)->setName(self::MAKE_NAME);
        $model = (new Model())->setCode(self::MODEL_CODE)->setName(self::MODEL_NAME);
        $model->setMake($make);

        $modelDetail = new ModelDetail();
        $modelDetail->setModel($model);

        $vehicle = new Vehicle();
        $vehicle->setModelDetail($modelDetail);
        $vehicle->setCountryOfRegistration(new CountryOfRegistration());

        $motTest = MotTestObjectsFactory::motTest();
        $motTest->setVehicle($vehicle);
        $this->userAssignedToVts();
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $draft = $this->createSut()->create($motTest);

        $this->assertEquals($motTest->getModel()->getName(), $draft->getModel()->getName());
        $this->assertEquals($motTest->getModelName(), $draft->getModelName());
        $this->assertEquals($motTest->getMake()->getName(), $draft->getMake()->getName());
        $this->assertEquals($motTest->getMakeName(), $draft->getMakeName());
        $this->assertEquals($motTest->getVersion(), $draft->getMotTestVersion());
        $this->assertEquals($motTest->getExpiryDate(), $draft->getExpiryDate());
        $this->assertEquals($motTest->getOdometerValue(), $draft->getOdometerValue());
        $this->assertEquals($motTest->getOdometerUnit(), $draft->getOdometerUnit());
        $this->assertEquals($motTest->getOdometerResultType(), $draft->getOdometerResultType());
    }

    private function createSut()
    {
        return new ReplacementCertificateDraftCreator(
            $this->motTestSecurityService,
            $this->authService,
            $this->vehicleService,
            $this->entityManager
        );
    }

    // TODO: move to a helper
    private function permissionsGranted($permissions)
    {
        $this->authService->expects($this->any())
            ->method('isGranted')
            ->will(
                $this->returnCallback(
                    function ($arg) use (&$permissions) {
                        return in_array($arg, $permissions);
                    }
                )
            );
    }

    private function userAssignedToVts($decision = true)
    {
        $this->motTestSecurityService->expects($this->any())
            ->method('isCurrentTesterAssignedToMotTest')
            ->will($this->returnValue($decision));
    }

    private function createMotTestActiveStatus()
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method('getName')
            ->willReturn(MotTestStatusName::ACTIVE);

        return $status;
    }

    private function getVehicleData()
    {
        return json_decode(json_encode([
            'id' => 1,
            'amendedOn' => '2004-01-11',
            'registration' => self::REGISTRATION,
            'vin' => self::VIN,
            'emptyVrmReason' => null,
            'emptyVinReason' => null,
            'make' => [
                'id' => 5,
                'name' => self::MAKE_NAME,
            ],
            'model' => [
                'id' => 6,
                'name' => self::MODEL_NAME,
            ],
            'colour' => [
                'code' => 'L',
                'name' => 'Grey',
            ],
            'colourSecondary' => [
                'code' => 'P',
                'name' => 'Black',
            ],
            'countryOfRegistrationId' => 1,
            'vehicleClass' => ['code' => VehicleClassCode::CLASS_4, 'name' => '4'],
            'fuelType' => [
                'code' => 'PE',
                'name' => 'Petrol',
            ],
            'bodyType' => '2 Door Saloon',
            'cylinderCapacity' => 1700,
            'transmissionType' => 'Automatic',
            'firstRegistrationDate' => '2004-01-03',
            'firstUsedDate' => '2004-01-04',
            'manufactureDate' => '2004-01-02',
            'isNewAtFirstReg' => true,
            'weight' => 12467,
            'weightSource' => [
                'code' => 'U',
                'name' => 'unladen',
            ],
            'version' => 2,
        ]));
    }
}
