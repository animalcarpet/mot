<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Factory\Service;

use Aws\S3\S3Client;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Service\SurveyServiceFactory;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use DvsaClient\MapperFactory;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use PHPUnit_Framework_TestCase;

class SurveyServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            SurveyServiceFactory::class,
            SurveyService::class, [
                Client::class => Client::class,
                S3Client::class => S3Client::class,
                MapperFactory::class => MapperFactory::class,
            ]
        );
    }
}
