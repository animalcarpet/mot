<?php

/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */
namespace DvsaMotTestTest\Factory\Listener;

use Core\Service\MotEventManager;
use Dvsa\Mot\Frontend\MotTestModule\Listener\SatisfactionSurveyListener;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaFeature\FeatureToggles;
use DvsaMotTest\Service\SurveyService;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use PHPUnit_Framework_TestCase;
use Zend\EventManager\Event;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\Mvc\Router\RouteStackInterface;

class SatisfactionSurveyListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SurveyService|MockObj
     */
    private $surveyServiceMock;

    /**
     * @var MotEventManager|MockObj
     */
    private $eventManagerMock;

    /**
     * @var Event|MockObj
     */
    private $eventMock;

    /**
     * @var RouteStackInterface|MockObj
     */
    private $routerMock;

    /**
     * @var SatisfactionSurveyListener|MockObj
     */
    private $listener;

    public function setUp()
    {
        $this->surveyServiceMock = $this->getMockBuilder(SurveyService::class)
            ->disableOriginalConstructor()
            ->setMethods(['surveyShouldDisplay', 'generateToken'])
            ->getMock();

        $this->eventManagerMock = $this->getMockBuilder(MotEventManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $this->routerMock= $this->getMockBuilder(TreeRouteStack::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testTokenIsGeneratedWithSurveyEnabled()
    {
        $this->surveyServiceMock->expects($this->once())
            ->method('surveyShouldDisplay')
            ->willReturn(true);

        $this->surveyServiceMock->expects($this->once())
            ->method('generateToken');

        $motDetailsMock = $this->getMockBuilder(MotTestDto::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTestType', 'getTester', 'getCode', 'getId'])
            ->getMock();

        $motDetailsMock->expects($this->once())
            ->method('getTestType')
            ->willReturnSelf();

        $motDetailsMock->expects($this->once())
            ->method('getCode')
            ->willReturn('testCode');

        $motDetailsMock->expects($this->once())
            ->method('getTester')
            ->willReturnSelf();

        $motDetailsMock->expects($this->once())
            ->method('getId')
            ->willReturn('testId');

        // returnValueMap must map _all_ arguments, including optional ones
        $valueMap = [
            ['motDetails', null, $motDetailsMock],
            ['motTestNumber', null, 12345],
        ];

        $this->eventMock->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap($valueMap)
            );

        $this->listener = new SatisfactionSurveyListener(
            $this->surveyServiceMock,
            $this->eventManagerMock,
            $this->routerMock
        );

        $this->listener->generateSurveyTokenIfEligible($this->eventMock);
    }

    public function testTokenIsNotGeneratedWhenSurveyShouldNotBeDisplayed()
    {
        $this->surveyServiceMock->expects($this->once())
            ->method('surveyShouldDisplay')
            ->willReturn(false);

        $motDetailsMock = $this->getMockBuilder(MotTestDto::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTestType', 'getTester', 'getCode', 'getId'])
            ->getMock();

        $motDetailsMock->expects($this->once())
            ->method('getTestType')
            ->willReturnSelf();

        $motDetailsMock->expects($this->once())
            ->method('getCode')
            ->willReturn('testCode');

        $motDetailsMock->expects($this->once())
            ->method('getTester')
            ->willReturnSelf();

        $motDetailsMock->expects($this->once())
            ->method('getId')
            ->willReturn('testId');

        $this->eventMock->expects($this->any())
            ->method('getParam')
            ->willReturn($motDetailsMock);

        $this->listener = new SatisfactionSurveyListener(
            $this->surveyServiceMock,
            $this->eventManagerMock,
            $this->routerMock
        );

        $this->listener->generateSurveyTokenIfEligible($this->eventMock);
    }
}
