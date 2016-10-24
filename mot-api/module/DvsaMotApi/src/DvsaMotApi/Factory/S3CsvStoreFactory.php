<?php

namespace DvsaMotApi\Factory;

use Aws\S3\S3Client;
use DvsaCommon\Configuration\MotConfig;
use DvsaMotApi\Service\S3\S3CsvStore;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class S3CsvStoreFactory implements FactoryInterface
{
    const AWS_CONFIG_KEY = 'aws';
    const SURVEY_CONFIG_KEY = 'surveyReportsStorage';

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return S3CsvStore
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotConfig $config */
        $config = $serviceLocator->get(MotConfig::class);

        $s3ClientArgs = [
            'version' => 'latest',
            'region' => $config->get(self::AWS_CONFIG_KEY, self::SURVEY_CONFIG_KEY, 'region'),
        ];

        // If "accessKeyId" and "secretKey" are not defined then fallback to IAM roles.
        if ($config->valueExists(self::AWS_CONFIG_KEY, self::SURVEY_CONFIG_KEY, 'accessKeyId') ||
            $config->valueExists(self::AWS_CONFIG_KEY, self::SURVEY_CONFIG_KEY, 'secretKey')) {
            $s3ClientArgs['credentials'] = [
                'key' => $config->get(self::AWS_CONFIG_KEY, self::SURVEY_CONFIG_KEY, 'accessKeyId'),
                'secret' => $config->get(self::AWS_CONFIG_KEY, self::SURVEY_CONFIG_KEY, 'secretKey'),
            ];
        }

        $s3Client = new S3Client($s3ClientArgs);
        $bucket = $config->get(self::AWS_CONFIG_KEY, self::SURVEY_CONFIG_KEY, 'bucket');
        $rootFolder = $config->get(self::AWS_CONFIG_KEY, self::SURVEY_CONFIG_KEY, 'root_folder');

        return new S3CsvStore($s3Client, $bucket, $rootFolder);
    }
}
