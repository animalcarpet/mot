<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthentication\Service\OtpService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonApi\Filter\XssFilter;
use DvsaEntities\Entity\EnforcementFullPartialRetest;
use DvsaEntities\Entity\MotTestReasonForCancel;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\MotTestDateHelperService;
use DvsaMotApi\Service\MotTestStatusChangeService;
use DvsaMotApi\Service\Validator\MotTestStatusChangeValidator;
use OrganisationApi\Service\OrganisationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class MotTestStatusChangeServiceFactory.
 */
class MotTestStatusChangeServiceFactory implements FactoryInterface
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return \DvsaMotApi\Service\MotTestStatusChangeService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new MotTestStatusChangeService(
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('MotTestValidator'),
            $serviceLocator->get(MotTestStatusChangeValidator::class),
            $serviceLocator->get(OtpService::class),
            $serviceLocator->get(OrganisationService::class),
            $serviceLocator->get('MotTestMapper'),
            $serviceLocator->get(MotTestRepository::class),
            $entityManager->getRepository(MotTestReasonForCancel::class),
            $entityManager->getRepository(EnforcementFullPartialRetest::class),
            $serviceLocator->get(MotTestDateHelperService::class),
            $entityManager,
            $serviceLocator->get(MotIdentityProviderInterface::class),
            $serviceLocator->get(ApiPerformMotTestAssertion::class),
            $serviceLocator->get(XssFilter::class)
        );
    }
}
