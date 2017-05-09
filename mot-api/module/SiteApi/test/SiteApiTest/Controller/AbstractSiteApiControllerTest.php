<?php

namespace SiteApiTest\Controller;

use DvsaCommonApiTest\Controller\ApiControllerUnitTestInterface;
use DvsaCommonApiTest\Controller\ApiControllerUnitTestTrait;
use SiteApi\Service\EquipmentService;
use SiteApi\Service\SiteBusinessRoleService;
use SiteApi\Service\SiteTestingDailyScheduleService;

/**
 * Base for tester application controller test in TesterApplication namespace.
 */
abstract class AbstractSiteApiControllerTest extends \PHPUnit_Framework_TestCase implements
    ApiControllerUnitTestInterface
{
    use ApiControllerUnitTestTrait;

    public function mockServices()
    {
        $this->createMock(EquipmentService::class);
        $this->createMock(SiteBusinessRoleService::class);
        $this->createMock(SiteTestingDailyScheduleService::class);
    }
}
