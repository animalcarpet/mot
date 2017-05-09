<?php

namespace Application\test\DvsaMotTestTest\Action;

use Core\Action\ActionResult;
use Core\Action\RedirectToRoute;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Action\DuplicateCertificateSearchByRegistrationAction;
use DvsaMotTest\Flash\VehicleCertificateSearchFlashMessage;
use DvsaMotTest\Form\VehicleSearch\DuplicateCertificateRegistrationSearchForm;
use DvsaMotTest\ViewModel\VehicleSearch\DuplicateCertificateSearchViewModel;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

class DuplicateCertificateSearchByRegistrationActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DuplicateCertificateSearchByRegistrationAction
     */
    private $action;
    /** @var FlashMessenger|\PHPUnit_Framework_MockObject_MockObject */
    private $flashMessengerMock;

    public function setUp()
    {
        $this->flashMessengerMock = XMock::of(FlashMessenger::class);
        $this->action = new DuplicateCertificateSearchByRegistrationAction($this->flashMessengerMock);
    }

    public function testHappyPath()
    {
        $vrm = 'FNZ6100';
        $data = [
            'vrm' => $vrm,
            DuplicateCertificateRegistrationSearchForm::FIELD_SUBMIT => 'whatever',
        ];

        $result = $this->action->execute($data);

        $this->assertInstanceOf(RedirectToRoute::class, $result);
        $this->assertEquals('vehicle-certificates', $result->getRouteName());
        $this->assertContains($vrm, $result->getQueryParams());
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
            'vrm' => '',
            DuplicateCertificateRegistrationSearchForm::FIELD_SUBMIT => 'whatever',
        ];

        $result = $this->action->execute($data);
        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertEquals(DuplicateCertificateSearchByRegistrationAction::TEMPLATE_NAME, $result->getTemplate());
        /** @var DuplicateCertificateSearchViewModel $viewModel */
        $viewModel = $result->getViewModel();
        $this->assertInstanceOf(DuplicateCertificateRegistrationSearchForm::class, $viewModel->getForm());
        $this->assertEquals(DuplicateCertificateSearchByRegistrationAction::PAGE_TITLE, $result->layout()->getPageTitle());
        $this->assertEquals(false, $viewModel->getForm()->isValid());
        $this->assertNotEmpty($viewModel->getForm()->getMessages());
    }

    public function testFlashMessageShowsWhenNoResultsAreReturned()
    {
        $data = [];
        $this->flashMessengerMock->expects($this->once())->method('getMessages')->willReturn([
            VehicleCertificateSearchFlashMessage::NOT_FOUND,
        ]);

        $result = $this->action->execute($data);
        /** @var DuplicateCertificateSearchViewModel $viewModel */
        $viewModel = $result->getViewModel();
        $this->assertEquals(true, $viewModel->getShowNoResultsMessage());
    }
}
