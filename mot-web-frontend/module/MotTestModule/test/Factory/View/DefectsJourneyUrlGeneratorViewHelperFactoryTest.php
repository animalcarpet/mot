<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Factory\View;

use Dvsa\Mot\Frontend\MotTestModule\Factory\View\DefectsJourneyUrlGeneratorViewHelperFactory;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGeneratorViewHelper;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use PHPUnit_Framework_TestCase;

class DefectsJourneyUrlGeneratorViewHelperFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceWithPluginManager(
            DefectsJourneyUrlGeneratorViewHelperFactory::class,
            DefectsJourneyUrlGeneratorViewHelper::class,
            [
                DefectsJourneyUrlGenerator::class => DefectsJourneyUrlGenerator::class,
                DefectsJourneyContextProvider::class => DefectsJourneyContextProvider::class,
            ]
        );
    }
}
