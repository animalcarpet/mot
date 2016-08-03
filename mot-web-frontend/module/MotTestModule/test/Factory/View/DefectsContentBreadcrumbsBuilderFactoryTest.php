<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Factory\View;


use Dvsa\Mot\Frontend\MotTestModule\Factory\View\DefectsContentBreadcrumbsBuilderFactory;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsContentBreadcrumbsBuilder;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use Zend\Mvc\Router\RouteStackInterface;

class DefectsContentBreadcrumbsBuilderFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            DefectsContentBreadcrumbsBuilderFactory::class,
            DefectsContentBreadcrumbsBuilder::class,
            [
                'Router' => RouteStackInterface::class,
            ]
        );
    }
}