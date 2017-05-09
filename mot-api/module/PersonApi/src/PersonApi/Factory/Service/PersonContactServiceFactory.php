<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\PersonContact;
use OrganisationApi\Service\Mapper\PersonContactMapper;
use PersonApi\Service\PersonContactService;
use PersonApi\Service\Validator\PersonalDetailsValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaAuthorisation\Service\AuthorisationService;
use Zend\Authentication\AuthenticationService;
use PersonApi\Helper\PersonDetailsChangeNotificationHelper;

/**
 * Factory for PersonContactService.
 */
class PersonContactServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PersonContactService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);
        $repository = $entityManager->getRepository(PersonContact::class);
        $emailRepository = $entityManager->getRepository(Email::class);
        /** @var PersonContactMapper $mapper */
        $mapper = $serviceLocator->get(PersonContactMapper::class);
        /** @var PersonalDetailsValidator $validator */
        $validator = $serviceLocator->get(PersonalDetailsValidator::class);
        /** @var AuthenticationService $authenticationService */
        $authenticationService = $serviceLocator->get('DvsaAuthenticationService');
        /** @var AuthorisationService $authorisationService */
        $authorisationService = $serviceLocator->get('DvsaAuthorisationService');
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $notificationHelper = $serviceLocator->get(PersonDetailsChangeNotificationHelper::class);

        return new PersonContactService(
            $repository,
            $mapper,
            $emailRepository,
            $validator,
            $authenticationService,
            $authorisationService,
            $entityManager,
            $notificationHelper
        );
    }
}
