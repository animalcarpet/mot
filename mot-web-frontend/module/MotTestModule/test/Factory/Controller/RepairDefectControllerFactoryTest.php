<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotTestTest\Factory\Controller;

use Dvsa\Mot\Frontend\MotTestModule\Controller\RepairDefectController;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\RepairDefectControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;

class RepairDefectControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            RepairDefectControllerFactory::class,
            RepairDefectController::class,
            [
                DefectsJourneyUrlGenerator::class => DefectsJourneyUrlGenerator::class,
            ]
        );
    }
}
