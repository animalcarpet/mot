<?php

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Repository\MotTestReasonForRejectionLocationRepository;
use DvsaEntities\Repository\MotTestReasonForRejectionRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\ReasonForRejectionTypeRepository;
use DvsaEntities\Repository\RfrRepository;
use DvsaMotApi\Service\Helper\BrakeTestResultsHelper;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
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

        /** @var ApiPerformMotTestAssertion $performMotTestAssertion */
        $performMotTestAssertion = $serviceLocator->get(ApiPerformMotTestAssertion::class);

        /** @var MotTestRepository $motTestRepository */
        $motTestRepository = $serviceLocator->get(MotTestRepository::class);

        /** @var RfrRepository $rfrRepository */
        $rfrRepository = $serviceLocator->get('RfrRepository');

        /** @var MotTestReasonForRejectionRepository $motTestReasonForRejectionRepository */
        $motTestReasonForRejectionRepository = $serviceLocator->get(MotTestReasonForRejectionRepository::class);

        /** @var MotTestReasonForRejectionLocationRepository $motTestReasonForRejectionLocationRepository */
        $motTestReasonForRejectionLocationRepository
            = $serviceLocator->get(MotTestReasonForRejectionLocationRepository::class);

        /** @var ReasonForRejectionTypeRepository $reasonForRejectionTypeRepository */
        $reasonForRejectionTypeRepository
            = $serviceLocator->get(ReasonForRejectionTypeRepository::class);

        /** @var BrakeTestResultsHelper $brakeTestResultsHelper */
        $brakeTestResultsHelper = new BrakeTestResultsHelper($entityManager);

        return new MotTestReasonForRejectionService(
            $entityManager,
            $authService,
            $motTestValidator,
            $performMotTestAssertion,
            $motTestRepository,
            $rfrRepository,
            $motTestReasonForRejectionRepository,
            $motTestReasonForRejectionLocationRepository,
            $reasonForRejectionTypeRepository,
            $brakeTestResultsHelper
        );
    }
}
