<?php

namespace Application\test\DvsaMotTestTest\Action;

use Core\Action\ActionResult;
use Core\Action\RedirectToRoute;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Action\DuplicateCertificateSearchByVinAction;
use DvsaMotTest\Flash\VehicleCertificateSearchFlashMessage;
use DvsaMotTest\Flash\VehicleCertificateSearchMessage;
use DvsaMotTest\Form\VehicleSearch\DuplicateCertificateVinSearchForm;
use DvsaMotTest\ViewModel\VehicleSearch\DuplicateCertificateSearchViewModel;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

class DuplicateCertificateSearchByVinActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DuplicateCertificateSearchByVinAction
     */
    private $action;
    /** @var  FlashMessenger|\PHPUnit_Framework_MockObject_MockObject */
    private $flashMessengerMock;

    public function setUp()
    {
        $this->flashMessengerMock = XMock::of(FlashMessenger::class);
        $this->action = new DuplicateCertificateSearchByVinAction($this->flashMessengerMock);
    }

    public function testHappyPath()
    {
        $vin = '1M5GDM9AXKP042755';
        $data = [
            'vin' => $vin,
            DuplicateCertificateVinSearchForm::FIELD_SUBMIT => 'whatever',
        ];

        $result = $this->action->execute($data);

        $this->assertInstanceOf(RedirectToRoute::class, $result);
        $this->assertEquals('vehicle-certificates', $result->getRouteName());
        $this->assertContains($vin, $result->getQueryParams());
    }

    public function testFormDoesNotShowAnyMessagesWhenUserVisitsPageWithoutQueryParams()
    {
        $result = $this->action->execute([]);
        $this->assertInstanceOf(ActionResult::class, $result);
        /** @var DuplicateCertificateSearchViewModel $viewModel */
        $viewModel = $result->getViewModel();
        $this->assertEquals([], $viewModel->getForm()->getMessages());
    }

    public function testValidationError()
    {
        $data = [
            'vin' => '',
            DuplicateCertificateVinSearchForm::FIELD_SUBMIT => 'whatever',
        ];

        $result = $this->action->execute($data);
        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertEquals(DuplicateCertificateSearchByVinAction::TEMPLATE_NAME, $result->getTemplate());
        /** @var DuplicateCertificateSearchViewModel $viewModel */
        $viewModel = $result->getViewModel();
        $this->assertInstanceOf(DuplicateCertificateVinSearchForm::class, $viewModel->getForm());
        $this->assertEquals(DuplicateCertificateSearchByVinAction::PAGE_TITLE, $result->layout()->getPageTitle());
        $this->assertEquals(false, $viewModel->getForm()->isValid());
        $this->assertNotEmpty($viewModel->getForm()->getMessages());
    }

    public function testFlashMessageShowsWhenNoResultsAreReturned()
    {
        $data = [];
        $this->flashMessengerMock->expects($this->once())->method('getMessages')->willReturn([
            VehicleCertificateSearchFlashMessage::NOT_FOUND
        ]);

        $result = $this->action->execute($data);
        /** @var DuplicateCertificateSearchViewModel $viewModel */
        $viewModel = $result->getViewModel();
        $this->assertEquals(true, $viewModel->getShowNoResultsMessage());
    }
}