<?php

namespace DvsaAuthentication\Factory;

use Doctrine\ORM\EntityManager;
use Dvsa\OpenAM\OpenAMClientInterface;
use DvsaAuthentication\Service\OtpFailedAttemptCounter;
use DvsaAuthentication\Service\OtpService;
use DvsaAuthentication\Service\OtpServiceAdapter\CardOtpServiceAdapter;
use DvsaAuthentication\Service\OtpServiceAdapter\DelegatingOtpServiceAdapter;
use DvsaAuthentication\Service\OtpServiceAdapter\PinOtpServiceAdapter;
use DvsaAuthentication\Service\PersonProvider;
use DvsaEntities\Entity\Person;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OtpServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return OtpService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $personRepository = $serviceLocator->get(EntityManager::class)->getRepository(Person::class);
        $configurationRepository = $serviceLocator->get('ConfigurationRepository');
        $authenticationService = $serviceLocator->get('DvsaAuthenticationService');
        $openAMClient = $serviceLocator->get(OpenAMClientInterface::class);

        return new OtpService(
            new DelegatingOtpServiceAdapter(new PinOtpServiceAdapter(), new CardOtpServiceAdapter($openAMClient)),
            new OtpFailedAttemptCounter($personRepository, $configurationRepository),
            new PersonProvider($personRepository, $authenticationService)
        );
    }
}
