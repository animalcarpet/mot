<?php

namespace VehicleTest\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\CreateVehicle\Action\ReviewAction;
use Vehicle\CreateVehicle\Service\CreateNewVehicleService;
use Vehicle\CreateVehicle\Service\CreateVehicleModelService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class ReviewActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var MotAuthorisationServiceInterface */
    private $authorisationService;
    /** @var CreateVehicleStepService */
    private $createVehicleStepService;
    /** @var CreateVehicleModelService */
    private $createVehicleModelService;
    /** @var CreateNewVehicleService */
    private $createNewVehicleService;
    /** @var Request */
    private $request;

    public function setUp()
    {
        parent::setUp();

        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->createVehicleStepService = XMock::of(CreateVehicleStepService::class);
        $this->createVehicleModelService = XMock::of(CreateVehicleModelService::class);
        $this->createNewVehicleService = XMock::of(CreateNewVehicleService::class);
        $this->request = XMock::of(Request::class);
    }

    /** @test */
    public function executeAction_isAllowedOnStep_postRequest_expectRedirectToConfirmationScreen()
    {
        $this->mockIsAllowedOnStep(true);
        $this->mockGetStaticData();
        $this->mockGetModelData();
        $this->mockGetStepRegAndVin('TESTREG', 'TESTVIN');
        $this->mockGetStepMakeAndModel();
        $this->mockGetStepEngine('PE', '1400');
        $this->mockGetStepClass();
        $this->mockGetStepColours('B', 'W');
        $this->mockGetStepCountry('GB');
        $this->mockGetStepFirstUseDate('10', '01', '1999');
        $this->mockIsPost(true);
        $this->mockCreateVehicle();
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertSame('create-vehicle/new-vehicle-created-and-started', $actual->getRouteName());
    }

    /** @test */
    public function executeAction_fuelAndCylinderCapacityEntered_expectedToRemainOnStepWithCommaSeparatingValues()
    {
        $this->mockIsAllowedOnStep(true);
        $this->mockGetStaticData();
        $this->mockGetModelData();
        $this->mockGetStepRegAndVin('TESTREG', 'TESTVIN');
        $this->mockGetStepMakeAndModel();
        $this->mockGetStepEngine('PE', '1400');
        $this->mockGetStepClass();
        $this->mockGetStepColours('B', 'W');
        $this->mockGetStepCountry('GB');
        $this->mockGetStepFirstUseDate('10', '01', '1999');
        $this->mockIsPost(false);
        /** @var ViewActionResult $actual */
        $actual = $this->buildAction()->execute($this->request);
        /** @var ViewModel $viewModel */
        $viewModel = $actual->getViewModel();
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/review.twig', $actual->getTemplate());
        $this->assertSame('Petrol, 1400', $viewModel->getVariables()['fuelNameAndEngine']);
    }

    /** @test */
    public function executeAction_registrationNotEntered_expectedToRemainOnStepWithNotProvidedForRegistration()
    {
        $this->mockIsAllowedOnStep(true);
        $this->mockGetStaticData();
        $this->mockGetModelData();
        $this->mockGetStepRegAndVin('', 'TESTVIN');
        $this->mockGetStepMakeAndModel();
        $this->mockGetStepEngine('PE', '1400');
        $this->mockGetStepClass();
        $this->mockGetStepColours('B', 'W');
        $this->mockGetStepCountry('GB');
        $this->mockGetStepFirstUseDate('10', '01', '1999');
        $this->mockIsPost(false);
        /** @var ViewActionResult $actual */
        $actual = $this->buildAction()->execute($this->request);
        /** @var ViewModel $viewModel */
        $viewModel = $actual->getViewModel();
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/review.twig', $actual->getTemplate());
        $this->assertSame('Not provided', $viewModel->getVariables()['registrationNumber']);
    }

    /** @test */
    public function executeAction_dateEntered_expectedToRemainOnStepWithDateInCorrectFormat()
    {
        $this->mockIsAllowedOnStep(true);
        $this->mockGetStaticData();
        $this->mockGetModelData();
        $this->mockGetStepRegAndVin('', 'TESTVIN');
        $this->mockGetStepMakeAndModel();
        $this->mockGetStepEngine('PE', '1400');
        $this->mockGetStepClass();
        $this->mockGetStepColours('B', 'W');
        $this->mockGetStepCountry('GB');
        $this->mockGetStepFirstUseDate('10', '01', '1999');
        $this->mockIsPost(false);
        /** @var ViewActionResult $actual */
        $actual = $this->buildAction()->execute($this->request);
        /** @var ViewModel $viewModel */
        $viewModel = $actual->getViewModel();
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/review.twig', $actual->getTemplate());
        $this->assertSame('10 January 1999', $viewModel->getVariables()['date']);
    }

    /** @test */
    public function executeAction_notAllowedOnStep_expectedToBeRedirectToPreviousStep()
    {
        $this->mockIsAllowedOnStep(false);
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertSame('create-vehicle/new-vehicle-first-use-date', $actual->getRouteName());
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     * @expectedExceptionMessage Not allowed
     */
    public function testWhenDontHavePermission_ThenExceptionWillBeThrown()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_TEST_START)
            ->willThrowException(new UnauthorisedException('Not allowed'));

        $this->buildAction()->execute($this->request);
    }

    private function mockCreateVehicle()
    {
        $this->createNewVehicleService
            ->expects($this->once())
            ->method('createVehicle')
            ->willReturn([]);
    }

    private function mockIsPost($isPost)
    {
        $this->request
            ->expects($this->at(1))
            ->method('isPost')
            ->willReturn($isPost);
    }

    private function mockGetModelData()
    {
        $this->createVehicleModelService
            ->expects($this->once())
            ->method('getModelFromMakeInSession')
            ->willReturn($this->mockModelData());
    }

    private function mockGetStaticData()
    {
        $this->createVehicleStepService
            ->expects($this->any())
            ->method('getStaticData')
            ->willReturn($this->mockStaticData());
    }

    private function mockIsAllowedOnStep($isAllowed)
    {
        $this->createVehicleStepService
            ->expects($this->once())
            ->method('isAllowedOnStep')
            ->with(CreateVehicleStepService::REVIEW_STEP)
            ->willReturn($isAllowed);
    }

    private function mockGetStepRegAndVin($regInput, $vinInput)
    {
        $this->createVehicleStepService
            ->expects($this->at(1))
            ->method('getStep')
            ->with('reg-vin')
            ->willReturn([
                'reg-input' => $regInput,
                'vin-input' => $vinInput,
            ]);
    }

    private function mockGetStepMakeAndModel()
    {
        $this->createVehicleStepService
            ->expects($this->at(2))
            ->method('getStep')
            ->with('make')
            ->willReturn([
                'vehicleMake' => '100001',
            ]);

        $this->createVehicleStepService
            ->expects($this->at(4))
            ->method('getStep')
            ->with('model')
            ->willReturn([
                'vehicleModel' => '104096',
            ]);
    }

    private function mockGetStepEngine($fuelType, $cylinderCapacity)
    {
        $this->createVehicleStepService
            ->expects($this->at(5))
            ->method('getStep')
            ->with('engine')
            ->willReturn([
                'fuel-type' => $fuelType,
                'cylinder-capacity' => $cylinderCapacity,
            ]);
    }

    private function mockGetStepClass()
    {
        $this->createVehicleStepService
            ->expects($this->at(7))
            ->method('getStep')
            ->with('class')
            ->willReturn([
                'class' => '4',
            ]);
    }

    private function mockGetStepColours($primaryColour, $secondaryColours)
    {
        $this->createVehicleStepService
            ->expects($this->at(8))
            ->method('getStep')
            ->with('colour')
            ->willReturn([
                'primaryColour' => $primaryColour,
                'secondaryColours' => $secondaryColours,
            ]);

        $this->createVehicleStepService
            ->expects($this->at(10))
            ->method('getStep')
            ->with('colour')
            ->willReturn([
                'primaryColour' => $primaryColour,
                'secondaryColours' => $secondaryColours,
            ]);
    }

    private function mockGetStepCountry($countryOfRegistration)
    {
        $this->createVehicleStepService
            ->expects($this->at(12))
            ->method('getStep')
            ->with('country')
            ->willReturn([
                'countryOfRegistration' => $countryOfRegistration,
            ]);
    }

    private function mockGetStepFirstUseDate($day, $month, $year)
    {
        $this->createVehicleStepService
            ->expects($this->at(14))
            ->method('getStep')
            ->with('date')
            ->willReturn([
                'dateDay' => $day,
                'dateMonth' => $month,
                'dateYear' => $year,
            ]);
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

    private function buildAction()
    {
        return new ReviewAction(
            $this->authorisationService,
            $this->createVehicleStepService,
            $this->createVehicleModelService,
            $this->createNewVehicleService
        );
    }
}
