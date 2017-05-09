<?php

namespace OrganisationApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Service\AddressService;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaCommonApi\Service\Validator\AddressValidator;
use DvsaCommonApi\Service\Validator\ContactDetailsValidator;
use DvsaEntities\Entity\PhoneContactType;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ContactDetailsServiceFactory.
 */
class ContactDetailsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new ContactDetailsService(
            $entityManager,
            $serviceLocator->get(AddressService::class),
            $entityManager->getRepository(PhoneContactType::class),
            new ContactDetailsValidator(new AddressValidator())
        );
    }
}
