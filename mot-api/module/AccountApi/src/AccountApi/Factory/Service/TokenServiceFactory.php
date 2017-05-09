<?php

namespace AccountApi\Factory\Service;

use AccountApi\Service\OpenAmIdentityService;
use AccountApi\Service\TokenService;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaEntities\Entity\Message;
use DvsaEntities\Entity\MessageType;
use DvsaEntities\Entity\Person;
use MailerApi\Service\MailerService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class TokenServiceFactory.
 */
class TokenServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return TokenService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $entityManager = $serviceLocator->get(EntityManager::class);

        $service = new TokenService(
            $entityManager,
            $entityManager->getRepository(Message::class),
            $entityManager->getRepository(MessageType::class),
            $entityManager->getRepository(Person::class),
            $serviceLocator->get('Application\Logger'),
            $serviceLocator->get(MailerService::class),
            $serviceLocator->get(OpenAmIdentityService::class),
            $config,
            $serviceLocator->get(ParamObfuscator::class),
            $serviceLocator->get('DvsaAuthenticationService'),
            $serviceLocator->get('DvsaAuthorisationService')
        );

        return $service;
    }
}
