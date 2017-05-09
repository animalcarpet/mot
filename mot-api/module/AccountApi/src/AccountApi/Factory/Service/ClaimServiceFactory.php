<?php

namespace AccountApi\Factory\Service;

use AccountApi\Service\ClaimService;
use AccountApi\Service\OpenAmIdentityService;
use AccountApi\Service\Validator\ClaimValidator;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContactType;
use DvsaEntities\Entity\SecurityQuestion;
use DvsaEventApi\Service\EventService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ClaimServiceFactory.
 */
class ClaimServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        $claimService = new ClaimService(
            $entityManager,
            $serviceLocator->get(MotIdentityProviderInterface::class),
            $serviceLocator->get(ClaimValidator::class),
            $entityManager->getRepository(SecurityQuestion::class),
            $entityManager->getRepository(Person::class),
            $serviceLocator->get(OpenAmIdentityService::class),
            $serviceLocator->get(EventService::class),
            $serviceLocator->get(ParamObfuscator::class),
            new DateTimeHolder(),
            $entityManager->getRepository(PersonContactType::class)
        );

        return $claimService;
    }
}
