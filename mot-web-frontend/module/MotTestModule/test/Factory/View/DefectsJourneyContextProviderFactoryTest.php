<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Factory\View;

use Dvsa\Mot\Frontend\MotTestModule\Factory\View\DefectsJourneyContextProviderFactory;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use PHPUnit_Framework_TestCase;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\Http\TreeRouteStack;

class DefectsJourneyContextProviderFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            DefectsJourneyContextProviderFactory::class,
            DefectsJourneyContextProvider::class,
            [
                'Router' => TreeRouteStack::class,
                'Request' => Request::class,
            ]
        );
    }
}
