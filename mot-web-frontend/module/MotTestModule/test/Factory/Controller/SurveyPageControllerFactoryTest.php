<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Factory;

use Dvsa\Mot\Frontend\MotTestModule\Controller\SurveyPageController;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\SurveyPageControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;

class SurveyPageControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            SurveyPageControllerFactory::class,
            SurveyPageController::class, [
                SurveyService::class => SurveyService::class,
            ]
        );
    }
}
