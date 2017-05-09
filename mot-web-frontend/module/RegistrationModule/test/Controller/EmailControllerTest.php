<?php

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Controller;

use Core\Service\StepService;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\EmailController;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\DetailsStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\EmailStep;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\EmailInputFilter;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Service\IsEmailDuplicateService;
use Zend\Http\Request;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Model\ViewModel;
use PHPUnit_Framework_MockObject_MockObject;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\HelperPluginManager;
use Zend\View\Helper\HeadTitle;

class EmailControllerTest extends \PHPUnit_Framework_TestCase
{
    private $emailDuplicateService;

    private $step;

    private $previousStep;

    public function setUp()
    {
        $this->emailDuplicateService = XMock::of(IsEmailDuplicateService::class);

        $this->step = $this->getMockBuilder(EmailStep::class)
        ->setConstructorArgs([
            XMock::of(RegistrationSessionService::class),
            XMock::of(EmailInputFilter::class),
        ])->getMock();

        $this->previousStep = $this->getMockBuilder(DetailsStep::class)
            ->setConstructorArgs([
                XMock::of(RegistrationSessionService::class),
                XMock::of(DetailsInputFilter::class),
            ])->getMock();
    }

    public function testGetRequest()
    {
        $this->previousStep->expects($this->once())->method('load')->willReturn($this->previousStep);
        $this->step->expects($this->once())->method('load');
        $this->step->expects($this->once())->method('getProgress');
        $this->step->expects($this->once())->method('toViewArray');

        $stepService = XMock::of(StepService::class);
        $stepService->expects($this->once())->method('setActiveById')->willReturnSelf();
        $stepService->expects($this->once())->method('current')->willReturn($this->step);
        $stepService->expects($this->once())->method('previous')->willReturn($this->previousStep);

        // mock the getRequest is post call
        $request = XMock::of(Request::class);
        $request->expects($this->once())->method('isPost')->willReturn(false);

        $controller = $this->getMockBuilder(EmailController::class)
            ->setConstructorArgs([
                $stepService,
                $this->emailDuplicateService,
            ])
            ->setMethods(['getRequest'])
            ->getMockForAbstractClass();

        $controller->expects($this->once())->method('getRequest')->willReturn($request);

        $serviceLocator = $this->getServiceLocatorMock();
        $controller->setServiceLocator($serviceLocator);

        $result = $controller->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
    }

    public function testPostWithInvalidValues()
    {
        $this->step->expects($this->once())->method('load');
        $this->step->expects($this->once())->method('readFromArray');
        $this->step->expects($this->once())->method('save');
        $this->step->expects($this->once())->method('validate')->willReturn(false);
        $this->step->expects($this->once())->method('getProgress');
        $this->step->expects($this->once())->method('toViewArray');

        $stepService = XMock::of(StepService::class);
        $stepService->expects($this->once())->method('setActiveById')->willReturnSelf();
        $stepService->expects($this->once())->method('current')->willReturn($this->step);

        $params = XMock::of(ParametersInterface::class);
        $params->expects($this->once())->method('toArray')->willReturn($this->getFakeDetailsArray());

        $request = XMock::of(Request::class);
        $request->expects($this->once())->method('isPost')->willReturn(true);
        $request->expects($this->once())->method('getPost')->willReturn($params);

        $controller = $this->getMockBuilder(EmailController::class)
            ->setConstructorArgs([
                $stepService,
                $this->emailDuplicateService,
            ])
            ->setMethods(['getRequest'])
            ->getMockForAbstractClass();

        $controller->expects($this->exactly(2))->method('getRequest')->willReturn($request);

        $serviceLocator = $this->getServiceLocatorMock();
        $controller->setServiceLocator($serviceLocator);

        $result = $controller->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
    }

    public function testPostRequestValidValuesAndNonDuplicateGoesToNextStep()
    {
        $this->emailDuplicateService
            ->expects($this->once())
            ->method('isEmailDuplicate')
            ->willReturn(false);

        $this->step->expects($this->once())->method('load');
        $this->step->expects($this->once())->method('readFromArray');
        $this->step->expects($this->once())->method('save');
        $this->step->expects($this->once())->method('validate')->willReturn(true);
        $this->step->expects($this->never())->method('getProgress');
        $this->step->expects($this->never())->method('toViewArray');

        $stepService = XMock::of(StepService::class);
        $stepService->expects($this->once())->method('setActiveById')->willReturnSelf();
        $stepService->expects($this->once())->method('current')->willReturn($this->step);
        $stepService->expects($this->once())->method('next')->willReturn($this->step);

        $params = XMock::of(ParametersInterface::class);
        $params->expects($this->once())->method('toArray')->willReturn($this->getFakeDetailsArray());

        $request = XMock::of(Request::class);
        $request->expects($this->once())->method('isPost')->willReturn(true);
        $request->expects($this->once())->method('getPost')->willReturn($params);

        $redirect = XMock::of(Redirect::class);
        $redirect->expects($this->once())->method('toRoute');

        $controller = $this->getMockBuilder(EmailController::class)
            ->setConstructorArgs([
                $stepService,
                $this->emailDuplicateService,
            ])
            ->setMethods(['getRequest', 'redirect'])
            ->getMockForAbstractClass();

        $controller->expects($this->exactly(2))->method('getRequest')->willReturn($request);
        $controller->expects($this->once())->method('redirect')->willReturn($redirect);

        $serviceLocator = $this->getServiceLocatorMock();
        $controller->setServiceLocator($serviceLocator);

        $controller->indexAction();
    }

    public function testPostRequestValidValuesAndNonDuplicateGoesToDuplicateEmailPage()
    {
        $this->emailDuplicateService
            ->expects($this->once())
            ->method('isEmailDuplicate')
            ->willReturn(true);

        $this->step->expects($this->once())->method('load');
        $this->step->expects($this->once())->method('readFromArray');
        $this->step->expects($this->once())->method('save');
        $this->step->expects($this->once())->method('validate')->willReturn(true);

        $stepService = XMock::of(StepService::class);
        $stepService->expects($this->once())->method('setActiveById')->willReturnSelf();
        $stepService->expects($this->once())->method('current')->willReturn($this->step);

        $params = XMock::of(ParametersInterface::class);
        $params->expects($this->once())->method('toArray')->willReturn($this->getFakeDetailsArray());

        $request = XMock::of(Request::class);
        $request->expects($this->once())->method('isPost')->willReturn(true);
        $request->expects($this->once())->method('getPost')->willReturn($params);

        $redirect = XMock::of(Redirect::class);
        $redirect->expects($this->once())->method('toRoute')->with('account-register/duplicate-email');

        $controller = $this->getMockBuilder(EmailController::class)
            ->setConstructorArgs([
                $stepService,
                $this->emailDuplicateService,
            ])
            ->setMethods(['getRequest', 'redirect'])
            ->getMockForAbstractClass();

        $controller->expects($this->exactly(2))->method('getRequest')->willReturn($request);
        $controller->expects($this->once())->method('redirect')->willReturn($redirect);

        $serviceLocator = $this->getServiceLocatorMock();
        $controller->setServiceLocator($serviceLocator);

        $controller->indexAction();
    }

    private function getFakeDetailsArray()
    {
        return [
            EmailInputFilter::FIELD_EMAIL => 'test@dvsa.com',
            EmailInputFilter::FIELD_EMAIL_CONFIRM => 'test@dvsa.com',
        ];
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ServiceLocatorInterface
     *
     * @throws \Exception
     */
    private function getServiceLocatorMock()
    {
        $helperPluginManager = XMock::of(HelperPluginManager::class);
        $helperPluginManager
            ->expects($this->any())
            ->method('get')
            ->with('headTitle')
            ->willReturn(XMock::of(HeadTitle::class));

        /** @var ServiceLocatorInterface | PHPUnit_Framework_MockObject_MockObject $serviceLocator */
        $serviceLocator = XMock::of(ServiceLocatorInterface::class);
        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('ViewHelperManager')
            ->willReturn($helperPluginManager);

        return $serviceLocator;
    }
}
