<?php

namespace DvsaMotApiTest\Factory\Controller;

use DvsaCommon\Formatting\DefectSentenceCaseConverter;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use DvsaMotApi\Controller\MotTestReasonForRejectionController;
use DvsaMotApi\Factory\Controller\MotTestReasonForRejectionControllerFactory;

class MotTestReasonForRejectionControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            MotTestReasonForRejectionControllerFactory::class,
            MotTestReasonForRejectionController::class, [
                DefectSentenceCaseConverter::class => DefectSentenceCaseConverter::class,
            ]
        );
    }
}
