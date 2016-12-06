<?php

namespace VehicleTest\CreateVehicle\Service;

use Application\Service\CatalogService;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\CreateVehicle\Service\CreateVehicleSessionService;
use DvsaCommon\HttpRestJson\Client;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;

class CreateVehicleStepServiceTest extends \PHPUnit_Framework_TestCase
{
    private $createVehicleSessionService;
    private $catalogService;
    private $client;

    public function setUp()
    {
        $this->createVehicleSessionService = XMock::of(CreateVehicleSessionService::class);
        $this->catalogService = XMock::of(CatalogService::class);
        $this->client = XMock::of(Client::class);
    }

    public function testGetStepsReturnsCorrectSteps()
    {
        $actual = $this->buildService()->getSteps();
        $expected = [
            CreateVehicleStepService::NEW_STEP,
            CreateVehicleStepService::REG_VIN_STEP,
            CreateVehicleStepService::MAKE_STEP,
            CreateVehicleStepService::MODEL_STEP,
            CreateVehicleStepService::ENGINE_STEP,
            CreateVehicleStepService::CLASS_STEP,
            CreateVehicleStepService::COLOUR_STEP,
            CreateVehicleStepService::COUNTRY_STEP,
            CreateVehicleStepService::DATE_STEP,
            CreateVehicleStepService::REVIEW_STEP,
            CreateVehicleStepService::CONFIRM_STEP,
        ];
        $this->assertSame($expected, $actual);
    }

    public function testStepsAreLoadedIntoSessionCorrectly()
    {
        $expectedSteps = [
            CreateVehicleStepService::NEW_STEP => true,
            CreateVehicleStepService::REG_VIN_STEP => false,
            CreateVehicleStepService::MAKE_STEP => false,
            CreateVehicleStepService::MODEL_STEP => false,
            CreateVehicleStepService::ENGINE_STEP => false,
            CreateVehicleStepService::CLASS_STEP => false,
            CreateVehicleStepService::COLOUR_STEP => false,
            CreateVehicleStepService::DATE_STEP => false,
            CreateVehicleStepService::COUNTRY_STEP => false,
            CreateVehicleStepService::REVIEW_STEP => false,
            CreateVehicleStepService::CONFIRM_STEP => false,
        ];

        $this->createVehicleSessionService
            ->expects($this->once())
            ->method('clear');

        $this->createVehicleSessionService
            ->expects($this->once())
            ->method('save')
            ->with(CreateVehicleSessionService::UNIQUE_KEY, [CreateVehicleSessionService::STEP_KEY => $expectedSteps]);

        $this->buildService()->loadStepsIntoSession();
    }

    public function testAllowedOnStepWithValidPreviousStep()
    {
        $steps = [
            'steps' => [
                CreateVehicleStepService::NEW_STEP => true,
                CreateVehicleStepService::REG_VIN_STEP => false,
                CreateVehicleStepService::MAKE_STEP => false,
                CreateVehicleStepService::MODEL_STEP => false,
                CreateVehicleStepService::ENGINE_STEP => false,
                CreateVehicleStepService::CLASS_STEP => false,
                CreateVehicleStepService::COLOUR_STEP => false,
                CreateVehicleStepService::DATE_STEP => false,
                CreateVehicleStepService::COUNTRY_STEP => false,
                CreateVehicleStepService::REVIEW_STEP => false,
            ]
        ];

        $this->createVehicleSessionService
            ->expects($this->once())
            ->method("load")
            ->with(CreateVehicleSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $actual = $this->buildService()->isAllowedOnStep(CreateVehicleStepService::REG_VIN_STEP);

        $this->assertTrue($actual);
    }

    public function testNotAllowedOnStepWithInvalidPreviousStep()
    {
        $steps = [
            'steps' => [
                CreateVehicleStepService::NEW_STEP => true,
                CreateVehicleStepService::REG_VIN_STEP => false,
                CreateVehicleStepService::MAKE_STEP => false,
                CreateVehicleStepService::MODEL_STEP => false,
                CreateVehicleStepService::ENGINE_STEP => false,
                CreateVehicleStepService::CLASS_STEP => false,
                CreateVehicleStepService::COLOUR_STEP => false,
                CreateVehicleStepService::DATE_STEP => false,
                CreateVehicleStepService::COUNTRY_STEP => false,
                CreateVehicleStepService::REVIEW_STEP => false,
            ]
        ];

        $this->createVehicleSessionService
            ->expects($this->once())
            ->method("load")
            ->with(CreateVehicleSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $actual = $this->buildService()->isAllowedOnStep(CreateVehicleStepService::MAKE_STEP);

        $this->assertFalse($actual);
    }

    public function testNotAllowedOnStepWithoutSessionData()
    {
        $this->createVehicleSessionService
            ->expects($this->once())
            ->method("load")
            ->with(CreateVehicleSessionService::UNIQUE_KEY)
            ->willReturn(null);

        $actual = $this->buildService()->isAllowedOnStep(CreateVehicleStepService::MAKE_STEP);

        $this->assertFalse($actual);
    }

    public function testNotAllowedOnStepIfStepDoesNotExist()
    {
        $steps = [
            'steps' => [
                CreateVehicleStepService::NEW_STEP => true,
                CreateVehicleStepService::REG_VIN_STEP => false,
                CreateVehicleStepService::MAKE_STEP => false,
                CreateVehicleStepService::MODEL_STEP => false,
                CreateVehicleStepService::ENGINE_STEP => false,
                CreateVehicleStepService::CLASS_STEP => false,
                CreateVehicleStepService::COLOUR_STEP => false,
                CreateVehicleStepService::DATE_STEP => false,
                CreateVehicleStepService::COUNTRY_STEP => false,
                CreateVehicleStepService::REVIEW_STEP => false,
            ]
        ];

        $this->createVehicleSessionService
            ->expects($this->once())
            ->method("load")
            ->with(CreateVehicleSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $actual = $this->buildService()->isAllowedOnStep('invalid-step');

        $this->assertFalse($actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Steps are not stored in session
     */
    public function testUpdateStepStatusNoStepDataInSession()
    {
        $this->createVehicleSessionService
            ->expects($this->once())
            ->method('load')
            ->with(CreateVehicleSessionService::UNIQUE_KEY)
            ->willReturn([]);

        $this->buildService()->updateStepStatus('test-step', true);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Step: test-step is not a valid step
     */
    public function testUpdateStepStatusStepNameIsNotValid()
    {
        $steps = [
            'steps' => [
                CreateVehicleStepService::NEW_STEP => true,
                CreateVehicleStepService::REG_VIN_STEP => false,
                CreateVehicleStepService::MAKE_STEP => false,
                CreateVehicleStepService::MODEL_STEP => false,
                CreateVehicleStepService::ENGINE_STEP => false,
                CreateVehicleStepService::CLASS_STEP => false,
                CreateVehicleStepService::COLOUR_STEP => false,
                CreateVehicleStepService::DATE_STEP => false,
                CreateVehicleStepService::COUNTRY_STEP => false,
                CreateVehicleStepService::REVIEW_STEP => false,
            ]
        ];
        $this->createVehicleSessionService
            ->expects($this->once())
            ->method('load')
            ->with(CreateVehicleSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $this->buildService()->updateStepStatus('test-step', true);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Step status must be a boolean
     */
    public function testUpdateStepStatusStepStatusIsNotABoolean()
    {
        $steps = [
            'steps' => [
                CreateVehicleStepService::NEW_STEP => true,
                CreateVehicleStepService::REG_VIN_STEP => false,
                CreateVehicleStepService::MAKE_STEP => false,
                CreateVehicleStepService::MODEL_STEP => false,
                CreateVehicleStepService::ENGINE_STEP => false,
                CreateVehicleStepService::CLASS_STEP => false,
                CreateVehicleStepService::COLOUR_STEP => false,
                CreateVehicleStepService::DATE_STEP => false,
                CreateVehicleStepService::COUNTRY_STEP => false,
                CreateVehicleStepService::REVIEW_STEP => false,
            ]
        ];

        $this->createVehicleSessionService
            ->expects($this->once())
            ->method('load')
            ->with(CreateVehicleSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $this->buildService()->updateStepStatus(CreateVehicleStepService::NEW_STEP, 'true');
    }

    public function testUpdateStepStatusUpdatesSucessfully()
    {
        $stepName = 'intro';
        $stepStatus = true;

        $steps = [
            'steps' => [
                $stepName => false,
                'security-question-2' => false,
                'confirmation' => false,
            ]
        ];

        $stepResult = [
            'steps' => [
                $stepName => true,
                'security-question-2' => false,
                'confirmation' => false,
            ]
        ];

        $this->createVehicleSessionService
            ->expects($this->once())
            ->method('load')
            ->with(CreateVehicleSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $this->createVehicleSessionService
            ->expects($this->once())
            ->method('load')
            ->with(CreateVehicleSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $this->createVehicleSessionService
            ->expects($this->once())
            ->method('save')
            ->with(CreateVehicleSessionService::UNIQUE_KEY, $stepResult);

        $this->buildService()->updateStepStatus($stepName, $stepStatus);
    }

    public function testStaticDataAlreadyStoredInSessionApiNotCalled()
    {
        $expected = [
            'make' => 'ford'
        ];

        $this->createVehicleSessionService
            ->expects($this->once())
            ->method('load')
            ->with(CreateVehicleSessionService::UNIQUE_KEY)
            ->willReturn([CreateVehicleSessionService::API_DATA => $expected]);

        $this->catalogService
            ->expects($this->never())
            ->method('getData');

        $this->client
            ->expects($this->never())
            ->method('get');

        $actual = $this->buildService()->getStaticData();

        $this->assertSame($expected, $actual);
    }

    public function testUpdateStepDataWithValidStepAndStepIsSaved()
    {
        $expectedData = [
            'field1' => 'testData'
        ];

        $this->createVehicleSessionService
            ->expects($this->once())
            ->method('load')
            ->with(CreateVehicleSessionService::UNIQUE_KEY)
            ->willReturn([CreateVehicleSessionService::USER_DATA => []]);

        $this->createVehicleSessionService
            ->expects($this->once())
            ->method('save')
            ->with(CreateVehicleSessionService::UNIQUE_KEY,
                [CreateVehicleSessionService::USER_DATA => [CreateVehicleStepService::COLOUR_STEP => $expectedData]]);

        $this->buildService()->saveStep(CreateVehicleStepService::COLOUR_STEP, $expectedData);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Step not-a-real-step is not a valid step.
     */
    public function testUpdateStepDataWithInvalidStepAndNotSaved()
    {
        $this->createVehicleSessionService
            ->expects($this->never())
            ->method('load');

        $this->createVehicleSessionService
            ->expects($this->never())
            ->method('save');

        $this->buildService()->saveStep('not-a-real-step', []);
    }

    private function buildService()
    {
        return new CreateVehicleStepService(
            $this->createVehicleSessionService,
            $this->catalogService,
            $this->client
        );
    }
}