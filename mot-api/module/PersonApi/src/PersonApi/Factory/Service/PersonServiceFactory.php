<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use DvsaEntities\Entity\Person;
use OrganisationApi\Service\Mapper\PersonMapper;
use PersonApi\Service\PersonService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PersonServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PersonService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);
        $realm = $serviceLocator->get(OpenAMClientOptions::class)->getRealm();

        return new PersonService(
            $entityManager->getRepository(Person::class),
            new PersonMapper(),
            $serviceLocator->get(OpenAMClientInterface::class),
            $realm,
            $serviceLocator->get('TesterService'),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('DvsaAuthenticationService')
        );
    }
}
