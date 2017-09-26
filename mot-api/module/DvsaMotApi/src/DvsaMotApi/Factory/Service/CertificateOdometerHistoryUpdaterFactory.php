<?php

namespace DvsaMotApi\Factory\Service;

use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaEntities\Repository\CertificateReplacementRepository;
use DvsaEntities\Repository\CertificateTypeRepository;
use DvsaEntities\Repository\MotTestHistoryRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\Mapper\MotTestMapper;
use DvsaMotApi\Service\ReplacementCertificate\CertificateOdometerHistoryUpdater;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;


class CertificateOdometerHistoryUpdaterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotTestHistoryRepository $motTestHistoryRepository */
        $motTestHistoryRepository = $serviceLocator->get(MotTestRepository::class);
        /** @var CertificateTypeRepository $certificateTypeRepository */
        $certificateTypeRepository = $serviceLocator->get(CertificateTypeRepository::class);
        /** @var CertificateCreationService $certificateCreationService */
        $certificateCreationService = $serviceLocator->get(CertificateCreationService::class);
        /** @var MotTestMapper $motTestMapper */
        $motTestMapper = $serviceLocator->get(MotTestMapper::class);
        /** @var MysteryShopperHelper $mysteryShopperHelper */
        $mysteryShopperHelper = $serviceLocator->get(MysteryShopperHelper::class);
        /** @var CertificateReplacementRepository $certificateReplacementRepository */
        $certificateReplacementRepository = $serviceLocator->get(CertificateReplacementRepository::class);
        /** @var MotIdentityProviderInterface $identityProvider */
        $identityProvider = $serviceLocator->get(MotIdentityProviderInterface::class);


        return new CertificateOdometerHistoryUpdater(
            $motTestHistoryRepository,
            $certificateTypeRepository,
            $certificateCreationService,
            $motTestMapper,
            $mysteryShopperHelper,
            $certificateReplacementRepository,
            $identityProvider
        );
    }

}