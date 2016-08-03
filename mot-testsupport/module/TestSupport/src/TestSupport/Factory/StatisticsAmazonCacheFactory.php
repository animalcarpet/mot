<?php

namespace TestSupport\Factory;

use Aws\S3\S3Client;
use TestSupport\Service\StatisticsAmazonCacheService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StatisticsAmazonCacheFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $awsConfig = $serviceLocator->get('Config')['aws']['statisticsAmazonStorage'];

        $s3Client = new S3Client([
            'credentials' => [
                'key'    => $awsConfig['accessKeyId'],
                'secret' => $awsConfig['secretKey'],
            ],
            'version'     => 'latest',
            'region'      => $awsConfig['region'],
        ]);

        $bucket = $awsConfig['bucket'];

        $env = $awsConfig['root_folder'];

        return new StatisticsAmazonCacheService(
            $s3Client,
            $bucket,
            $env
        );
    }

}