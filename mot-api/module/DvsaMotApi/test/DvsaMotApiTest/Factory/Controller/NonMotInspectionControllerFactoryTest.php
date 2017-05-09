<?php

namespace DvsaMotApiTest\Factory\Controller;

use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use DvsaMotApi\Controller\NonMotInspectionController;
use DvsaMotApi\Factory\Controller\NonMotInspectionControllerFactory;
use DvsaMotApi\Service\MotTestService;

class NonMotInspectionControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            NonMotInspectionControllerFactory::class,
            NonMotInspectionController::class,
            [
                'MotTestService' => MotTestService::class,
                'DvsaAuthorisationService' => AuthorisationService::class,
            ]
        );
    }
}
