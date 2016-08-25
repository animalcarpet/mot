<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardOrder\Action;

use Core\Action\ActionResult;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderAddressAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderProtection;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardAddressService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\ViewModel\CardOrderAddressViewModel;
use DvsaCommonTest\TestUtils\XMock;
use Core\Action\RedirectToRoute;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Form\SecurityCardAddressForm;
use Zend\Http\Request as HttpRequest;
use Zend\Stdlib\ParametersInterface;

class CardOrderAddressActionTest extends \PHPUnit_Framework_TestCase
{
    const USER_ID = 999;

    /** @var OrderSecurityCardAddressService $orderSecurityCardAddressService */
    private $orderSecurityCardAddressService;

    /** @var OrderNewSecurityCardSessionService $sessionService */
    private $sessionService;

    /** @var OrderSecurityCardStepService $stepService */
    private $stepService;

    /** @var CardOrderProtection $cardOrderProtection */
    private $cardOrderProtection;

    /** @var Request $request */
    private $request;

    public function setUp()
    {
        parent::setUp();
        $this->orderSecurityCardAddressService = XMock::of(OrderSecurityCardAddressService::class);
        $this->sessionService = XMock::of(OrderNewSecurityCardSessionService::class);
        $this->stepService = XMock::of(OrderSecurityCardStepService::class);
        $this->cardOrderProtection = XMock::of(CardOrderProtection::class);
        $this->request = XMock::of(Request::class);
    }

    public function testReturnsToNewPage_WhenUserNotAllowedOnStep() {
        $this->setUpProtection();
        $this->mockIsAllowedOnStep(false);

        /** @var RedirectToRoute $actionResult */
        $actionResult = $this->buildAction()->execute($this->request, self::USER_ID);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertEquals('security-card-order/new', $actionResult->getRouteName());
        $this->assertEquals(self::USER_ID, $actionResult->getRouteParams()['userId']);
    }

    public function testReturnsPopulatedAddressForm_WhenNotAPostRequest() {
        $this->setUpProtection();
        $this->mockIsAllowedOnStep(true);
        $this->mockGetSecurityCardOrderAddresses([]);

        $addressData = [SecurityCardAddressForm::ADDRESS_RADIOS => SecurityCardAddressForm::CUSTOM_ADDRESS_VALUE];
        $this->sessionService
            ->expects($this->once())
            ->method('loadByGuid')
            ->with(self::USER_ID)
            ->willReturn([OrderNewSecurityCardSessionService::ADDRESS_STEP_STORE => $addressData]);

        $this->mockIsPost(false, $this->getFakeDetailsArray());

        /** @var ActionResult $actionResult */
        $actionResult = $this->buildAction()->execute($this->request, self::USER_ID);

        /** @var CardOrderAddressViewModel $viewModel */
        $viewModel = $actionResult->getViewModel();

        $this->assertInstanceOf(ActionResult::class, $actionResult);
        $this->assertInstanceOf(CardOrderAddressViewModel::class, $viewModel);
        $this->assertEquals('2fa/card-order/address', $actionResult->getTemplate());
        $this->assertEquals(self::USER_ID, $viewModel->getUserId());
        $this->assertInstanceOf(SecurityCardAddressForm::class, $viewModel->getForm());
        $this->assertEquals(CardOrderAddressAction::ADDRESS_PAGE_TITLE, $actionResult->layout()->getPageTitle());
        $this->assertEquals(CardOrderAddressAction::ADDRESS_PAGE_SUBTITLE, $actionResult->layout()->getPageSubTitle());
    }

    public function testPostWithAnInvalidForm_RedirectsToAddress() {
        $this->setUpProtection();
        $this->mockIsAllowedOnStep(true);
        $this->mockGetSecurityCardOrderAddresses([]);
        $this->mockIsPost(true, $this->getFakeDetailsArray());

        /** @var ActionResult $actionResult */
        $actionResult = $this->buildAction()->execute($this->request, self::USER_ID);

        /** @var CardOrderAddressViewModel $viewModel */
        $viewModel = $actionResult->getViewModel();

        $this->assertInstanceOf(ActionResult::class, $actionResult);
        $this->assertInstanceOf(CardOrderAddressViewModel::class, $viewModel);
        $this->assertEquals('2fa/card-order/address', $actionResult->getTemplate());
        $this->assertEquals(self::USER_ID, $viewModel->getUserId());
        $this->assertEquals(CardOrderAddressAction::ADDRESS_PAGE_TITLE, $actionResult->layout()->getPageTitle());
        $this->assertEquals(CardOrderAddressAction::ADDRESS_PAGE_SUBTITLE, $actionResult->layout()->getPageSubTitle());
    }

    public function testPostWithAValidForm_AndNonCustomAddress_SavesPostDataToSession() {
        $this->setUpProtection();
        $this->mockIsAllowedOnStep(true);
        $this->mockGetSecurityCardOrderAddresses([]);
        $this->mockIsPost(true, $this->getValidAddressData('addressChoiceCustom'));

        $this->sessionService
            ->expects($this->once())
            ->method('saveToGuid');

        /** @var RedirectToRoute $actionResult */
        $actionResult = $this->buildAction()->execute($this->request, self::USER_ID);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertEquals('security-card-order/review', $actionResult->getRouteName());
    }

    private function mockIsPost($isPost, $postData) {
        if ($isPost) {
            $params = XMock::of(ParametersInterface::class);
            $params->expects($this->once())
                ->method('toArray')
                ->willReturn($postData);

            $this->request->expects($this->once())->method('isPost')->willReturn($isPost);
            $this->request->expects($this->once())->method('getPost')->willReturn($params);
        } else {
            $this->request->expects($this->once())->method('isPost')->willReturn($isPost);
        }
    }

    private function buildAction()
    {
        $action = new CardOrderAddressAction(
            $this->orderSecurityCardAddressService,
            $this->sessionService,
            $this->stepService,
            $this->cardOrderProtection
        );
        return $action;
    }

    private function setUpProtection()
    {
        $this->cardOrderProtection
            ->expects($this->once())
            ->method('checkAuthorisation')
            ->willReturn(null);
    }

    private function mockIsAllowedOnStep($allowed) {
        $this->stepService
            ->expects($this->once())
            ->method('isAllowedOnStep')
            ->with(self::USER_ID, OrderSecurityCardStepService::ADDRESS_STEP)
            ->willReturn($allowed);
    }

    private function getFakeDetailsArray()
    {
        return [
            'some_test'     => "some_test_value",
        ];
    }

    private function mockGetSecurityCardOrderAddresses($return) {
        $this->orderSecurityCardAddressService
            ->expects($this->once())
            ->method('getSecurityCardOrderAddresses')
            ->with(self::USER_ID)
            ->willReturn($return);
    }

    private function getValidAddressData($addressChoiceCustom)
    {
        $data = [
            'address1' => '73 southwell avenue',
            'address2' => 'address 2',
            'address3' => 'address 3',
            'townOrCity' => 'Northolt',
            'postcode' => 'ng1 6lp',
            'addressChoice' => $addressChoiceCustom
        ];

        return $data;
    }

}