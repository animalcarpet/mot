<?php

namespace DvsaMotTesttest\Factory\Service;

use Aws\S3\S3Client;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Service\SurveyServiceFactory;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;

class SurveyServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            SurveyServiceFactory::class,
            SurveyService::class, [
                Client::class => Client::class,
                S3Client::class => S3Client::class,
            ]
        );
    }
}
