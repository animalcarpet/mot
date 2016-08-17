<?php

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaMotApi\Service\S3\S3CsvStore;
use DvsaMotApi\Service\SurveyService;
use DvsaMotApi\Domain\Survey\SurveyConfiguration;
use UnexpectedValueException;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SurveyServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SurveyService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        /** @var AuthenticationService $authService */
        $authService = $serviceLocator->get('DvsaAuthenticationService');

        $config = $serviceLocator->get('config');
        if (!isset($config['surveyConfig']) || !is_array($config['surveyConfig'])) {
            throw new UnexpectedValueException('Key "surveyConfig" is either missing from the config or not an array.');
        }
        $surveyConfiguration = new SurveyConfiguration($config['surveyConfig']);

        $fileStorageClient = $serviceLocator->get(S3CsvStore::class);

        return new SurveyService($entityManager, $authService, $fileStorageClient, $surveyConfiguration);
    }
}
