<?php

namespace PersonApiTest\Controller;

use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Controller\MotTestingAuthorisationController;
use PersonApi\Dto\MotTestingAuthorisationCollector;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;

/**
 * Unit tests for MotTestingAuthorisationController.
 */
class MotTestingAuthorisationControllerTest extends AbstractPersonControllerTestCase
{
    public function setUp()
    {
        $personalAuthorisationForTestingMotMock = XMock::of(
            PersonalAuthorisationForMotTestingService::class,
            ['getPersonalTestingAuthorisation', 'updatePersonalTestingAuthorisationGroup']
        );
        $motTestingAuthorisationCollectorMock = XMock::of(
            MotTestingAuthorisationCollector::class,
            ['toArray']
        );
        $personalAuthorisationForTestingMotMock->expects($this->once())
            ->method('getPersonalTestingAuthorisation')
            ->willReturn($motTestingAuthorisationCollectorMock);
        $personalAuthorisationForTestingMotMock->expects($this->once())
            ->method('updatePersonalTestingAuthorisationGroup')
            ->willReturn($motTestingAuthorisationCollectorMock);
        $this->controller = new MotTestingAuthorisationController($personalAuthorisationForTestingMotMock);
        $this->setUpTestCase();
    }

    public function testWhiteList()
    {
        $this->assertMethodsOk(
            ['get', 'update']
        );
    }
}
