<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardOrder\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use DvsaCommonTest\TestUtils\XMock;

class OrderSecurityCardStepServiceTest extends \PHPUnit_Framework_TestCase
{
    const GUID = 101010101;
    /**
     * @var OrderSecurityCardStepService
     */
    private $orderSecurityCardStepService;

    /**
     * @var OrderNewSecurityCardSessionService
     */
    private $orderNewSecurityCardSessionService;

    public function setUp()
    {
        $this->orderNewSecurityCardSessionService = XMock::of(OrderNewSecurityCardSessionService::class);

        $this->orderSecurityCardStepService = new OrderSecurityCardStepService(
            $this->orderNewSecurityCardSessionService
        );
    }

    public function testGetStepsReturnsCorrectSteps()
    {
        $expected = [
            OrderSecurityCardStepService::NEW_STEP,
            OrderSecurityCardStepService::ADDRESS_STEP,
            OrderSecurityCardStepService::REVIEW_STEP,
        ];

        $actual = $this->orderSecurityCardStepService->getSteps();
        $this->assertSame($expected, $actual);
    }

    public function testAllowedOnStepWithValidPreviousStep()
    {
        $steps = [
            OrderNewSecurityCardSessionService::STEP_SESSION_STORE => [
                OrderSecurityCardStepService::ADDRESS_STEP => true,
                OrderSecurityCardStepService::REVIEW_STEP => false,
            ],
        ];

        $this->orderNewSecurityCardSessionService
            ->expects($this->once())
            ->method('loadByGuid')
            ->with(self::GUID)
            ->willReturn($steps);

        $actual = $this->orderSecurityCardStepService->isAllowedOnStep(
            self::GUID,
            OrderSecurityCardStepService::REVIEW_STEP
        );

        $this->assertTrue($actual);
    }

    public function testNotAllowedOnStepWithInvalidPreviousStep()
    {
        $steps = [
            OrderNewSecurityCardSessionService::STEP_SESSION_STORE => [
                OrderSecurityCardStepService::ADDRESS_STEP => false,
                OrderSecurityCardStepService::REVIEW_STEP => false,
            ],
        ];

        $this->orderNewSecurityCardSessionService
            ->expects($this->once())
            ->method('loadByGuid')
            ->with(self::GUID)
            ->willReturn($steps);

        $actual = $this->orderSecurityCardStepService->isAllowedOnStep(
            self::GUID,
            OrderSecurityCardStepService::REVIEW_STEP
        );

        $this->assertFalse($actual);
    }

    public function testNotAllowedOnStepWithoutSessionData()
    {
        $this->orderNewSecurityCardSessionService
            ->expects($this->once())
            ->method('loadByGuid')
            ->with(self::GUID)
            ->willReturn(null);

        $actual = $this->orderSecurityCardStepService->isAllowedOnStep(
            self::GUID,
            OrderSecurityCardStepService::REVIEW_STEP
        );

        $this->assertFalse($actual);
    }

    public function testNotAllowedOnStepIfStepDoesNotExistInSession()
    {
        $steps = [
            OrderNewSecurityCardSessionService::STEP_SESSION_STORE => [
                OrderSecurityCardStepService::ADDRESS_STEP => false,
            ],
        ];

        $this->orderNewSecurityCardSessionService
            ->expects($this->once())
            ->method('loadByGuid')
            ->with(self::GUID)
            ->willReturn($steps);

        $actual = $this->orderSecurityCardStepService->isAllowedOnStep(
            self::GUID,
            OrderSecurityCardStepService::REVIEW_STEP
        );

        $this->assertFalse($actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Steps are not stored in session
     */
    public function testUpdateStepStatusNoStepDataInSession()
    {
        $this->orderNewSecurityCardSessionService
            ->expects($this->once())
            ->method('loadByGuid')
            ->with(self::GUID)
            ->willReturn([]);

        $this->orderSecurityCardStepService->updateStepStatus(self::GUID, OrderSecurityCardStepService::REVIEW_STEP, true);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Step: test-step is not a valid step
     */
    public function testUpdateStepStatusStepNameIsNotValid()
    {
        $steps = [
            OrderNewSecurityCardSessionService::STEP_SESSION_STORE => [
                OrderSecurityCardStepService::ADDRESS_STEP => false,
            ],
        ];

        $this->orderNewSecurityCardSessionService
            ->expects($this->once())
            ->method('loadByGuid')
            ->with(self::GUID)
            ->willReturn($steps);

        $this->orderSecurityCardStepService->updateStepStatus(self::GUID, 'test-step', true);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Step status must be a boolean
     */
    public function testUpdateStepStatusStepStatusIsNotABoolean()
    {
        $steps = [
            OrderNewSecurityCardSessionService::STEP_SESSION_STORE => [
                OrderSecurityCardStepService::ADDRESS_STEP => false,
                OrderSecurityCardStepService::REVIEW_STEP => false,
            ],
        ];

        $this->orderNewSecurityCardSessionService
            ->expects($this->once())
            ->method('loadByGuid')
            ->with(self::GUID)
            ->willReturn($steps);

        $this->orderSecurityCardStepService->updateStepStatus(self::GUID, OrderSecurityCardStepService::ADDRESS_STEP, 'true');
    }

    public function testUpdateStepStatusUpdatesSucessfully()
    {
        $steps = [
            OrderNewSecurityCardSessionService::STEP_SESSION_STORE => [
                OrderSecurityCardStepService::ADDRESS_STEP => false,
                OrderSecurityCardStepService::REVIEW_STEP => false,
            ],
        ];

        $stepActual = [
            OrderNewSecurityCardSessionService::STEP_SESSION_STORE => [
                OrderSecurityCardStepService::ADDRESS_STEP => true,
                OrderSecurityCardStepService::REVIEW_STEP => false,
            ],
        ];

        $this->orderNewSecurityCardSessionService
            ->expects($this->once())
            ->method('loadByGuid')
            ->with(self::GUID)
            ->willReturn($steps);

        $this->orderNewSecurityCardSessionService
            ->expects($this->once())
            ->method('saveToGuid')
            ->with(self::GUID, $stepActual);

        $this->orderSecurityCardStepService->updateStepStatus(self::GUID, OrderSecurityCardStepService::ADDRESS_STEP, true);
    }
}
