<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Factory;

use Dvsa\Mot\Frontend\MotTestModule\Controller\SurveyPageController;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\SurveyPageControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use DvsaApplicationLogger\Log\Logger;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use Zend\EventManager\EventManager;
use Zend\Session\Container;

class SurveyPageControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            SurveyPageControllerFactory::class,
            SurveyPageController::class, [
                EventManager::class => EventManager::class,
                Container::class => Container::class,
                SurveyService::class => SurveyService::class,
                'Application\Logger' => Logger::class,
            ]
        );
    }
}
