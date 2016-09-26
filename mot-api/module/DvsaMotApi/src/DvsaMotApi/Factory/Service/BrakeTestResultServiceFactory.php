<?php

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaEntities\Entity\BrakeTestType;
use DvsaEntities\Entity\WeightSource;
use DvsaMotApi\Mapper\BrakeTestResultClass12Mapper;
use DvsaMotApi\Mapper\BrakeTestResultClass3AndAboveMapper;
use DvsaMotApi\Service\BrakeTestResultService;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass1And2Calculator;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculator;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for BrakeTestResultService
 */
class BrakeTestResultServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $em = $serviceLocator->get(EntityManager::class);
        $brakeTestTypeRepository = $em->getRepository(BrakeTestType::class);
        $weightSourceRepository = $em->getRepository(WeightSource::class);

        $brakeTestResultValidator = $serviceLocator->get('BrakeTestResultValidator');
        $brakeTestConfigurationValidator = $serviceLocator->get('BrakeTestConfigurationValidator');
        $objectHydrator = $serviceLocator->get('Hydrator');
        $authService = $serviceLocator->get('DvsaAuthorisationService');
        $motTestValidator = $serviceLocator->get('MotTestValidator');
        $motTestReasonForRejectionService = $serviceLocator->get(MotTestReasonForRejectionService::class);
        $performMotTestAssertion = $serviceLocator->get(ApiPerformMotTestAssertion::class);

        return new BrakeTestResultService(
            $em,
            $brakeTestResultValidator,
            $brakeTestConfigurationValidator,
            $objectHydrator,
            new BrakeTestResultClass3AndAboveCalculator(),
            new BrakeTestResultClass1And2Calculator(),
            new BrakeTestResultClass3AndAboveMapper($brakeTestTypeRepository, $weightSourceRepository),
            new BrakeTestResultClass12Mapper($brakeTestTypeRepository),
            $authService,
            $motTestValidator,
            $motTestReasonForRejectionService,
            $performMotTestAssertion
        );
    }
}
