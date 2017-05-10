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

        $s3ClientArgs = [
             'version' => 'latest',
             'region' => $awsConfig['region'],
        ];

        //If "accessKeyId" and "secretKey" are not defined then fallback to IAM roles.
        if ($this->keyExists('accessKeyId', $awsConfig) ||
           $this->keyExists('secretKey', $awsConfig)) {
           $s3ClientArgs['credentials'] = [
               'key' => $awsConfig['accessKeyId'],
              'secret' => $awsConfig['secretKey'],
           ];
        }

        $s3Client = new S3Client($s3ClientArgs);

        $bucket = $awsConfig['bucket'];

        $env = $awsConfig['root_folder'];

        return new StatisticsAmazonCacheService(
            $s3Client,
            $bucket,
            $env
        );
    }

    private function keyExists($key, $config) {
      return array_key_exists($key, $config) && $config[$key] != '';
    }

}
