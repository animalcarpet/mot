<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace EventTest\Controller;

use DvsaCommonTest\TestUtils\XMock;
use Event\Controller\EventOutcomeController;
use Event\Service\EventSessionService;
use Event\Service\EventStepService;
use Event\Step\OutcomeStep;
use Event\Step\RecordStep;
use Zend\View\Model\ViewModel;
use PHPUnit_Framework_MockObject_MockObject;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\HelperPluginManager;
use Zend\View\Helper\HeadTitle;

/**
 * Class EventOutcomeControllerTest.
 *
 * @group event
 */
class EventOutcomeControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexAction_noViewModel()
    {
        $session = XMock::of(EventSessionService::class);

        $step = XMock::of(OutcomeStep::class);
        $step->expects($this->any())->method('load')->willReturn($step);
        $step->expects($this->any())->method('getEventType')->willReturn('ae');

        $service = XMock::of(EventStepService::class);
        $service->expects($this->once())->method('injectParamsIntoSteps');

        $controller = $this->getMockBuilder(EventOutcomeController::class)
            ->setConstructorArgs([$service, $session])
            ->setMethods([
                'extractRouteParams',
                'loadEventCatalogData',
                'loadEventCategory',
                'assertPermission',
                'doStepLogic',
                'resetOutcomeStep',
            ])
            ->getMock();

        $serviceLocator = $this->getServiceLocatorMock();
        $controller->setServiceLocator($serviceLocator);

        $controller->expects($this->once())->method('extractRouteParams');
        $controller->expects($this->once())->method('loadEventCatalogData');
        $controller->expects($this->once())->method('loadEventCategory');
        $controller->expects($this->once())->method('assertPermission');
        $controller->expects($this->once())->method('doStepLogic');
        $controller->expects($this->never())->method('injectViewModelVariables');
        $controller->expects($this->never())->method('resetOutcomeStep');

        $controller->indexAction();
    }

    public function testIndexAction_withViewModel()
    {
        $session = XMock::of(EventSessionService::class);

        $model = XMock::of(ViewModel::class);

        $step = XMock::of(RecordStep::class);
        $step->expects($this->any())->method('load')->willReturn($step);
        $step->expects($this->any())->method('getEventType')->willReturn('ae');

        $service = XMock::of(EventStepService::class);
        $service->expects($this->any())->method('getById')->willReturn($step);
        $service->expects($this->once())->method('injectParamsIntoSteps');

        $controller = $this->getMockBuilder(EventOutcomeController::class)
            ->setConstructorArgs([$service, $session])
            ->setMethods([
                'extractRouteParams',
                'loadEventCatalogData',
                'loadEventCategory',
                'assertPermission',
                'doStepLogic',
                'resetOutcomeStep',
                'injectViewModelVariables',
            ])
            ->getMock();

        $serviceLocator = $this->getServiceLocatorMock();
        $controller->setServiceLocator($serviceLocator);

        $controller->expects($this->once())->method('extractRouteParams');
        $controller->expects($this->once())->method('loadEventCatalogData');
        $controller->expects($this->once())->method('loadEventCategory');
        $controller->expects($this->once())->method('assertPermission');
        $controller->expects($this->once())->method('doStepLogic')->willReturn($model);
        $controller->expects($this->once())->method('injectViewModelVariables')->willReturn($model);

        $controller->indexAction();
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
