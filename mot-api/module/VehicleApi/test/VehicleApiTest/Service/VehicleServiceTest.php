<?php

namespace VehicleApiTest\Service;

use DataCatalogApi\Service\VehicleCatalogService;
use Doctrine\ORM\EntityManager;
use Dvsa\Mot\ApiClient\Request\CreateDvlaVehicleRequest;
use Dvsa\Mot\ApiClient\Request\CreateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvlaVehicle as NewDvlaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle as NewDvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService as NewVehicleService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Database\Transaction;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Vehicle\AbstractVehicleDto;
use DvsaCommon\Dto\Vehicle\DvlaVehicleDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\VehicleClassId;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\ArgCapture;
use DvsaCommonTest\TestUtils\MultiCallStubBuilder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\DvlaVehicle;
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
use DvsaMotApi\Service\Validator\VehicleValidator;
use DvsaMotApiTest\Factory\VehicleObjectsFactory as VOF;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use VehicleApi\Service\VehicleService;
use DvsaAuthentication\Identity;

/**
 * it test functionality of class VehicleService.
 */
class VehicleServiceTest extends AbstractServiceTestCase
{
    const OTP_VALID = '123456';
    const OTP_INVALID = '000000';

    const VEHICLE_ID = 9999;
    const VEHICLE_ID_ENC = 'jq33IixSpBsx4rglOvxByg';

    const SET_MASS_IN_SERVICE_WEIGHT_METHOD_NAME = 'setMassInServiceWeight';
    const GET_MASS_IN_SERVICE_WEIGHT_METHOD_NAME = 'getMassInServiceWeight';
    const SET_DESIGNED_GROSS_WEIGHT_METHOD_NAME = 'setDesignedGrossWeight';
    const GET_DESIGNED_GROSS_WEIGHT_METHOD_NAME = 'getDesignedGrossWeight';

    /** @var MotAuthorisationServiceInterface|MockObj $mockAuthService */
    private $mockAuthService;

    /** @var VehicleRepository|MockObj $mockVehicleRepository */
    private $mockVehicleRepository;

    /** @var VehicleV5CRepository|MockObj $mockVehicleV5CRepository */
    private $mockVehicleV5CRepository;

    /** @var DvlaVehicleRepository|MockObj $mockDvlaVehicleRepository */
    private $mockDvlaVehicleRepository;

    /** @var DvlaVehicleImportChangesRepository|MockObj $mockDvlaVehicleImportChangesRepository */
    private $mockDvlaVehicleImportChangesRepository;

    /** @var EntityRepository|MockObj $mockDvlaMakeModelMapRepository */
    private $mockDvlaMakeModelMapRepository;

    /** @var VehicleCatalogService|MockObj $mockVehicleCatalog */
    private $mockVehicleCatalog;

    /** @var VehicleValidator|MockObj $mockValidator */
    private $mockValidator;

    /** @var ParamObfuscator $paramObfuscator */
    private $paramObfuscator;

    /** @var MotTestServiceProvider $motTestServiceProvider */
    private $motTestServiceProvider;

    /** @var PersonRepository $personRepository */
    private $personRepository;

    /** @var MotTestService|MockObj $mockMotTestService */
    private $mockMotTestService;

    /** @var MotIdentityProviderInterface $motIdentityProviderInterface */
    private $motIdentityProviderInterface;

    /** @var Transaction $transaction */
    private $transaction;

    /** @var NewVehicleService|MockObj $mockNewVehicleService */
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
        $this->motIdentityProviderInterface
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn(XMock::of(Identity::class));
        $this->mockNewVehicleService = XMock::of(NewVehicleService::class);
        $this->mockNewVehicleService
            ->expects($this->any())
            ->method('createVehicleFromDvla')
            ->willReturn($this->getNewDvlaVehicleData());
        $this->mockNewVehicleService
            ->expects($this->any())
            ->method('createDvsaVehicle')
            ->willReturn($this->getNewDvsaVehicleData());

        $this->personRepository = XMock::of(PersonRepository::class);
        $this->personRepository->expects($this->any())->method('get')->willReturn(new Person());
        $this->transaction = new Transaction(XMock::of(EntityManager::class));

        $this->paramObfuscator = XMock::of(ParamObfuscator::class);
        $this->mockValidator = new VehicleValidator();
        $this->motTestServiceProvider = XMock::of(MotTestServiceProvider::class);
        $this->motTestServiceProvider->expects($this->any())->method('getService')->willReturn($this->mockMotTestService);
    }

    public function testGetVehicleById()
    {
        $id = 2;
        $entity = 'VEHICLE_ENTITY';

        $this->mockVehicleRepository
            ->expects($this->once())
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
        $dvlaVehicle->setMakeCode('BB');
        $dvlaVehicle->setModelCode('COOPER');
        $vehicleClassCode = VehicleClassCode::CLASS_4;

        $vtrCapture = ArgCapture::create();

        $this->vehicleServiceMockMethods($vehicleClassCode, $dvlaVehicle);

        $this->mockNewVehicleService
            ->expects($this->any())
            ->method('createVehicleFromDvla')
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
     * @dataProvider weightAndVehicleClassCodeDataProvider
     *
     * @param string   $setWeightMethod
     * @param int|null $weight
     * @param string   $getWeightMethod
     * @param string   $vehicleClassCode
     */
    public function testWeightIsSetCorrectlyAfterCreatingVtrAndV5CfromDvlaVehicle(
        $setWeightMethod, $weight, $getWeightMethod, $vehicleClassCode
    ) {
        $dvlaVehicle = VOF::dvlaVehicle();
        $dvlaVehicle->$setWeightMethod($weight);

        $vtrCapture = ArgCapture::create();

        $this->vehicleServiceMockMethods($vehicleClassCode, $dvlaVehicle);

        $this->mockNewVehicleService
            ->expects($this->any())
            ->method('createVehicleFromDvla')
            ->with($vtrCapture());

        $this->mockNewVehicleService
            ->expects($this->any())
            ->method('createVehicleFromDvla')
            ->will($this->returnCallback(
                function ($dvlaVehicleRequest) use ($dvlaVehicle, $weight, $getWeightMethod) {
                    if ($weight == null || $weight == 0) {
                        $dvlaVehicleRequestJsonData =
                            json_decode($dvlaVehicleRequest->getVehicleData()->toJson(), true);
                        $this->assertArrayNotHasKey('weight', $dvlaVehicleRequestJsonData);
                    } else {
                        $this->assertEquals(
                            $dvlaVehicle->$getWeightMethod(),
                            $dvlaVehicleRequest->getVehicleData()->weight
                        );
                    }
                }
            ));

        $this->createService()->createVtrAndV5CFromDvlaVehicle($dvlaVehicle->getId(), $vehicleClassCode);
    }

    public function weightAndVehicleClassCodeDataProvider()
    {
        /* If the vehicle class code of the vehicle is 3 or 4, use the Mass in Service Weight
           If the vehicle class code of the vehicle is 5 or 7, use the Designed Gross Weight */
        return [
            [self::SET_MASS_IN_SERVICE_WEIGHT_METHOD_NAME, null,
             self::GET_MASS_IN_SERVICE_WEIGHT_METHOD_NAME, VehicleClassCode::CLASS_3, ],
            [self::SET_MASS_IN_SERVICE_WEIGHT_METHOD_NAME, 0,
             self::GET_MASS_IN_SERVICE_WEIGHT_METHOD_NAME, VehicleClassCode::CLASS_3, ],
            [self::SET_MASS_IN_SERVICE_WEIGHT_METHOD_NAME, 10000,
             self::GET_MASS_IN_SERVICE_WEIGHT_METHOD_NAME, VehicleClassCode::CLASS_3, ],
            [self::SET_DESIGNED_GROSS_WEIGHT_METHOD_NAME, null,
             self::GET_DESIGNED_GROSS_WEIGHT_METHOD_NAME, VehicleClassCode::CLASS_5, ],
            [self::SET_DESIGNED_GROSS_WEIGHT_METHOD_NAME, 0,
             self::GET_DESIGNED_GROSS_WEIGHT_METHOD_NAME, VehicleClassCode::CLASS_5, ],
            [self::SET_DESIGNED_GROSS_WEIGHT_METHOD_NAME, 10000,
             self::GET_DESIGNED_GROSS_WEIGHT_METHOD_NAME, VehicleClassCode::CLASS_5, ],
        ];
    }

    public function testCreateVtrAndV5CfromDvlaVehicleGivenDvlaVehicleShouldCreateLinkBetweenDvlaAndVtr()
    {
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
        $this->returningOn($this->mockVehicleCatalog, VOF::bodyType(), 'findBodyTypeByCode');

        $this->returningOn(
            $this->mockVehicleCatalog, VOF::weightSource(WeightSourceCode::MISW), 'getWeightSourceByCode'
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

    public function invalidDvlaBodyTypeCodeProvider()
    {
        return [[''], [null], ['xxx']];
    }

    public function testLogDvlaVehicleImportChangesShouldSaveImportChangesData()
    {
        $tester = new Person();
        $tester->setId(1);

        $vehicle = VOF::dvlaImportedVehicle();
        $primaryColourCode = 'A';
        $secondaryColourCode = 'B';
        $fuelTypeCode = FuelTypeCode::PETROL;
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
     * @param Vehicle            $entity
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
     * @param DvlaVehicle        $entity
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

        $colourId = 1;
        $fuelTypeCode = FuelTypeCode::PETROL;
        $colourCode = $inputData['colour'];
        $secondaryColourId = 2;
        $secondaryColourCode = $inputData['secondaryColour'];
        $countryOfRegistrationId = $inputData['countryOfRegistration'];
        $transTypeId = $inputData['transmissionType'];
        $vehicleClassCode = $inputData['testClass'];
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
        $expectedRequest->setColourCode($colourCode);
        $expectedRequest->setSecondaryColourCode($secondaryColourCode);
        $expectedRequest->setCountryOfRegistrationId($countryOfRegistrationId);
        $expectedRequest->setVin($inputData['vin']);
        $expectedRequest->setCylinderCapacity($inputData['cylinderCapacity']);
        $expectedRequest->setFirstUsedDate(new \DateTime($inputData['dateOfFirstUse']));
        $expectedRequest->setMakeId($makeId);
        $expectedRequest->setModelId($modelId);
        $expectedRequest->setVehicleClassCode($vehicleClassCode);
        $expectedRequest->setFuelTypeCode($fuelTypeCode);
        $expectedRequest->setTransmissionTypeId($transTypeId);

        $actualRequest = $vehicleCapture->get();

        $this->assertEquals($expectedRequest, $actualRequest);
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
            'testClass' => VehicleClassCode::CLASS_4,
            'fuelTypeCode' => FuelTypeCode::PETROL,
            'countryOfRegistration' => 9,
            'transmissionType' => 10,
            'secondaryColour' => 'G',
            'vtsId' => 1,
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
                    'registration' => 'YK02OML',
                    'vin' => '1HGCM82633A004352',
                    'emptyVrmReason' => null,
                    'emptyVinReason' => null,
                    'make' => [
                        'id' => 5,
                        'name' => 'PORSCHE',
                    ],
                    'model' => [
                        'id' => 6,
                        'name' => 'BOXSTER',
                    ],
                    'colour' => [
                        'code' => 'C',
                        'name' => 'Red',
                    ],
                    'colourSecondary' => [
                        'code' => 'W',
                        'name' => 'Not Stated',
                    ],
                    'vehicleClass' => ['code' => '4', 'name' => '4'],
                    'bodyType' => '2 Door Saloon',
                    'cylinderCapacity' => 1700,
                    'transmissionType' => 'Automatic',
                    'fuelType' => [
                        'code' => FuelTypeCode::PETROL,
                        'name' => 'Petrol',
                    ],
                    'firstRegistrationDate' => new \DateTime('2001-03-01'),
                    'firstUsedDate' => new \DateTime('2001-03-02'),
                    'manufactureDate' => new \DateTime('2000-12-12'),
                    'isNewAtFirstReg' => false,
                    'weight' => null,
                    'weightSource' => [
                        'code' => 'U',
                        'name' => 'unladen',
                    ],
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
                    'make' => [
                        'id' => 5,
                        'name' => 'PORSCHE',
                    ],
                    'model' => [
                        'id' => 6,
                        'name' => 'BOXSTER',
                    ],
                    'colour' => [
                        'code' => 'C',
                        'name' => 'Red',
                    ],
                    'colourSecondary' => [
                        'code' => 'W',
                        'name' => 'Not Stated',
                    ],
                    'vehicleClass' => ['code' => '4', 'name' => '4'],
                    'bodyType' => '2 Door Saloon',
                    'cylinderCapacity' => 1700,
                    'transmissionType' => 'Automatic',
                    'fuelType' => [
                        'code' => FuelTypeCode::PETROL,
                        'name' => 'Petrol',
                    ],
                    'firstRegistrationDate' => '2001-03-02',
                    'firstUsedDate' => '2001-03-02',
                    'manufactureDate' => '2001-03-02',
                    'isNewAtFirstReg' => false,
                    'weight' => null,
                    'weightSource' => [
                        'code' => 'U',
                        'name' => 'unladen',
                    ],
                ]
            )
        );

        $dvsaVehicle = new NewDvsaVehicle($dvsaVehicleData);

        return $dvsaVehicle;
    }

    /**
     * @param string      $vehicleClassCode
     * @param DvlaVehicle $dvlaVehicle
     */
    protected function vehicleServiceMockMethods($vehicleClassCode, $dvlaVehicle)
    {
        $this->returningOn($this->mockVehicleCatalog, VOF::countryOfRegistration(3), 'getCountryOfRegistrationByCode');

        $this->returningOn(
            $this->mockVehicleCatalog,
            VOF::vehicleClass((int) $vehicleClassCode, $vehicleClassCode),
            'getVehicleClassByCode'
        );

        $this->returningOn($this->mockDvlaVehicleRepository, $dvlaVehicle);
        $this->returningOn($this->mockVehicleCatalog, VOF::bodyType(), 'findBodyTypeByCode');

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
    }
}
