<?php

namespace VehicleTest\CreateVehicle\Service;

use Application\Service\ContingencySessionManager;
use ClassesWithParents\F;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\ApiClient\Request\CreateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommonTest\Builder\DvsaVehicleBuilder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Service\AuthorisedClassesService;
use Vehicle\CreateVehicle\Service\CreateNewVehicleService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;

class CreateNewVehicleServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var CreateVehicleStepService */
    private $createVehicleStepService;
    /** @var VehicleService */
    private $vehicleService;
    /** @var MotFrontendIdentityProviderInterface */
    private $identityProvider;
    /** @var ContingencySessionManager */
    private $contingencySessionManager;
    /** @var Client */
    private $client;
    /** @var AuthorisedClassesService */
    private $authorisedClassesService;

    public function setUp()
    {
        parent::setUp();

        $this->createVehicleStepService = XMock::of(CreateVehicleStepService::class);
        $this->vehicleService = XMock::of(VehicleService::class);
        $this->identityProvider = XMock::of(MotFrontendIdentityProviderInterface::class);
        $this->contingencySessionManager = XMock::of(ContingencySessionManager::class);
        $this->client = XMock::of(Client::class);
        $this->authorisedClassesService = XMock::of(AuthorisedClassesService::class);
    }

    public function testAuthorisedClassesForUserAndVTS_emptyContainer_shouldGetClassesFromApi()
    {
        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn((new Identity())
                ->setUserId(1)
                ->setCurrentVts((new VehicleTestingStation())->setVtsId(1))
            );

        $this->authorisedClassesService
            ->expects($this->once())
            ->method('getCombinedAuthorisedClassesForPersonAndVts')
            ->with(1, 1)
            ->willReturn($this->mockAuthorisedClassesForPersonAndVts());

        $this->buildService()->getAuthorisedClassesForUserAndVTS();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage VTS not found
     */
    public function testAuthorisedClassesForUserAndVTS_emptyContainerEmptyVts_shouldThrowException()
    {
        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn((new Identity())
                ->setUserId(1)
            );

        $this->authorisedClassesService
            ->expects($this->never())
            ->method('getCombinedAuthorisedClassesForPersonAndVts')
            ->with(1, 1)
            ->willReturn($this->mockAuthorisedClassesForPersonAndVts());

        $this->buildService()->getAuthorisedClassesForUserAndVTS();
    }

    public function testCreateVehicle_shouldReturnVehicleAndMotTestNumber()
    {
        $this->createVehicleStepService
            ->expects($this->at(0))
            ->method('getStep')
            ->with('reg-vin')
            ->willReturn([
                'reg-input' => 'TESTREG',
                'vin-input' => 'TESTVIN',
                'leavingVINBlank' => 0,
                'leavingRegBlank' => 0,
            ]);

        $this->createVehicleStepService
            ->expects($this->at(1))
            ->method('getStep')
            ->with('make')
            ->willReturn([
                'vehicleMake' => '100001',
            ]);

        $this->createVehicleStepService
            ->expects($this->at(2))
            ->method('getStep')
            ->with('model')
            ->willReturn([
                'vehicleModel' => '104096',
            ]);

        $this->createVehicleStepService
            ->expects($this->at(3))
            ->method('getStep')
            ->with('engine')
            ->willReturn([
                'fuel-type' => 'PE',
                'cylinder-capacity' => '1200',
            ]);

        $this->createVehicleStepService
            ->expects($this->at(4))
            ->method('getStep')
            ->with('class')
            ->willReturn([
                'class' => '3',
            ]);

        $this->createVehicleStepService
            ->expects($this->at(5))
            ->method('getStep')
            ->with('country')
            ->willReturn([
                'countryOfRegistration' => 'GB',
            ]);

        $this->createVehicleStepService
            ->expects($this->at(6))
            ->method('getStep')
            ->with('colour')
            ->willReturn([
                'primaryColour' => 'S',
                'secondaryColours' => 'W',
            ]);

        $this->createVehicleStepService
            ->expects($this->at(7))
            ->method('getStep')
            ->with('date')
            ->willReturn([
                'dateDay' => '10',
                'dateMonth' => '10',
                'dateYear' => '2010',
            ]);

        $this->createVehicleStepService
            ->expects($this->at(9))
            ->method('getStep')
            ->with('country')
            ->willReturn([
                'countryOfRegistration' => 'GB',
            ]);

        $this->createVehicleStepService
            ->expects($this->once())
            ->method('getStaticData')
            ->willReturn($this->mockStaticData());

        $request = new CreateDvsaVehicleRequest();
        $request
            ->setRegistration('TESTREG')
            ->setVin('TESTVIN')
            ->setMakeId('100001')
            ->setModelId('104096')
            ->setFuelTypeCode('PE')
            ->setCylinderCapacity(1200)
            ->setColourCode('S')
            ->setVehicleClassCode('3')
            ->setSecondaryColourCode('W')
            ->setCountryOfRegistrationId(1)
            ->setFirstUsedDate(new \DateTime('2010-10-10'));

        $dvsaVehicleBuilder = new DvsaVehicleBuilder();
        $data = $dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $vehicleClassData = new \stdClass();
        $vehicleClassData->code = VehicleClassCode::CLASS_3;
        $vehicleClassData->name = VehicleClassCode::CLASS_3;
        $data->id = 1;
        $data->vehicleClass = $vehicleClassData;
        $data->emptyVrmReason = 3;
        $colour = new \stdClass();
        $colour->code = ColourCode::BEIGE;
        $data->colour = $colour;
        $secondaryColour = new \stdClass();
        $secondaryColour->code = ColourCode::NOT_STATED;
        $data->colourSecondary = $secondaryColour;
        $fuelType = new \stdClass();
        $fuelType->code = FuelTypeCode::PETROL;
        $data->fuelType = $fuelType;
        $cylinderCapacity = new \stdClass();
        $cylinderCapacity->code = 1200;
        $data->cylinderCapacity = $cylinderCapacity;
        $data->countryOfRegistrationId = 1;

        $vehicle = new DvsaVehicle($data);

        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn((new Identity())
                ->setUserId(1)
                ->setCurrentVts((new VehicleTestingStation())->setVtsId(1))
            );

        $this->vehicleService
            ->expects($this->once())
            ->method('createDvsaVehicle')
            ->with($request)
            ->willReturn($vehicle);

        $this->buildService()->createVehicle();
    }

    private function mockStaticData()
    {
        return [
            'make' => [
                0 => [
                    'id' => 100001,
                    'code' => '187FA',
                    'name' => 'ABARTH',
                ],
            ],
            'colour' => [
                'S' => 'Beige',
                'P' => 'Black',
                'B' => 'Bronze',
                'A' => 'Brown',
                'V' => 'Cream',
                'G' => 'Gold',
                'H' => 'Green',
                'L' => 'Grey',
                'T' => 'Maroon',
                'K' => 'Purple',
                'E' => 'Orange',
                'D' => 'Pink',
                'C' => 'Red',
                'M' => 'Silver',
                'U' => 'Turquoise',
                'N' => 'White',
                'F' => 'Yellow',
                'R' => 'Multi-colour',
                'W' => 'Not Stated',
                'J' => 'Blue',
            ],
            'fuelType' => [
                'PE' => 'Petrol',
                'DI' => 'Diesel',
                'EL' => 'Electric',
                'ST' => 'Steam',
                'LP' => 'LPG',
                'CN' => 'CNG',
                'LN' => 'LNG',
                'FC' => 'Fuel Cells',
                'OT' => 'Other',
                'GA' => 'Gas',
                'GB' => 'Gas Bi-Fuel',
                'HY' => 'Hybrid Electric (Clean)',
                'GD' => 'Gas Diesel',
                'ED' => 'Electric Diesel',
            ],
            'country' => [
                0 => [
                    'id' => 1,
                    'code' => 'GB',
                    'name' => 'GB, UK, ENG, CYM, SCO (UK) - Great Britain',
                ],
                1 => [
                    'id' => 2,
                    'code' => 'NI',
                    'name' => 'GB, NI (UK) - Northern Ireland',
                ],
                2 => [
                    'id' => 3,
                    'code' => 'GBA',
                    'name' => 'GBA (GG) - Alderney',
                ],
            ],
            'vehicleClass' => [
                0 => [
                    'id' => 1,
                    'name' => '1',
                ],
                1 => [
                    'id' => 2,
                    'name' => '2',
                ],
                2 => [
                    'id' => 3,
                    'name' => '3',
                ],
                3 => [
                    'id' => 4,
                    'name' => '4',
                ],
                4 => [
                    'id' => 5,
                    'name' => '5',
                ],
                5 => [
                    'id' => 7,
                    'name' => '7',
                ],
            ],
        ];
    }

    private function mockModelData()
    {
        return [
            0 => [
                'id' => 104096,
                'code' => '01315',
                'name' => '500',
            ],
            1 => [
                'id' => 104097,
                'code' => '01316',
                'name' => '595',
            ],
            2 => [
                'id' => 104098,
                'code' => '01317',
                'name' => '595C',
            ],
            3 => [
                'id' => 107691,
                'code' => '10001',
                'name' => '695',
            ],
            4 => [
                'id' => 104099,
                'code' => '01318',
                'name' => 'GRAND PUNTO',
            ],
            5 => [
                'id' => 104100,
                'code' => '01319',
                'name' => 'PUNTO',
            ],
        ];
    }

    private function mockAuthorisedClassesForPersonAndVts()
    {
        return [
            'forPerson' => [
                0 => '1',
                1 => '2',
                2 => '3',
                3 => '4',
                4 => '5',
                5 => '7',
            ],
            'forVts' => [
                0 => '1',
                1 => '2',
                2 => '3',
                3 => '4',
                4 => '5',
                5 => '7',
            ],
        ];
    }

    private function buildService()
    {
        return new CreateNewVehicleService(
            $this->vehicleService,
            $this->createVehicleStepService,
            $this->identityProvider,
            $this->contingencySessionManager,
            $this->client,
            $this->authorisedClassesService
        );
    }
}
