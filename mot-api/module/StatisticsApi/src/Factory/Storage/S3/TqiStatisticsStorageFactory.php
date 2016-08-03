<?php

namespace Dvsa\Mot\Api\StatisticsApi\Factory\Storage\S3;

use Aws\S3\S3Client;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;
use DvsaCommon\KeyValueStorage\S3\S3KeyValueStorage;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TqiStatisticsStorageFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get(MotConfig::class);

        $s3Client = new S3Client([
            'credentials' => [
                'key'    => $config->get('aws', 'statisticsAmazonStorage', 'accessKeyId'),
                'secret' => $config->get('aws', 'statisticsAmazonStorage', 'secretKey'),
            ],
            'version'     => 'latest',
            'region'      => $config->get('aws', 'statisticsAmazonStorage', 'region'),
        ]);

        $bucket = $config->get('aws', 'statisticsAmazonStorage', 'bucket');

        $rootFolder = $config->get('aws', 'statisticsAmazonStorage', 'root_folder');

        return new S3KeyValueStorage(
            $s3Client,
            $bucket,
            $rootFolder
        );
    }
}
