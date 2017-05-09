<?php

namespace EventTest\Factory\Controller;

use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use Event\Controller\EventController;
use Event\Factory\Controllers\EventControllerFactory;

class EventControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            EventControllerFactory::class,
            EventController::class,
            [
                ContextProvider::class => ContextProvider::class,
            ]
        );
    }
}
