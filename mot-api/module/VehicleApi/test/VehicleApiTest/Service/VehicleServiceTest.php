<?php

namespace VehicleApiTest\Service;

use DataCatalogApi\Service\VehicleCatalogService;
use Doctrine\ORM\EntityManager;
use Dvsa\Mot\ApiClient\Request\CreateDvlaVehicleRequest;
use Dvsa\Mot\ApiClient\Request\CreateDvsaVehicleRequest;
use \Dvsa\Mot\ApiClient\Resource\Item\DvlaVehicle as NewDvlaVehicle;
use \Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle as NewDvsaVehicle;
use \Dvsa\Mot\ApiClient\Service\VehicleService as NewVehicleService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Database\Transaction;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Vehicle\AbstractVehicleDto;
use DvsaCommon\Dto\Vehicle\DvlaVehicleDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\VehicleClassId;
use DvsaCommon\Enum\FuelTypeId;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaAuthentication\Service\Exception\OtpException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\ArgCapture;
use DvsaCommonTest\TestUtils\MultiCallStubBuilder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\DvlaVehicle;
use DvsaEntities\Entity\DvlaMakeModelMap;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleV5C;
use DvsaEntities\Repository\DvlaVehicleImportChangesRepository;
use DvsaEntities\Repository\DvlaVehicleRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\VehicleRepository;
use DvsaEntities\Repository\VehicleV5CRepository;
use Doctrine\ORM\EntityRepository;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\MotTestServiceProvider;
use DvsaAuthentication\Service\OtpService;
use DvsaMotApi\Service\Validator\VehicleValidator;
use DvsaMotApiTest\Factory\VehicleObjectsFactory as VOF;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use VehicleApi\Service\VehicleService;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaAuthentication\Identity;
/**
 * it test functionality of class VehicleService
 *
 * @package VehicleApiTest\Service
 */
class VehicleServiceTest extends AbstractServiceTestCase
{
    const OTP_VALID = '123456';
    const OTP_INVALID = '000000';

    const VEHICLE_ID = 9999;
    const VEHICLE_ID_ENC = 'jq33IixSpBsx4rglOvxByg';

    /** @var MotAuthorisationServiceInterface|MockObj */
    private $mockAuthService;

    /** @var VehicleRepository|MockObj */
    private $mockVehicleRepository;

    /** @var VehicleV5CRepository|MockObj */
    private $mockVehicleV5CRepository;

    /** @var DvlaVehicleRepository|MockObj */
    private $mockDvlaVehicleRepository;

    /** @var DvlaVehicleImportChangesRepository|MockObj */
    private $mockDvlaVehicleImportChangesRepository;
    
    /** @var EntityRepository */
    private $mockDvlaMakeModelMapRepository;

    /** @var VehicleCatalogService|MockObj */
    private $mockVehicleCatalog;

    /** @var VehicleValidator|MockObj */
    private $mockValidator;

    /** @var  OtpService|MockObj */
    private $mockOtpService;

    /** @var ParamObfuscator */
    private $paramObfuscator;

    /** @var MotTestServiceProvider */
    private $motTestServiceProvider;

    /** @var PersonRepository */
    private $personRepository;

    /** @var MotTestService|MockObj */
    private $mockMotTestService;

    /** @var MotIdentityProviderInterface */
    private $motIdentityProviderInterface;

    /** @var Transaction */
    private $transaction;

    private $mockNewVehicleService;


    public function setUp()
    {
        $this->mockAuthService = XMock::of(AuthorisationServiceInterface::class, ['isGranted', 'assertGranted']);
        $this->mockVehicleRepository = XMock::of(VehicleRepository::class);
        $this->mockVehicleV5CRepository = XMock::of(VehicleV5CRepository::class);
        $this->mockDvlaVehicleRepository = XMock::of(DvlaVehicleRepository::class);
        $this->mockDvlaVehicleImportChangesRepository = XMock::of(DvlaVehicleImportChangesRepository::class);
        $this->mockVehicleCatalog = XMock::of(VehicleCatalogService::class);
        $this->mockDvlaMakeModelMapRepository = XMock::of(EntityRepository::class);
        $this->mockMotTestService = XMock::of(MotTestService::class);
        $this->motIdentityProviderInterface = XMock::of(MotIdentityProviderInterface::class);
        $this->motIdentityProviderInterface->expects($this->any())->method('getIdentity')
                                                          ->willReturn(XMock::of(Identity::class));
        $this->mockNewVehicleService = XMock::of(NewVehicleService::class);
        $this->mockNewVehicleService->expects($this->any())
            ->method('createDvlaVehicle')
            ->willReturn($this->getNewDvlaVehicleData());
        $this->mockNewVehicleService->expects($this->any())
            ->method('createDvsaVehicle')
            ->willReturn($this->getNewDvsaVehicleData());

        $this->personRepository = XMock::of(PersonRepository::class);
        $this->personRepository->expects($this->any())->method('get')->willReturn(new Person());
        $this->transaction = new Transaction(XMock::of(EntityManager::class));

        $this->paramObfuscator = XMock::of(ParamObfuscator::class);
        $this->mockValidator = new VehicleValidator();
        $this->mockOtpService = XMock::of(OtpService::class);
        $this->mockOtpService->expects($this->any())->method('authenticate')->will(
            MultiCallStubBuilder::of()
                ->add(self::OTP_INVALID, $this->throwException(new OtpException(0, 5)))
                ->add(null, $this->throwException(new OtpException(5, 5)))
                ->add(self::OTP_VALID, true)
                ->build()
        );
        $this->motTestServiceProvider = XMock::of(MotTestServiceProvider::class);
        $this->motTestServiceProvider->expects($this->any())->method('getService')->willReturn($this->mockMotTestService);
    }

    public function testGetVehicleById()
    {
        $id = 2;
        $entity = 'VEHICLE_ENTITY';

        $this->mockVehicleRepository->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($entity));

        $service = $this->createService();

        $this->assertEquals($entity, $service->getVehicle($id));
    }

    public function testGetVehicleByIdShouldReturnVehicleEntity()
    {
        $vehicleId = 1;
        $vehicle = VOF::vehicle($vehicleId);
        $this->returningOn($this->mockVehicleRepository, $vehicle);

        $vehicleEntity = $this->createService()->getVehicle($vehicleId);

        $this->assertSame($vehicle, $vehicleEntity);
    }

    public function testGetVehicleDtoByIdShouldReturnVehicleDto()
    {
        $vehicle = VOF::vehicle(self::VEHICLE_ID);
        $this->returningOn($this->mockVehicleRepository, $vehicle);

        $vehicleDto = $this->createService()->getVehicleDto(self::VEHICLE_ID_ENC);

        $this->assertInstanceOf(VehicleDto::class, $vehicleDto);
        $this->assertVehicleEntityEqualsDto($vehicle, $vehicleDto);

        $this->assertEquals($vehicleDto->getId(), self::VEHICLE_ID_ENC);
        $this->assertNotEquals($vehicleDto->getId(), $vehicle->getId());
    }

    public function testGetDvlaVehicleDtoByIdShouldReturnDvlaVehicleDto()
    {
        $vehicle = VOF::dvlaVehicle(self::VEHICLE_ID);
        $this->returningOn($this->mockDvlaVehicleRepository, $vehicle);

        $colourCode = 'R';
        $secondaryColourCode = 'G';
        $this->returningOn(
            $this->mockVehicleCatalog,
            MultiCallStubBuilder::of()
                ->add([$colourCode, $this->anything()], VOF::colour(1, $colourCode))
                ->add([$secondaryColourCode, $this->anything()], VOF::colour(2, $secondaryColourCode))
                ->build(),
            'findColourByCode'
        );

        $this->mockMethod($this->mockVehicleCatalog, 'findBodyTypeByCode', $this->once(), VOF::bodyType());

        $vehicleDto = $this->createService()->getDvlaVehicleData(self::VEHICLE_ID_ENC);

        $this->assertInstanceOf(DvlaVehicleDto::class, $vehicleDto);
        $this->assertDvlaVehicleEntityEqualsDto($vehicle, $vehicleDto);

        $this->assertEquals($vehicleDto->getId(), self::VEHICLE_ID_ENC);
        $this->assertNotEquals($vehicleDto->getId(), $vehicle->getId());
    }

    public function testCreateVtrAndV5CfromDvlaVehicleGivenDvlaVehicleShouldCreateVtrAndV5C()
    {
        $dvlaVehicle = VOF::dvlaVehicle();
        $dvlaVehicle->setMakeCode("BB");
        $dvlaVehicle->setModelCode("COOPER");
        $vehicleClassCode = VehicleClassCode::CLASS_4;
//        $dvlaVehicle->setMassInServiceWeight(1000);

        $vtrCapture = ArgCapture::create();

        $this->returningOn($this->mockVehicleCatalog, VOF::countryOfRegistration(3), 'getCountryOfRegistrationByCode');
        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::vehicleClass(VehicleClassId::CLASS_4, VehicleClassCode::CLASS_4),
            'getVehicleClassByCode'
        );
        $this->returningOn($this->mockDvlaVehicleRepository, $dvlaVehicle);
        $this->returningOn($this->mockVehicleCatalog, VOF::bodyType(), "findBodyTypeByCode");

        $colourCode = 'R';
        $secondaryColourCode = 'G';
        $this->returningOn(
            $this->mockVehicleCatalog,
            MultiCallStubBuilder::of()
                ->add([$colourCode, $this->anything()], VOF::colour(1, $colourCode))
                ->add([$secondaryColourCode, $this->anything()], VOF::colour(2, $secondaryColourCode))
                ->build(),
            'getColourByCode'
        );
        $this->returningOn($this->mockVehicleCatalog, VOF::fuelType(), 'findFuelTypeByPropulsionCode');
        $this->returningOn($this->mockVehicleCatalog, VOF::model(), 'getModelByCode');
        $this->returningOn($this->mockVehicleCatalog, VOF::make(), 'findMakeByCode');

        $this->returningOn(
            $this->mockVehicleCatalog, VOF::weightSource(WeightSourceCode::MISW), 'getWeightSourceByCode'
        );

        $this->mockNewVehicleService
            ->expects($this->any())
            ->method('createDvlaVehicle')
            ->with($vtrCapture());

        $this->mockVehicleV5CRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(VehicleV5C::class));

        $vehicleId = $this->paramObfuscator->obfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $dvlaVehicle->getId());

        $this->createService()->createVtrAndV5CFromDvlaVehicle($vehicleId, $vehicleClassCode);

        /** @var CreateDvlaVehicleRequest $v */
        $createDvlaVehicleRequest = $vtrCapture->get();
        $vehicleData = $createDvlaVehicleRequest->getVehicleData();

        $this->assertEquals($dvlaVehicle->getVin(), $vehicleData->vin);
        $this->assertEquals($dvlaVehicle->getRegistration(), $vehicleData->registration);
        $this->assertEquals($dvlaVehicle->getManufactureDate(), new \DateTime($vehicleData->dateOfManufacture));
        $this->assertEquals($dvlaVehicle->getFirstRegistrationDate(), new \DateTime($vehicleData->firstUsedDate));
        $this->assertEquals($dvlaVehicle->getCylinderCapacity(), $vehicleData->cylinderCapacity);
        $this->assertEquals($dvlaVehicle->getDvlaVehicleId(), $vehicleData->dvlaVehicleId);
    }

    /**
     * @dataProvider getVehicleWeight
     * @param array $data
     * @param array $expectedData
     */
    public function testDependsOnVehicleClassShouldImportDifferentWeight(array $data, array $expectedData)
    {
        $dvlaVehicle = VOF::dvlaVehicle();
        $vehicleClassCode = $data["vehicleClassCode"];
        $vehicleClassId = $data["vehicleClassId"];
        $weightSourceCode = $data["weightSourceCode"];
        $dvlaVehicle->setMassInServiceWeight($data["massInServiceWeight"]);
        $dvlaVehicle->setUnladenWeight($data["unladenWeight"]);
        $dvlaVehicle->setDesignedGrossWeight($data["designedGrossWeight"]);

        $vtrCapture = ArgCapture::create();

        $this->returningOn($this->mockVehicleCatalog, VOF::countryOfRegistration(3), 'getCountryOfRegistrationByCode');
        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::vehicleClass($vehicleClassId, $vehicleClassCode),
            'getVehicleClassByCode'
        );
        $this->returningOn($this->mockDvlaVehicleRepository, $dvlaVehicle);
        $this->returningOn($this->mockVehicleCatalog, VOF::bodyType(), "findBodyTypeByCode");

        if ($weightSourceCode !== VehicleClassCode::CLASS_1 && $weightSourceCode !== VehicleClassCode::CLASS_2) {
            $this->returningOn(
                $this->mockVehicleCatalog, VOF::weightSource($weightSourceCode), 'getWeightSourceByCode'
            );
        }

        $colourCode = 'R';
        $secondaryColourCode = 'G';
        $this->returningOn(
            $this->mockVehicleCatalog,
            MultiCallStubBuilder::of()
                ->add([$colourCode, $this->anything()], VOF::colour(1, $colourCode))
                ->add([$secondaryColourCode, $this->anything()], VOF::colour(2, $secondaryColourCode))
                ->build(),
            'getColourByCode'
        );
        $this->returningOn($this->mockVehicleCatalog, VOF::model(), 'getModelByCode');

        $this->mockVehicleRepository
            ->expects($this->any())
            ->method('save');

        $this->mockVehicleV5CRepository
            ->expects($this->once())
            ->method('save');

        $this->markTestSkipped('BL-1164 is parked to investigate lifting vehicle\'s entity relationship. Talk to Ali');
        $v = $this->createService()->createVtrAndV5CFromDvlaVehicle(self::VEHICLE_ID_ENC, $vehicleClassCode);

        $wsc = null;
        if ($v->getWeightSource()) {
            $wsc = $v->getWeightSource()->getCode();
        }

        $this->assertEquals($expectedData["weight"], $v->getWeight());
        $this->assertEquals($expectedData["weightSourceCode"], $wsc);
    }

    public function getVehicleWeight()
    {
        return [
            [
                [
                    "vehicleClassCode" => VehicleClassCode::CLASS_1,
                    "vehicleClassId" => VehicleClassId::CLASS_1,
                    "weightSourceCode" => null,
                    "massInServiceWeight" => 2000,
                    "unladenWeight" => 2500,
                    "designedGrossWeight" => 3000
                ],
                [
                    "weight" => 0,
                    "weightSourceCode" => null
                ]
            ],

            [
                [
                    "vehicleClassCode" => VehicleClassCode::CLASS_2,
                    "vehicleClassId" => VehicleClassId::CLASS_2,
                    "weightSourceCode" => WeightSourceCode::DGW,
                    "massInServiceWeight" => 2000,
                    "unladenWeight" => 2500,
                    "designedGrossWeight" => 3000
                ],
                [
                    "weight" => 0,
                    "weightSourceCode" => null
                ]
            ],

            [
                [
                    "vehicleClassCode" => VehicleClassCode::CLASS_3,
                    "vehicleClassId" => VehicleClassId::CLASS_3,
                    "weightSourceCode" => WeightSourceCode::MISW,
                    "massInServiceWeight" => 2000,
                    "unladenWeight" => 2500,
                    "designedGrossWeight" => 3000
                ],
                [
                    "weight" => 2000,
                    "weightSourceCode" => WeightSourceCode::MISW
                ]
            ],

            [
                [
                    "vehicleClassCode" => VehicleClassCode::CLASS_4,
                    "vehicleClassId" => VehicleClassId::CLASS_4,
                    "weightSourceCode" => WeightSourceCode::DGW,
                    "massInServiceWeight" => 0,
                    "unladenWeight" => 2500,
                    "designedGrossWeight" => 3000
                ],
                [
                    "weight" => 0,
                    "weightSourceCode" => null
                ]
            ],

            [
                [
                    "vehicleClassCode" => VehicleClassCode::CLASS_5,
                    "vehicleClassId" => VehicleClassId::CLASS_5,
                    "weightSourceCode" => WeightSourceCode::DGW,
                    "massInServiceWeight" => 2000,
                    "unladenWeight" => 2500,
                    "designedGrossWeight" => 3000
                ],
                [
                    "weight" => 3000,
                    "weightSourceCode" => WeightSourceCode::DGW
                ]
            ],

            [
                [
                    "vehicleClassCode" => VehicleClassCode::CLASS_7,
                    "vehicleClassId" => VehicleClassId::CLASS_7,
                    "weightSourceCode" => WeightSourceCode::DGW,
                    "massInServiceWeight" => 2000,
                    "unladenWeight" => 2500,
                    "designedGrossWeight" => 3000
                ],
                [
                    "weight" => 3000,
                    "weightSourceCode" => WeightSourceCode::DGW
                ]
            ],

            [
                [
                    "vehicleClassCode" => VehicleClassCode::CLASS_7,
                    "vehicleClassId" => VehicleClassId::CLASS_7,
                    "weightSourceCode" => WeightSourceCode::DGW,
                    "massInServiceWeight" => 2000,
                    "unladenWeight" => 2500,
                    "designedGrossWeight" => 0
                ],
                [
                    "weight" => 0,
                    "weightSourceCode" => null
                ]
            ],
        ];
    }

    public function testCreateVtrAndV5CfromDvlaVehicleGivenDvlaVehicleShouldCountWeightBasedOnGrossWeight()
    {
        $this->markTestSkipped('BL-1164 is parked to investigate lifting vehicle\'s entity relationship. Talk to Ali');
        $grossWeight = 3200;

        $dvlaVehicle = VOF::dvlaVehicle();
        $dvlaVehicle->setDesignedGrossWeight($grossWeight);
        $dvlaVehicle->setUnladenWeight(1000);

        $vehicleClassCode = VehicleClassCode::CLASS_5;
        $vtrCapture = ArgCapture::create();

        $this->returningOn($this->mockVehicleCatalog, VOF::countryOfRegistration(3), 'getCountryOfRegistrationByCode');
        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::vehicleClass(VehicleClassId::CLASS_5, VehicleClassCode::CLASS_5),
            'getVehicleClassByCode'
        );
        $this->returningOn($this->mockDvlaVehicleRepository, $dvlaVehicle);
        $this->returningOn($this->mockVehicleCatalog, VOF::bodyType(), "findBodyTypeByCode");
        $this->returningOn(
            $this->mockVehicleCatalog, VOF::weightSource(WeightSourceCode::DGW), 'getWeightSourceByCode'
        );

        $colourCode = 'R';
        $secondaryColourCode = 'G';
        $this->returningOn(
            $this->mockVehicleCatalog,
            MultiCallStubBuilder::of()
                ->add([$colourCode, $this->anything()], VOF::colour(1, $colourCode))
                ->add([$secondaryColourCode, $this->anything()], VOF::colour(2, $secondaryColourCode))
                ->build(),
            'getColourByCode'
        );
        $this->returningOn($this->mockVehicleCatalog, VOF::model(), 'getModelByCode');

        $this->mockVehicleRepository
            ->expects($this->any())
            ->method('save')
            ->with($vtrCapture());

        $this->mockVehicleV5CRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(VehicleV5C::class));

        $this->createService()->createVtrAndV5CFromDvlaVehicle(self::VEHICLE_ID_ENC, $vehicleClassCode);

        /** @var Vehicle $v */
        $v = $vtrCapture->get();

        $this->assertEquals($grossWeight, $v->getWeight());
        $this->assertEquals(WeightSourceCode::DGW, $v->getWeightSource()->getCode());
    }

    public function testCreateVtrAndV5CfromDvlaVehicleGivenDvlaVehicleShouldCreateLinkBetweenDvlaAndVtr()
    {
        $this->markTestSkipped('BL-1164 is parked to investigate lifting vehicle\'s entity relationship. Talk to Ali');

        $dvlaVehicle = VOF::dvlaVehicle();
        $vehicleClassCode = VehicleClassCode::CLASS_4;
        $dvlaVehicle->setMassInServiceWeight(1000);

        $dvlaCapture = ArgCapture::create();

        $this->returningOn($this->mockVehicleCatalog, VOF::countryOfRegistration(3), 'getCountryOfRegistrationByCode');
        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::vehicleClass(VehicleClassId::CLASS_4, VehicleClassCode::CLASS_4),
            'getVehicleClassByCode'
        );
        $this->returningOn($this->mockDvlaVehicleRepository, $dvlaVehicle);
        $this->returningOn($this->mockVehicleCatalog, VOF::bodyType(), "findBodyTypeByCode");

        $colourCode = 'R';
        $secondaryColourCode = 'G';
        $this->returningOn(
            $this->mockVehicleCatalog,
            MultiCallStubBuilder::of()
                ->add([$colourCode, $this->anything()], VOF::colour(1, $colourCode))
                ->add([$secondaryColourCode, $this->anything()], VOF::colour(2, $secondaryColourCode))
                ->build(),
            'getColourByCode'
        );
        $this->returningOn($this->mockVehicleCatalog, VOF::model(), 'getModelByCode');

        $this->mockDvlaVehicleRepository
            ->expects($this->any())
            ->method('save')
            ->with($dvlaCapture());

        $this->mockVehicleV5CRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(VehicleV5C::class));

        $this->createService()->createVtrAndV5CFromDvlaVehicle(self::VEHICLE_ID_ENC, $vehicleClassCode);

        /** @var DvlaVehicle $savedDvla */
        $savedDvla = $dvlaCapture->get();

        $this->assertNotNull($savedDvla->getVehicleId());
    }

    /**
     * If the dvla_vehicle.recent_v5_document_number is NULL a new VehicleV5C entity should *not* be created and
     * persisted.
     */
    public function testCreateVtrAndV5CfromDvlaVehicleGivenDvlaVehicleShouldNotCreateV5CIfDvlaFieldIsMissing()
    {
        $dvlaVehicle = VOF::dvlaVehicle();
        $dvlaVehicle->setV5DocumentNumber(null);
        $vehicleClassCode = VehicleClassCode::CLASS_4;
        $dvlaVehicle->setMassInServiceWeight(1000);
        $dvlaVehicle->setMakeCode("BB");
        $dvlaVehicle->setModelCode("COOPER");

        $vtrCapture = ArgCapture::create();

        $this->returningOn($this->mockVehicleCatalog, VOF::countryOfRegistration(3), 'getCountryOfRegistrationByCode');
        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::vehicleClass(VehicleClassId::CLASS_4, VehicleClassCode::CLASS_4),
            'getVehicleClassByCode'
        );
        $this->returningOn($this->mockDvlaVehicleRepository, $dvlaVehicle);
        $this->returningOn($this->mockVehicleCatalog, VOF::bodyType(), "findBodyTypeByCode");

        $colourCode = 'R';
        $secondaryColourCode = 'G';
        $this->returningOn(
            $this->mockVehicleCatalog,
            MultiCallStubBuilder::of()
                ->add([$colourCode, $this->anything()], VOF::colour(1, $colourCode))
                ->add([$secondaryColourCode, $this->anything()], VOF::colour(2, $secondaryColourCode))
                ->build(),
            'getColourByCode'
        );
        $this->returningOn($this->mockVehicleCatalog, VOF::fuelType(), 'findFuelTypeByPropulsionCode');
        $this->returningOn($this->mockVehicleCatalog, VOF::model(), 'getModelByCode');
        $this->returningOn($this->mockVehicleCatalog, VOF::make(), 'findMakeByCode');

        $this->returningOn(
            $this->mockVehicleCatalog, VOF::weightSource(WeightSourceCode::MISW), 'getWeightSourceByCode'
        );

        $this->mockVehicleRepository
            ->expects($this->any())
            ->method('save')
            ->with($vtrCapture());

        $this->mockVehicleV5CRepository
            ->expects($this->never())
            ->method('save');

        $vehicleId = $this->paramObfuscator->obfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $dvlaVehicle->getId());

        $this->markTestSkipped('BL-1164 is parked to investigate lifting vehicle\'s entity relationship. Talk to Ali');
        $this->createService()->createVtrAndV5CFromDvlaVehicle($vehicleId, $vehicleClassCode);

        /** @var Vehicle $v */
        $v = $vtrCapture->get();

        $this->assertEquals($dvlaVehicle->getVin(), $v->getVin());
        $this->assertEquals($dvlaVehicle->getRegistration(), $v->getRegistration());
        $this->assertEquals($dvlaVehicle->getManufactureDate(), $v->getManufactureDate());
        $this->assertEquals($dvlaVehicle->getFirstRegistrationDate(), $v->getFirstUsedDate());
        $this->assertEquals($dvlaVehicle->getPrimaryColour(), $v->getColour()->getCode());
        $this->assertEquals($dvlaVehicle->getSecondaryColour(), $v->getSecondaryColour()->getCode());
        $this->assertEquals($dvlaVehicle->getMakeName(), $v->getMakeName());
        $this->assertEquals($dvlaVehicle->getModelName(), $v->getModelName());
        $this->assertEquals($dvlaVehicle->getCylinderCapacity(), $v->getCylinderCapacity());
        $this->assertEquals($dvlaVehicle->getBodyType(), $v->getBodyType()->getCode());
        $this->assertEquals($dvlaVehicle->getFuelType(), $v->getFuelType()->getCode());
        $this->assertEquals($dvlaVehicle->getMassInServiceWeight(), $v->getWeight());
        $this->assertEquals($dvlaVehicle->getDvlaVehicleId(), $v->getDvlaVehicleId());
        $this->assertEquals(WeightSourceCode::MISW, $v->getWeightSource()->getCode());
    }

    /**
     * @dataProvider invalidDvlaBodyTypeCodeProvider
     */
    public function test_createVtrAndV5CfromDvlaVehicle_whenDvlaBodyTypeIsInvalid_createVehicleWithDefaultBodyType($invalidDvlaBodyType)
    {
        $dvlaVehicle = VOF::dvlaVehicle();
        $dvlaVehicle->setV5DocumentNumber(null);
        $dvlaVehicle->setBodyType($invalidDvlaBodyType);
        $vehicleClassCode = VehicleClassCode::CLASS_7;
        $dvlaVehicle->setDesignedGrossWeight(1000);
        $dvlaVehicle->setMakeCode("BB");
        $dvlaVehicle->setModelCode("COOPER");

        $vtrCapture = ArgCapture::create();

        $this->returningOn($this->mockVehicleCatalog, VOF::countryOfRegistration(3), 'getCountryOfRegistrationByCode');
        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::vehicleClass(VehicleClassId::CLASS_7, VehicleClassCode::CLASS_7),
            'getVehicleClassByCode'
        );
        $this->returningOn($this->mockDvlaVehicleRepository, $dvlaVehicle);

        $this->mockVehicleCatalog
            ->expects($this->any())
            ->method("findBodyTypeByCode")
            ->willReturnCallback(function ($code) use ($invalidDvlaBodyType) {
                if ($code === $invalidDvlaBodyType) {
                    return null;
                }

                return VOF::bodyType(103, '0', 'Not Provided');
            });

        $colourCode = 'R';
        $secondaryColourCode = 'G';
        $this->returningOn(
            $this->mockVehicleCatalog,
            MultiCallStubBuilder::of()
                ->add([$colourCode, $this->anything()], VOF::colour(1, $colourCode))
                ->add([$secondaryColourCode, $this->anything()], VOF::colour(2, $secondaryColourCode))
                ->build(),
            'getColourByCode'
        );
        $this->returningOn($this->mockVehicleCatalog, VOF::fuelType(), 'findFuelTypeByPropulsionCode');
        $this->returningOn($this->mockVehicleCatalog, VOF::model(), 'getModelByCode');
        $this->returningOn($this->mockVehicleCatalog, VOF::make(), 'findMakeByCode');

        $this->returningOn(
            $this->mockVehicleCatalog, VOF::weightSource(WeightSourceCode::DGW), 'getWeightSourceByCode'
        );

        $this->mockVehicleRepository
            ->expects($this->any())
            ->method('save')
            ->with($vtrCapture());

        $this->mockVehicleV5CRepository
            ->expects($this->never())
            ->method('save');

        $vehicleId = $this->paramObfuscator->obfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $dvlaVehicle->getId());
        $this->markTestSkipped('BL-1164 is parked to investigate lifting vehicle\'s entity relationship. Talk to Ali');
        $this->createService()->createVtrAndV5CFromDvlaVehicle($vehicleId, $vehicleClassCode);

        /** @var Vehicle $v */
        $v = $vtrCapture->get();

        $this->assertEquals($dvlaVehicle->getVin(), $v->getVin());
        $this->assertEquals($dvlaVehicle->getRegistration(), $v->getRegistration());
        $this->assertEquals($dvlaVehicle->getManufactureDate(), $v->getManufactureDate());
        $this->assertEquals($dvlaVehicle->getFirstRegistrationDate(), $v->getFirstUsedDate());
        $this->assertEquals($dvlaVehicle->getPrimaryColour(), $v->getColour()->getCode());
        $this->assertEquals($dvlaVehicle->getSecondaryColour(), $v->getSecondaryColour()->getCode());
        $this->assertEquals($dvlaVehicle->getMakeName(), $v->getMakeName());
        $this->assertEquals($dvlaVehicle->getModelName(), $v->getModelName());
        $this->assertEquals($dvlaVehicle->getCylinderCapacity(), $v->getCylinderCapacity());
        $this->assertEquals(VehicleService::DEFAULT_BODY_TYPE_CODE, $v->getBodyType()->getCode());
        $this->assertEquals($dvlaVehicle->getFuelType(), $v->getFuelType()->getCode());
        $this->assertEquals($dvlaVehicle->getDesignedGrossWeight(), $v->getWeight());
        $this->assertEquals($dvlaVehicle->getDvlaVehicleId(), $v->getDvlaVehicleId());
        $this->assertEquals(WeightSourceCode::DGW, $v->getWeightSource()->getCode());
    }

    public function invalidDvlaBodyTypeCodeProvider()
    {
        return [[""], [null], ["xxx"]];
    }

    public function testVehicleRecordFromDvlaVehicleWithNoMakeOrModelCodeAndTextShouldBeUnknown()
    {
        $dvlaVehicle = VOF::dvlaVehicle();
        $dvlaVehicle->setMakeCode(null);
        $dvlaVehicle->setModelCode(null);
        $dvlaVehicle->setMakeInFull(null);

        $vehicleClassCode = VehicleClassCode::CLASS_4;

        $dvlaCapture = ArgCapture::create();

        $this->returningOn($this->mockVehicleCatalog, VOF::countryOfRegistration(3), 'getCountryOfRegistrationByCode');
        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::vehicleClass(VehicleClassId::CLASS_4, VehicleClassCode::CLASS_4),
            'getVehicleClassByCode'
        );
        $this->returningOn($this->mockDvlaVehicleRepository, $dvlaVehicle);
        $this->returningOn($this->mockVehicleCatalog, VOF::bodyType(), "findBodyTypeByCode");

        $colourCode = 'R';
        $secondaryColourCode = 'G';
        $this->returningOn(
            $this->mockVehicleCatalog,
            MultiCallStubBuilder::of()
                ->add([$colourCode, $this->anything()], VOF::colour(1, $colourCode))
                ->add([$secondaryColourCode, $this->anything()], VOF::colour(2, $secondaryColourCode))
                ->build(),
            'getColourByCode'
        );
        $this->returningOn($this->mockVehicleCatalog, VOF::model(), 'getModelByCode');

        $this->mockDvlaVehicleRepository
            ->expects($this->any())
            ->method('save')
            ->with($dvlaCapture());

        $this->markTestSkipped('BL-1164 is parked to investigate lifting vehicle\'s entity relationship. Talk to Ali');
        $this->createService()->createVtrAndV5CFromDvlaVehicle(self::VEHICLE_ID_ENC, $vehicleClassCode);

        /** @var DvlaVehicle $savedDvla */
        $savedDvla = $dvlaCapture->get();

        $this->assertNotNull($savedDvla->getVehicleId());
        $this->assertEquals($savedDvla->getVehicleId()->getMakeName(), 'Unknown');
    }

    public function testVehicleRecordFromDvlaVehicleWithFullMakeTextShouldBeFullMakeText()
    {
        $dvlaVehicle = VOF::dvlaVehicle();
        $dvlaVehicle->setMakeCode(null);
        $dvlaVehicle->setModelCode(null);
        $dvlaVehicle->setMake(null);
        $dvlaVehicle->setModel(null);
        $dvlaVehicle->setMakeInFull('Ford Supercharger');

        $vehicleClassCode = VehicleClassCode::CLASS_4;
        $dvlaCapture = ArgCapture::create();

        $this->returningOn($this->mockVehicleCatalog, VOF::countryOfRegistration(3), 'getCountryOfRegistrationByCode');
        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::vehicleClass(VehicleClassId::CLASS_4, VehicleClassCode::CLASS_4),
            'getVehicleClassByCode'
        );
        $this->returningOn($this->mockDvlaVehicleRepository, $dvlaVehicle);
        $this->returningOn($this->mockVehicleCatalog, VOF::bodyType(), "findBodyTypeByCode");

        $colourCode = 'R';
        $secondaryColourCode = 'G';
        $this->returningOn(
            $this->mockVehicleCatalog,
            MultiCallStubBuilder::of()
                ->add([$colourCode, $this->anything()], VOF::colour(1, $colourCode))
                ->add([$secondaryColourCode, $this->anything()], VOF::colour(2, $secondaryColourCode))
                ->build(),
            'getColourByCode'
        );
        $this->returningOn($this->mockVehicleCatalog, VOF::model(), 'getModelByCode');

        $this->mockDvlaVehicleRepository
            ->expects($this->any())
            ->method('save')
            ->with($dvlaCapture());

        $this->markTestSkipped('BL-1164 is parked to investigate lifting vehicle\'s entity relationship. Talk to Ali');
        $this->createService()->createVtrAndV5CFromDvlaVehicle(self::VEHICLE_ID_ENC, $vehicleClassCode);

        /** @var DvlaVehicle $savedDvla */
        $savedDvla = $dvlaCapture->get();

        $this->assertNotNull($savedDvla->getVehicleId());
        $this->assertEquals($savedDvla->getVehicleId()->getMakeName(), 'Ford Supercharger');
    }

    public function testVehicleRecordFromDvlaVehicleWithNoMakeButWithModelCodeShouldBeUnknownWithModel()
    {
        $dvlaVehicle = VOF::dvlaVehicle();
        $dvlaVehicle->setMakeCode(null);
        $dvlaVehicle->setMake(null);
        $dvlaVehicle->setModelCode('ABC');
        $dvlaVehicle->setModel(
            (new Model())->setCode('ABC')
                ->setId(1)
                ->setName('DB9')
        );
        $dvlaVehicle->setMakeInFull(null);

        $vehicleClassCode = VehicleClassCode::CLASS_4;

        $dvlaCapture = ArgCapture::create();

        $this->returningOn($this->mockVehicleCatalog, VOF::countryOfRegistration(3), 'getCountryOfRegistrationByCode');
        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::vehicleClass(VehicleClassId::CLASS_4, VehicleClassCode::CLASS_4),
            'getVehicleClassByCode'
        );
        $this->returningOn($this->mockDvlaVehicleRepository, $dvlaVehicle);
        $this->returningOn($this->mockVehicleCatalog, VOF::bodyType(), "findBodyTypeByCode");

        $colourCode = 'R';
        $secondaryColourCode = 'G';
        $this->returningOn(
            $this->mockVehicleCatalog,
            MultiCallStubBuilder::of()
                ->add([$colourCode, $this->anything()], VOF::colour(1, $colourCode))
                ->add([$secondaryColourCode, $this->anything()], VOF::colour(2, $secondaryColourCode))
                ->build(),
            'getColourByCode'
        );
        $this->returningOn($this->mockVehicleCatalog, VOF::model(), 'getModelByCode');

        $this->mockDvlaVehicleRepository
            ->expects($this->any())
            ->method('save')
            ->with($dvlaCapture());

        $this->markTestSkipped('BL-1164 is parked to investigate lifting vehicle\'s entity relationship. Talk to Ali');
        $this->createService()->createVtrAndV5CFromDvlaVehicle(self::VEHICLE_ID_ENC, $vehicleClassCode);

        /** @var DvlaVehicle $savedDvla */
        $savedDvla = $dvlaCapture->get();

        $this->assertNotNull($savedDvla->getVehicleId());
        $this->assertEquals($savedDvla->getVehicleId()->getMakeName(), 'Unknown');
    }

    public function testVehicleRecordFromDvlaVehicleWithMakeButWithNoModelCodeShouldBeMakeWithUnknown()
    {
        $dvlaVehicle = VOF::dvlaVehicle();
        $dvlaVehicle->setMakeCode('ABC');
        $dvlaVehicle->setMake(
            (new Make())->setCode('ABC')
                ->setName('Aston Martin')
                ->setId(1)
        );
        $dvlaVehicle->setModelCode(null);
        $dvlaVehicle->setMakeInFull(null);

        $vehicleClassCode = VehicleClassCode::CLASS_4;

        $dvlaCapture = ArgCapture::create();

        $this->returningOn($this->mockVehicleCatalog, VOF::countryOfRegistration(3), 'getCountryOfRegistrationByCode');
        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::vehicleClass(VehicleClassId::CLASS_4, VehicleClassCode::CLASS_4),
            'getVehicleClassByCode'
        );
        $this->returningOn($this->mockDvlaVehicleRepository, $dvlaVehicle);
        $this->returningOn($this->mockVehicleCatalog, VOF::bodyType(), "findBodyTypeByCode");

        $colourCode = 'R';
        $secondaryColourCode = 'G';
        $this->returningOn(
            $this->mockVehicleCatalog,
            MultiCallStubBuilder::of()
                ->add([$colourCode, $this->anything()], VOF::colour(1, $colourCode))
                ->add([$secondaryColourCode, $this->anything()], VOF::colour(2, $secondaryColourCode))
                ->build(),
            'getColourByCode'
        );
        $this->returningOn($this->mockVehicleCatalog, VOF::model(), 'getModelByCode');

        $this->mockDvlaVehicleRepository
            ->expects($this->any())
            ->method('save')
            ->with($dvlaCapture());

        $this->markTestSkipped('BL-1164 is parked to investigate lifting vehicle\'s entity relationship. Talk to Ali');
        $this->createService()->createVtrAndV5CFromDvlaVehicle(self::VEHICLE_ID_ENC, $vehicleClassCode);

        /** @var DvlaVehicle $savedDvla */
        $savedDvla = $dvlaCapture->get();

        $this->assertNotNull($savedDvla->getVehicleId());
        $this->assertEquals($savedDvla->getVehicleId()->getMakeName(), 'Unknown');
    }

    public function testLogDvlaVehicleImportChangesShouldSaveImportChangesData()
    {
        $tester = new Person();
        $tester->setId(1);

        $vehicle = VOF::dvlaImportedVehicle();
        $primaryColourCode = 'A';
        $secondaryColourCode = 'B';
        $fuelTypeCode = 'PE';
        $vehicleClassCode = 22;
        $changesCapture = ArgCapture::create();

        $this->mockDvlaVehicleImportChangesRepository->expects($this->any())->method('save')->with($changesCapture());
        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::vehicleClass(VehicleClassId::CLASS_4, VehicleClassCode::CLASS_4),
            'getVehicleClass'
        );

        $this->returningOn(
            $this->mockVehicleCatalog,
            MultiCallStubBuilder::of()
                ->add([1, $this->anything()], VOF::colour(1, $primaryColourCode))
                ->add([2, $this->anything()], VOF::colour(2, $secondaryColourCode))
                ->build(),
            'getColour'
        );

        $this->returningOn($this->mockVehicleCatalog, VOF::fuelType(), 'getFuelType');
        $this->returningOn($this->mockVehicleCatalog, VOF::model(), 'getModelByCode');

        $this->createService()->logDvlaVehicleImportChanges(
            $tester,
            $vehicle,
            $vehicleClassCode,
            $primaryColourCode,
            $secondaryColourCode,
            $fuelTypeCode
        );

        /** @var \DvsaEntities\Entity\DvlaVehicleImportChangeLog $dvlaImportChanges */
        $dvlaImportChanges = $changesCapture->get();

        $this->assertEquals($vehicle->getId(), $dvlaImportChanges->getVehicleId());
        $this->assertEquals($primaryColourCode, $dvlaImportChanges->getColour());
        $this->assertEquals($secondaryColourCode, $dvlaImportChanges->getSecondaryColour());
        $this->assertEquals($fuelTypeCode, $dvlaImportChanges->getFuelType());
    }

    /**
     * @param Vehicle $entity
     * @param AbstractVehicleDto $dto
     */
    private function assertVehicleEntityEqualsDto(Vehicle $entity, AbstractVehicleDto $dto)
    {
        $this->assertEquals($entity->getVin(), $dto->getVin());
        $this->assertEquals($entity->getRegistration(), $dto->getRegistration());
        $this->assertEquals($entity->getCylinderCapacity(), $dto->getCylinderCapacity());

        $this->assertEquals($entity->getYear(), $dto->getYear());
        $this->assertEquals(
            DateTimeApiFormat::date($entity->getManufactureDate()),
            $dto->getManufactureDate()
        );
        $this->assertEquals(
            DateTimeApiFormat::date($entity->getFirstRegistrationDate()),
            $dto->getFirstRegistrationDate()
        );
        $this->assertEquals(
            DateTimeApiFormat::date($entity->getFirstUsedDate()),
            $dto->getFirstUsedDate()
        );

        $this->assertInstanceOf(\DateTime::class, $entity->getFirstRegistrationDate());
        $this->assertEquals(
            DateTimeApiFormat::date($entity->getFirstRegistrationDate()),
            $dto->getFirstRegistrationDate()
        );

        $makeName = $entity->getMakeName();
        $dto->setMakeName($makeName);
        $this->assertEquals($makeName, $dto->getMakeName());

        $modelName = $entity->getModelName();
        $dto->setModelName($modelName);
        $this->assertEquals($modelName, $dto->getModelName());

        $vehicleClass = $entity->getVehicleClass();
        $vehicleClassDto = new VehicleClassDto();
        $vehicleClassDto
            ->setId($vehicleClass->getId())
            ->setCode($vehicleClass->getCode())
            ->setName($vehicleClass->getName());

        $this->assertEquals($vehicleClassDto, $dto->getVehicleClass());

        $colour = $entity->getColour();

        $this->assertNotNull($colour);
        $this->assertNotNull($entity->getSecondaryColour());

        //  --  check colour --
        $expect = $colour;
        $actual = $dto->getColour();
        $this->assertEquals($expect->getCode(), $actual->getCode());
        $this->assertEquals($expect->getName(), $actual->getName());

        //  --  check secondary colour --
        $expect = $entity->getSecondaryColour();
        $actual = $dto->getColourSecondary();
        $this->assertEquals($expect->getCode(), $actual->getCode());
        $this->assertEquals($expect->getName(), $actual->getName());

        //  --  check body type --
        $expect = $entity->getBodyType();
        $actual = $dto->getBodyType();
        $this->assertEquals($expect->getId(), $actual->getId());
        $this->assertEquals($expect->getName(), $actual->getName());

        //  --  check transmission type --
        $expect = $entity->getTransmissionType();
        $actual = $dto->getTransmissionType();
        $this->assertEquals($expect->getId(), $actual->getId());
        $this->assertEquals($expect->getName(), $actual->getName());
    }

    /**
     * @param DvlaVehicle $entity
     * @param AbstractVehicleDto $dto
     */
    private function assertDvlaVehicleEntityEqualsDto(DvlaVehicle $entity, AbstractVehicleDto $dto)
    {
        $this->assertEquals($entity->getVin(), $dto->getVin());
        $this->assertEquals($entity->getRegistration(), $dto->getRegistration());
        $this->assertEquals($entity->getCylinderCapacity(), $dto->getCylinderCapacity());

        $this->assertInstanceOf(\DateTime::class, $entity->getFirstRegistrationDate());
        $this->assertEquals(
            DateTimeApiFormat::date($entity->getFirstRegistrationDate()),
            $dto->getFirstRegistrationDate()
        );

        $this->assertNotNull($entity->getPrimaryColour());
        $this->assertNotNull($entity->getSecondaryColour());

        //  --  check colour --
        $expectedColourCode = $entity->getPrimaryColour();
        $actual = $dto->getColour();
        $this->assertEquals($expectedColourCode, $actual->getCode());

        //  --  check secondary colour --
        $expectedSecondaryColourCode = $entity->getSecondaryColour();
        $actual = $dto->getColourSecondary();
        $this->assertEquals($expectedSecondaryColourCode, $actual->getCode());

        //  --  check body type --
        $expect = $entity->getBodyType();
        $actual = $dto->getBodyType();
        $this->assertEquals($expect, $actual->getCode());
    }

    public function testCreateGivenVehicleDataShouldSaveIt()
    {
        $inputData = self::dataCreateVehicle();
        $inputData['oneTimePassword'] = self::OTP_VALID;

        $colourId = 1;
        $fuelTypeId = 1;
        $colourCode = $inputData['colour'];
        $secondaryColourId = 2;
        $secondaryColourCode = $inputData['secondaryColour'];
        $countryOfRegistrationId = $inputData['countryOfRegistration'];
        $transTypeId = $inputData['transmissionType'];
        $vehicleClassCode = $inputData['testClass'];
        $vehicleClassId = 4;
        $makeId = $inputData['make'];
        $modelId = $inputData['model'];
        $modelDetailId = $inputData['modelType'];

        $vehicleCapture = ArgCapture::create();
        $this->mockNewVehicleService->expects($this->any())->method('createDvsaVehicle')->with($vehicleCapture());

        $this->mockNewVehicleService->expects($this->any())
            ->method('createDvsaVehicle')
            ->with($vehicleCapture())
            ->willReturn(VOF::vehicle(5));

        $this->mockMotTestService->expects($this->any())
            ->method('createMotTest')
            ->willReturn(new MotTest());

        $this->returningOn(
            $this->mockVehicleCatalog,
            MultiCallStubBuilder::of()
                ->add([$colourCode, $this->anything()], VOF::colour($colourId, $colourCode))
                ->add([$secondaryColourCode, $this->anything()], VOF::colour($secondaryColourId, $secondaryColourCode))
                ->build(),
            'getColourByCode'
        );

        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::vehicleClass(VehicleClassId::CLASS_4, VehicleClassCode::CLASS_4),
            'getVehicleClassByCode'
        );
        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::countryOfRegistration($countryOfRegistrationId),
            'getCountryOfRegistration'
        );
        $this->returningOn($this->mockVehicleCatalog, VOF::transmissionType($transTypeId), 'getTransmissionType');
        $this->returningOn($this->mockVehicleCatalog, VOF::fuelType(), 'getFuelType');
        $this->returningOn($this->mockVehicleCatalog, VOF::make($makeId), 'getMakeByCode');
        $this->returningOn(
            $this->mockVehicleCatalog, VOF::model($modelId, 'COPER', 'Cooper', VOF::make($makeId)),
            'findModel'
        );
        $this->returningOn($this->mockVehicleCatalog, VOF::modelDetail($modelDetailId), 'getModelDetail');

        $this->paramObfuscator = XMock::of(ParamObfuscator::class);
        $this->paramObfuscator->expects($this->any())
            ->method('obfuscate')
            ->withAnyParameters()
            ->will($this->returnValue(1));

        $this->createService()->create($inputData);

        $expectedRequest = new CreateDvsaVehicleRequest();
        $expectedRequest->setRegistration($inputData['registrationNumber']);
        $expectedRequest->setColourId($colourId);
        $expectedRequest->setSecondaryColourId($secondaryColourId);
        $expectedRequest->setCountryOfRegistrationId($countryOfRegistrationId);
        $expectedRequest->setVin($inputData['vin']);
        $expectedRequest->setCylinderCapacity($inputData['cylinderCapacity']);
        $expectedRequest->setFirstUsedDate(new \DateTime($inputData['dateOfFirstUse']));
        $expectedRequest->setMakeId($makeId);
        $expectedRequest->setModelId($modelId);
        $expectedRequest->setVehicleClassId($vehicleClassCode);
        $expectedRequest->setFuelTypeId($fuelTypeId);
        $expectedRequest->setTransmissionTypeId($transTypeId);
        $expectedRequest->setOneTimePassword('123456');

        $actualRequest = $vehicleCapture->get();

        $this->assertEquals($expectedRequest, $actualRequest);
    }

    /**
     * @expectedException \DvsaAuthentication\Service\Exception\OtpException
     */
    public function testCreateCalledByUserWithoutPermissionAndInvalidOtpThrowsAnException()
    {
        $input = $this->dataCreateVehicle();
        $input['oneTimePassword'] = self::OTP_INVALID;

        $this->createService()->create($input);
    }

    /**
     * @expectedException \DvsaAuthentication\Service\Exception\OtpException
     */
    public function testCreateCalledByUserWithoutPermissionRequiresOneTimePassword()
    {
        $input = $this->dataCreateVehicle();

        $this->createService()->create($input);
    }

    private static function dataCreateVehicle()
    {
        return [
            'vin' => VOF::EXAMPLE_VIN,
            'registrationNumber' => VOF::EXAMPLE_VRM,
            'cylinderCapacity' => 1234,
            'manufactureDate' => '1990-12-12',
            'firstRegistrationDate' => '1990-12-23',
            'dateOfFirstUse' => '2000-12-12',
            'make' => 1,
            'makeOther' => '',
            'model' => 2,
            'modelOther' => '',
            'modelType' => 3,
            'colour' => 'R',
            'fuelTypeId' => FuelTypeId::PETROL,
            'testClass' => VehicleClassCode::CLASS_4,
            'countryOfRegistration' => 9,
            'transmissionType' => 10,
            'secondaryColour' => 'G',
            'vtsId' => 1
        ];
    }

    private function createService()
    {
        return new VehicleService(
            $this->mockAuthService,
            $this->mockVehicleRepository,
            $this->mockVehicleV5CRepository,
            $this->mockDvlaVehicleRepository,
            $this->mockDvlaVehicleImportChangesRepository,
            $this->mockDvlaMakeModelMapRepository,
            $this->mockVehicleCatalog,
            $this->mockValidator,
            $this->mockOtpService,
            $this->paramObfuscator,
            $this->motTestServiceProvider,
            $this->motIdentityProviderInterface,
            $this->personRepository,
            $this->transaction,
            $this->mockNewVehicleService
        );
    }

    private function returningOn(MockObj $repo, $returnObject, $method = 'get')
    {
        $repo->expects($this->any())->method($method)->will(
            is_a($returnObject, \PHPUnit_Framework_MockObject_Stub::class)
                ? $returnObject
                : $this->returnValue($returnObject)
        );
    }

    private function getNewDvlaVehicleData()
    {
        $dvlaVehicleData = json_decode(
            json_encode(
                [
                    'id' => 2,
                    'amendedOn' => '2016-02-03',
                    'registration' => 'DII4454',
                    'vin' => '1M7GDM9AXKP042777',
                    'emptyVrmReason' => null,
                    'emptyVinReason' => null,
                    'make' => 'PORSCHE',
                    'model' => 'BOXSTER',
                    'colour' => 'Red',
                    'colourSecondary' => 'Not Stated',
                    'fuelTypeId' => FuelTypeId::PETROL,
                    'vehicleClass' => '4',
                    'bodyType' => '2 Door Saloon',
                    'cylinderCapacity' => 1700,
                    'transmissionType' => 'Automatic',
                    'firstRegistrationDate' => '2001-03-02',
                    'firstUsedDate' => '2001-03-02',
                    'manufactureDate' => '2001-03-02',
                    'isNewAtFirstReg' => false,
                    'weight' => null
                ]
            )
        );

        $dvlaVehicle = new NewDvlaVehicle($dvlaVehicleData);

        return $dvlaVehicle;
    }

    private function getNewDvsaVehicleData()
    {
        $dvsaVehicleData = json_decode(
            json_encode(
                [
                    'id' => 2,
                    'amendedOn' => '2016-02-03',
                    'registration' => 'DII4454',
                    'vin' => '1M7GDM9AXKP042777',
                    'emptyVrmReason' => null,
                    'emptyVinReason' => null,
                    'make' => 'PORSCHE',
                    'model' => 'BOXSTER',
                    'colour' => 'Red',
                    'colourSecondary' => 'Not Stated',
                    'fuelTypeId' => FuelTypeId::PETROL,
                    'vehicleClass' => '4',
                    'bodyType' => '2 Door Saloon',
                    'cylinderCapacity' => 1700,
                    'transmissionType' => 'Automatic',
                    'firstRegistrationDate' => '2001-03-02',
                    'firstUsedDate' => '2001-03-02',
                    'manufactureDate' => '2001-03-02',
                    'isNewAtFirstReg' => false,
                    'weight' => null
                ]
            )
        );

        $dvsaVehicle = new NewDvsaVehicle($dvsaVehicleData);

        return $dvsaVehicle;
    }
}
