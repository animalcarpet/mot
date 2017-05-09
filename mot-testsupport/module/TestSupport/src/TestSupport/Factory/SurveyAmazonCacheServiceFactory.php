<?php

namespace TestSupport\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Aws\S3\S3Client;
use TestSupport\Service\SurveyAmazonCacheService;

class SurveyAmazonCacheServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $awsConfig = $serviceLocator->get('Config')['aws']['surveyReportsStorage'];

        $s3Client = new S3Client(
            [
                'credentials' => [
                    'key' => $awsConfig['accessKeyId'],
                    'secret' => $awsConfig['secretKey'],
                ],
                'version' => 'latest',
                'region' => $awsConfig['region'],
            ]
        );

        $bucket = $awsConfig['bucket'];

        $env = $awsConfig['root_folder'];

        return new SurveyAmazonCacheService(
            $s3Client,
            $bucket,
            $env
        );
    }
}
