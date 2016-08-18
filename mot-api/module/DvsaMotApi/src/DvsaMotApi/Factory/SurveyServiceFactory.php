<?php

namespace DvsaMotApi\Factory;

use Aws\S3\S3Client;
use Doctrine\ORM\EntityManager;
use DvsaMotApi\Service\SurveyService;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SurveyServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var AuthenticationService $authService */
        $authService = $serviceLocator->get('DvsaAuthenticationService');

        $config = $serviceLocator->get('config');
        $surveyStorageConfig = $config['aws']['surveyReportsStorage'];
        $surveyDisplayConfig = $config['surveyConfig'];

        $clientConfig = [
            'credentials' => [
                'key' => $surveyStorageConfig['accessKeyId'],
                'secret' => $surveyStorageConfig['secretKey']
            ],
            'region'  => $surveyStorageConfig['region'],
            'version' => $surveyStorageConfig['sdkVersion'],
        ];

        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        $s3Client = new S3Client($clientConfig);

        return new SurveyService(
            $entityManager,
            $authService,
            $s3Client,
            $surveyStorageConfig['bucket'],
            $surveyDisplayConfig
        );
    }
}
