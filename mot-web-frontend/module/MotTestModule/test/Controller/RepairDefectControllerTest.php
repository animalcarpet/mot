<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Controller;

use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\MotTestModule\Controller\RepairDefectController;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use DvsaCommonTest\Bootstrap;

class RepairDefectControllerTest extends AbstractFrontendControllerTestCase
{
    protected function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);

        $defectsJourneyUrlGeneratorMock = $this
            ->getMockBuilder(DefectsJourneyUrlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defectsJourneyUrlGeneratorMock->method('goBack')->willReturn('/mot-test/123456789000');

        $this->setServiceManager($this->serviceManager);
        $this->setController(
            new RepairDefectController($defectsJourneyUrlGeneratorMock)
        );

        parent::setUp();
    }

    public function testSuccessfulRepairShowsSuccessMessage()
    {
        $motTestNumber = 1;
        $identifiedDefectId = 2;
        $identifiedDefectType = 'failure';
        $identifiedDefectText = 'Engine made of cheese';

        $routeParams = [
            'motTestNumber' => $motTestNumber,
        ];

        $postParams = [
            'defectType' => $identifiedDefectType,
            'defectText' => $identifiedDefectText,
            'defectId' => $identifiedDefectId,
        ];

        $this->getFlashMessengerMockForAddSuccessMessage(sprintf(
            'The %s <strong>%s</strong> has been repaired',
            $identifiedDefectType,
            $identifiedDefectText));

        $this->getResultForAction2('post', 'repair', $routeParams, [], $postParams);

        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }
}
