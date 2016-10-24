<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Factory\Storage\S3;

use Aws\S3\S3Client;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\KeyValueStorage\S3\S3KeyValueStorage;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TqiStatisticsStorageFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotConfig $config */
        $config = $serviceLocator->get(MotConfig::class);

        $s3ClientArgs = [
            'version' => 'latest',
            'region' => $config->get('aws', 'statisticsAmazonStorage', 'region'),
        ];

        // If "accessKeyId" and "secretKey" are not defined then fallback to IAM roles.
        if ($config->valueExists('aws', 'statisticsAmazonStorage', 'accessKeyId') ||
            $config->valueExists('aws', 'statisticsAmazonStorage', 'secretKey')) {
            $s3ClientArgs['credentials'] = [
                'key' => $config->get('aws', 'statisticsAmazonStorage', 'accessKeyId'),
                'secret' => $config->get('aws', 'statisticsAmazonStorage', 'secretKey'),
            ];
        }

        $s3Client = new S3Client($s3ClientArgs);
        $bucket = $config->get('aws', 'statisticsAmazonStorage', 'bucket');
        $rootFolder = $config->get('aws', 'statisticsAmazonStorage', 'root_folder');

        return new S3KeyValueStorage($s3Client, $bucket, $rootFolder);
    }
}
