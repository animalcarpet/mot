<?php

namespace UserApi\Factory;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\Person;
use UserApi\HelpDesk\Mapper\PersonHelpDeskProfileMapper;
use UserApi\HelpDesk\Service\HelpDeskPersonService;
use UserApi\HelpDesk\Service\Validator\SearchPersonValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Creates HelpDeskPersonService.
 */
class HelpDeskPersonServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new HelpDeskPersonService(
            $serviceLocator->get(EntityManager::class)->getRepository(Person::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('UserRoleService'),
            new SearchPersonValidator(),
            new PersonHelpDeskProfileMapper()
        );
    }
}
