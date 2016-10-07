<?php

namespace DvsaMotApi\Factory\Service\Mapper;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\Utility\Hydrator;
use DvsaMotApi\Formatting\DefectSentenceCaseConverter;
use DvsaMotApi\Service\BrakeTestResultService;
use DvsaMotApi\Service\CertificateExpiryService;
use DvsaMotApi\Service\Mapper\MotTestMapper;
use DvsaMotApi\Service\MotTestDateHelperService;
use DvsaMotApi\Service\MotTestStatusService;
use VehicleApi\Service\VehicleSearchService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create an instance of MotTestMapper.
 *
 * @package DvsaMotApi\Factory\Service\Mapper
 */
class MotTestMapperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var DoctrineObject $objectHydrator */
        $objectHydrator = $serviceLocator->get('Hydrator');

        /** @var BrakeTestResultService $brakeTestResultService */
        $brakeTestResultService = $serviceLocator->get('BrakeTestResultService');

        /** @var VehicleSearchService $vehicleService */
        $vehicleService = $serviceLocator->get(VehicleSearchService::class);

        /** @var CertificateExpiryService $certificateExpiryService */
        $certificateExpiryService = $serviceLocator->get('CertificateExpiryService');

        /** @var MotTestStatusService $motTestStatusService */
        $motTestStatusService = $serviceLocator->get('MotTestStatusService');

        /** @var MotTestDateHelperService $motTestDateHelperService */
        $motTestDateHelperService = $serviceLocator->get(MotTestDateHelperService::class);

        /** @var ParamObfuscator $paramObfuscator */
        $paramObfuscator = $serviceLocator->get(ParamObfuscator::class);

        /** @var DefectSentenceCaseConverter $defectSentenceCaseConverter */
        $defectSentenceCaseConverter = $serviceLocator->get(DefectSentenceCaseConverter::class);

        return new MotTestMapper($objectHydrator, $brakeTestResultService, $vehicleService, $certificateExpiryService,
            $motTestStatusService, $motTestDateHelperService, $paramObfuscator, $defectSentenceCaseConverter);
    }
}
