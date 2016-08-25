<?php

namespace OrganisationApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use DvsaAuthentication\Service\TwoFactorStatusService;
use DvsaCommon\Database\Transaction;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\Person;
use DvsaFeature\FeatureToggles;
use OrganisationApi\Model\Operation\DirectNominationOperation;
use OrganisationApi\Model\Operation\ConditionalNominationOperation;
use OrganisationApi\Service\NominateRoleServiceBuilder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;

/**
 * Class NominateRoleServiceFactory
 * @package OrganisationApi\Factory\Service
 */
class NominateRoleServiceBuilderFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new NominateRoleServiceBuilder(
            $entityManager->getRepository(Organisation::class),
            $entityManager->getRepository(Person::class),
            $entityManager->getRepository(OrganisationBusinessRole::class),
            $entityManager->getRepository(BusinessRoleStatus::class),
            $entityManager->getRepository(OrganisationBusinessRoleMap::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(ConditionalNominationOperation::class),
            $serviceLocator->get(DirectNominationOperation::class),
            new Transaction($serviceLocator->get(\Doctrine\ORM\EntityManager::class)),
            $serviceLocator->get('DvsaAuthenticationService'),
            $serviceLocator->get('Feature\FeatureToggles'),
            $serviceLocator->get(TwoFactorStatusService::class)
        );
    }
}
