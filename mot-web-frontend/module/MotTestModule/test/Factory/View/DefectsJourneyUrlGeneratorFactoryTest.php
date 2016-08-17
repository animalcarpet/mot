<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Factory\View;

use Dvsa\Mot\Frontend\MotTestModule\Factory\View\DefectsJourneyUrlGeneratorFactory;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use PHPUnit_Framework_TestCase;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\RouteStackInterface;

class DefectsJourneyUrlGeneratorFactoryTest  extends PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            DefectsJourneyUrlGeneratorFactory::class,
            DefectsJourneyUrlGenerator::class,
            [
                'Router' => RouteStackInterface::class,
                'Request' => Request::class,
                DefectsJourneyContextProvider::class => DefectsJourneyContextProvider::class
            ]
        );
    }
}