<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Factory;

use Dvsa\Mot\Frontend\MotTestModule\Controller\RemoveDefectController;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\RemoveDefectControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;

class RemoveDefectControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            RemoveDefectControllerFactory::class,
            RemoveDefectController::class,
            [
                DefectsJourneyContextProvider::class => DefectsJourneyContextProvider::class,
                DefectsJourneyUrlGenerator::class => DefectsJourneyUrlGenerator::class,
            ]
        );
    }
}
