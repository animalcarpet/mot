<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Factory;

use Dvsa\Mot\Frontend\MotTestModule\Controller\DefectCategoriesController;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\DefectCategoriesControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsContentBreadcrumbsBuilder;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;

class DefectCategoriesControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            DefectCategoriesControllerFactory::class,
            DefectCategoriesController::class,
            [
                'AuthorisationService' => MotAuthorisationServiceInterface::class,
                DefectsContentBreadcrumbsBuilder::class => DefectsContentBreadcrumbsBuilder::class
            ]
        );
    }
}