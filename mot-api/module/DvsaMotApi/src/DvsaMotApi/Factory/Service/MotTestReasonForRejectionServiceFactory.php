<?php

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaEntities\Repository\MotTestRepository;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use DvsaMotApi\Service\TestItemSelectorService;
use DvsaMotApi\Service\Validator\MotTestValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class MotTestReasonForRejectionServiceFactory.
 */
class MotTestReasonForRejectionServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return MotTestReasonForRejectionService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        /** @var AuthorisationServiceInterface $authService */
        $authService = $serviceLocator->get('DvsaAuthorisationService');

        /** @var MotTestValidator $motTestValidator */
        $motTestValidator = $serviceLocator->get('MotTestValidator');

        /** @var TestItemSelectorService $motTestItemSelectorService */
        $motTestItemSelectorService = $serviceLocator->get('TestItemSelectorService');

        /** @var ApiPerformMotTestAssertion $performMotTestAssertion */
        $performMotTestAssertion = $serviceLocator->get(ApiPerformMotTestAssertion::class);

        /** @var MotTestRepository $motTestRepository */
        $motTestRepository = $serviceLocator->get(MotTestRepository::class);

        return new MotTestReasonForRejectionService
        (
            $entityManager,
            $authService,
            $motTestValidator,
            $motTestItemSelectorService,
            $performMotTestAssertion,
            $motTestRepository
        );
    }
}
