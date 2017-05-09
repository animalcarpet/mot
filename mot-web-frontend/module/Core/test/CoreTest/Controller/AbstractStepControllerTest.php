<?php

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Controller;

use Core\Controller\AbstractStepController;
use Core\Service\StepService;
use Zend\Mvc\Controller\Plugin\Redirect;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\DetailsStep;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\Request as HttpRequest;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Model\ViewModel;

class AbstractStepControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Mock the GET path through doStepLogic.
     *
     * @throws \Exception
     */
    public function testDoStepLogicGetRequest()
    {
        $step = $this->getMockBuilder(DetailsStep::class)
            ->setConstructorArgs([
                XMock::of(RegistrationSessionService::class),
                XMock::of(DetailsInputFilter::class),
            ])->getMock();

        $previousStep = $this->getMockBuilder(DetailsStep::class)
            ->setConstructorArgs([
                XMock::of(RegistrationSessionService::class),
                XMock::of(DetailsInputFilter::class),
            ])->getMock();

        $previousStep->expects($this->once())->method('load')->willReturn($previousStep);

        $step->expects($this->once())->method('load');
        $step->expects($this->once())->method('getProgress');
        $step->expects($this->once())->method('toViewArray');

        $stepService = XMock::of(StepService::class);
        $stepService->expects($this->once())->method('setActiveById')->willReturnSelf();
        $stepService->expects($this->once())->method('current')->willReturn($step);
        $stepService->expects($this->once())->method('previous')->willReturn($previousStep);

        // mock the getRequest is post call
        $request = XMock::of(HttpRequest::class);
        $request->expects($this->once())->method('isPost')->willReturn(false);

        $controller = $this->getMockBuilder(AbstractStepController::class)
            ->setConstructorArgs([
                $stepService,
            ])
            ->setMethods(['getRequest'])
            ->getMockForAbstractClass();

        $controller->expects($this->once())->method('getRequest')->willReturn($request);

        $result = $controller->doStepLogic(1, 'title');

        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * Mock the POST  path through doStepLogic with INVALID values.
     *
     * @throws \Exception
     */
    public function testDoStepLogicPostRequestInvalidValues()
    {
        $step = $this->getMockBuilder(DetailsStep::class)
            ->setConstructorArgs([
                XMock::of(RegistrationSessionService::class),
                XMock::of(DetailsInputFilter::class),
            ])->getMock();

        $step->expects($this->once())->method('load');
        $step->expects($this->once())->method('readFromArray');
        $step->expects($this->once())->method('save');
        $step->expects($this->once())->method('validate')->willReturn(false);
        $step->expects($this->once())->method('getProgress');
        $step->expects($this->once())->method('toViewArray');

        $stepService = XMock::of(StepService::class);
        $stepService->expects($this->once())->method('setActiveById')->willReturnSelf();
        $stepService->expects($this->once())->method('current')->willReturn($step);

        $params = XMock::of(ParametersInterface::class);
        $params->expects($this->once())->method('toArray')->willReturn($this->getFakeDetailsArray());

        $request = XMock::of(HttpRequest::class);
        $request->expects($this->once())->method('isPost')->willReturn(true);
        $request->expects($this->once())->method('getPost')->willReturn($params);

        $controller = $this->getMockBuilder(AbstractStepController::class)
            ->setConstructorArgs([
                $stepService,
            ])
            ->setMethods(['getRequest'])
            ->getMockForAbstractClass();

        $controller->expects($this->exactly(2))->method('getRequest')->willReturn($request);

        $result = $controller->doStepLogic(1, 'title');

        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * Mock the POST path through doStepLogic with VALID values.
     *
     * @throws \Exception
     */
    public function testDoStepLogicPostRequestValidValues()
    {
        $step = $this->getMockBuilder(DetailsStep::class)
            ->setConstructorArgs([
                XMock::of(RegistrationSessionService::class),
                XMock::of(DetailsInputFilter::class),
            ])->getMock();

        $step->expects($this->once())->method('load');
        $step->expects($this->once())->method('readFromArray');
        $step->expects($this->once())->method('save');
        $step->expects($this->once())->method('validate')->willReturn(true);
        $step->expects($this->never())->method('getProgress');
        $step->expects($this->never())->method('toViewArray');

        $stepService = XMock::of(StepService::class);
        $stepService->expects($this->once())->method('setActiveById')->willReturnSelf();
        $stepService->expects($this->once())->method('current')->willReturn($step);
        $stepService->expects($this->once())->method('next')->willReturn($step);

        $params = XMock::of(ParametersInterface::class);
        $params->expects($this->once())->method('toArray')->willReturn($this->getFakeDetailsArray());

        $request = XMock::of(HttpRequest::class);
        $request->expects($this->once())->method('isPost')->willReturn(true);
        $request->expects($this->once())->method('getPost')->willReturn($params);

        $redirect = XMock::of(Redirect::class);
        $redirect->expects($this->once())->method('toRoute');

        $controller = $this->getMockBuilder(AbstractStepController::class)
            ->setConstructorArgs([
                $stepService,
            ])
            ->setMethods(['getRequest', 'redirect'])
            ->getMockForAbstractClass();

        $controller->expects($this->exactly(2))->method('getRequest')->willReturn($request);
        $controller->expects($this->once())->method('redirect')->willReturn($redirect);

        $controller->doStepLogic(1, 'title');
    }

    private function getFakeDetailsArray()
    {
        return [
            DetailsInputFilter::FIELD_FIRST_NAME => 'John',
            DetailsInputFilter::FIELD_MIDDLE_NAME => 'James',
            DetailsInputFilter::FIELD_LAST_NAME => 'Doe',
        ];
    }
}
