<?php

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaEntities\Entity\BrakeTestType;
use DvsaEntities\Entity\WeightSource;
use DvsaEntities\Repository\BrakeTestTypeRepository;
use DvsaEntities\Repository\WeightSourceRepository;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Mapper\BrakeTestResultClass12Mapper;
use DvsaMotApi\Mapper\BrakeTestResultClass3AndAboveMapper;
use DvsaMotApi\Mapper\ParkingBrakeClass3AndAboveRfrMapper;
use DvsaMotApi\Service\BrakeTestResultService;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass1And2Calculator;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculator;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use DvsaMotApi\Service\Validator\BrakeTestConfigurationValidator;
use DvsaMotApi\Service\Validator\BrakeTestResultValidator;
use DvsaMotApi\Service\Validator\MotTestValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for BrakeTestResultService.
 */
class BrakeTestResultServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $em */
        $em = $serviceLocator->get(EntityManager::class);
        /** @var BrakeTestTypeRepository $brakeTestTypeRepository */
        $brakeTestTypeRepository = $em->getRepository(BrakeTestType::class);
        /** @var WeightSourceRepository $weightSourceRepository */
        $weightSourceRepository = $em->getRepository(WeightSource::class);

        /** @var BrakeTestResultValidator $brakeTestResultValidator */
        $brakeTestResultValidator = $serviceLocator->get('BrakeTestResultValidator');
        /** @var BrakeTestConfigurationValidator $brakeTestConfigurationValidator */
        $brakeTestConfigurationValidator = $serviceLocator->get('BrakeTestConfigurationValidator');
        /** @var DoctrineObject $objectHydrator */
        $objectHydrator = $serviceLocator->get('Hydrator');
        /** @var AuthorisationServiceInterface $authService */
        $authService = $serviceLocator->get('DvsaAuthorisationService');
        /** @var MotTestValidator $motTestValidator */
        $motTestValidator = $serviceLocator->get('MotTestValidator');
        /** @var MotTestReasonForRejectionService $motTestReasonForRejectionService */
        $motTestReasonForRejectionService = $serviceLocator->get(MotTestReasonForRejectionService::class);
        /** @var ApiPerformMotTestAssertion $performMotTestAssertion */
        $performMotTestAssertion = $serviceLocator->get(ApiPerformMotTestAssertion::class);
        /** @var FeatureToggles $featureToggles */
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');
        /** @var BrakeTestResultClass3AndAboveCalculator $brakeTestResultClass3AndAboveCalculator */
        $brakeTestResultClass3AndAboveCalculator = $serviceLocator->get(BrakeTestResultClass3AndAboveCalculator::class);
        return new BrakeTestResultService(
            $em,
            $brakeTestResultValidator,
            $brakeTestConfigurationValidator,
            $objectHydrator,
            $brakeTestResultClass3AndAboveCalculator,
            new BrakeTestResultClass1And2Calculator(),
            new BrakeTestResultClass3AndAboveMapper($brakeTestTypeRepository, $weightSourceRepository),
            new BrakeTestResultClass12Mapper($brakeTestTypeRepository),
            $authService,
            $motTestValidator,
            $motTestReasonForRejectionService,
            $performMotTestAssertion,
            $weightSourceRepository,
            $featureToggles
        );
    }
}
