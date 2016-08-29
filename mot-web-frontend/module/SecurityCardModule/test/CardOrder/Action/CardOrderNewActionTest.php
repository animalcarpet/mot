<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardOrder\Action;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderNewAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderProtection;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\ViewModel\CardOrderNewViewModel;
use DvsaCommonTest\TestUtils\XMock;
use Core\Action\ActionResult;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use Core\Service\MotFrontendIdentityProvider;

class CardOrderNewActionTest extends \PHPUnit_Framework_TestCase
{
    const START_TEMPLATE = '2fa/card-order/start';
    const TEST_STEP_1 = 'testStep1';
    const TEST_STEP_2 = 'testStep2';
    const USER_ID = 999;

    /** @var OrderNewSecurityCardSessionService $sessionService */
    private $sessionService;

    /** @var OrderSecurityCardStepService $stepService */
    private $stepService;

    /** @var Request $request */
    private $request;

    /** @var MotFrontendIdentityProvider $identityProvider */
    private $identityProvider;

    /** @var Identity $identity */
    private $identity;

    /** @var CardOrderProtection $cardOrderProtection */
    private $cardOrderProtection;

    public function setUp()
    {
        parent::setUp();
        $this->sessionService = XMock::of(OrderNewSecurityCardSessionService::class);
        $this->stepService = XMock::of(OrderSecurityCardStepService::class);
        $this->identityProvider = XMock::of(MotFrontendIdentityProvider::class);
        $this->request = XMock::of(Request::class);
        $this->identity = XMock::of(Identity::class);
        $this->cardOrderProtection = XMock::of(CardOrderProtection::class);
    }

    public function testStepArrayCleared_AndSessionWithViewModelCorrectlySetUp() {
        $this->setSecondFactorRequiredMock(true);
        $this->setUpProtection();
        $this->stepService
            ->expects($this->any())
            ->method('getSteps')
            ->willReturn([self::TEST_STEP_1, self::TEST_STEP_2]);

        $this->sessionService
            ->expects($this->any())
            ->method('saveToGuid')
            ->with(self::USER_ID, $this->getExpectedTestSessionArray());

        $this->stepService
            ->expects($this->once())
            ->method('updateStepStatus')
            ->with(self::USER_ID,  OrderSecurityCardStepService::ADDRESS_STEP, true);

        /** @var ActionResult $actionResult */
        $actionResult = $this->buildAction()->execute($this->request, self::USER_ID);

        /** @var CardOrderNewViewModel $viewModel */
        $viewModel = $actionResult->getViewModel();

        $this->assertEquals(self::START_TEMPLATE, $actionResult->getTemplate());
        $this->assertEquals(self::USER_ID, $viewModel->getUserId());
        $this->assertTrue($viewModel->getHasAnActiveCard());
    }

    private function getExpectedTestSessionArray() {
        return [
            'userId' => self::USER_ID,
            'steps' => ['new' => true, self::TEST_STEP_1 => false, self::TEST_STEP_2 => false],
            'hasOrdered' => false
        ];
    }

    private function buildAction()
    {
        $action = new CardOrderNewAction(
            $this->sessionService,
            $this->stepService,
            $this->identityProvider,
            $this->cardOrderProtection
        );
        return $action;
    }

    private function setSecondFactorRequiredMock($secondFactorRequired) {
        $this->identity
            ->expects($this->any())
            ->method('isSecondFactorRequired')
            ->willReturn($secondFactorRequired);

        $this->identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($this->identity);
    }

    private function setUpProtection()
    {
        $this->cardOrderProtection
            ->expects($this->once())
            ->method('checkAuthorisation')
            ->willReturn(null);
    }
}
