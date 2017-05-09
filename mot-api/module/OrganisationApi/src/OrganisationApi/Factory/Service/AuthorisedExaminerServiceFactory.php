<?php

namespace OrganisationApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaEntities\Entity\AuthForAeStatus;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\CompanyType;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationContactType;
use DvsaEntities\Entity\OrganisationType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEventApi\Service\EventService;
use OrganisationApi\Service\AuthorisedExaminerService;
use OrganisationApi\Service\Mapper\OrganisationMapper;
use OrganisationApi\Service\Validator\AuthorisedExaminerValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AuthorisedExaminerServiceFactory.
 */
class AuthorisedExaminerServiceFactory implements FactoryInterface
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return \OrganisationApi\Service\AuthorisedExaminerService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new AuthorisedExaminerService(
            $entityManager,
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(MotIdentityProviderInterface::class)->getIdentity(),
            $serviceLocator->get(ContactDetailsService::class),
            $serviceLocator->get(EventService::class),
            $entityManager->getRepository(Organisation::class),
            $entityManager->getRepository(Person::class),
            $entityManager->getRepository(OrganisationType::class),
            $entityManager->getRepository(CompanyType::class),
            $entityManager->getRepository(OrganisationContactType::class),
            new OrganisationMapper(),
            $entityManager->getRepository(AuthForAeStatus::class),
            $serviceLocator->get(XssFilter::class),
            $entityManager->getRepository(AuthorisationForAuthorisedExaminer::class),
            new AuthorisedExaminerValidator(),
            new DateTimeHolder(),
            $entityManager->getRepository(Site::class)
        );
    }
}
