<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaMotApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Controller\ContingencyTestController;
use DvsaMotApi\Factory\Controller\ContingencyTestControllerFactory;
use DvsaMotApi\Service\EmergencyService;
use SiteApi\Service\SiteService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ContingencyTestControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            ContingencyTestControllerFactory::class,
            ContingencyTestController::class, [
                EmergencyService::class         => EmergencyService::class,
                SiteService::class              => SiteService::class,
                'Feature\FeatureToggles'        => function() {
                    return XMock::of(FeatureToggles::class);
                }

            ]
        );
    }
}
