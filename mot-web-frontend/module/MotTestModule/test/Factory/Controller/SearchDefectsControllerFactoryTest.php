<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Factory;


use Dvsa\Mot\Frontend\MotTestModule\Controller\SearchDefectsController;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\SearchDefectsControllerFactory;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;

class SearchDefectsControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            SearchDefectsControllerFactory::class,
            SearchDefectsController::class,
            []
        );
    }
}
