<?php

namespace DvsaCommonApiTest\Service;

use DvsaCommonApi\Module\AbstractErrorHandlingModule;
use Zend\Mvc\MvcEvent;
use PHPUnit_Framework_TestCase;

/**
 * Class AbstractErrorHandlingModuleTest.
 */
class AbstractErrorHandlingModuleTest extends PHPUnit_Framework_TestCase
{
    public function testAttachJsonErrorHandling()
    {
        $testErrorHandlingModule = new TestErrorHandlingModule();

        $mockEventManager = \DvsaCommonTest\TestUtils\XMock::of(\Zend\EventManager\EventManager::class);

        $mockEventManager->expects($this->at(0))
                         ->method('attach')
                         ->with(MvcEvent::EVENT_DISPATCH_ERROR, [$testErrorHandlingModule, 'onDispatchError'], 1);
        $mockEventManager->expects($this->at(1))
                         ->method('attach')
                         ->with(MvcEvent::EVENT_RENDER_ERROR, [$testErrorHandlingModule, 'onRenderError'], 1);

        $testErrorHandlingModule->attachJsonErrorHandling($mockEventManager);
    }
}

/**
 * Class TestErrorHandlingModule.
 */
class TestErrorHandlingModule extends AbstractErrorHandlingModule
{
}
