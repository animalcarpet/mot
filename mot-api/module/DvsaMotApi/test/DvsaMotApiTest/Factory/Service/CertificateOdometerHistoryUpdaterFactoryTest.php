<?php

namespace DvsaMotApiTest\Factory;


use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use DvsaEntities\Repository\CertificateReplacementRepository;
use DvsaEntities\Repository\CertificateTypeRepository;
use DvsaEntities\Repository\MotTestHistoryRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Factory\Service\CertificateOdometerHistoryUpdaterFactory;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\Mapper\MotTestMapper;
use DvsaMotApi\Service\ReplacementCertificate\CertificateOdometerHistoryUpdater;

class CertificateOdometerHistoryUpdaterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesAppropriateServiceInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            CertificateOdometerHistoryUpdaterFactory::class,
            CertificateOdometerHistoryUpdater::class,
            [
                MotTestRepository::class => MotTestHistoryRepository::class,
                CertificateTypeRepository::class => CertificateTypeRepository::class,
                CertificateCreationService::class => CertificateCreationService::class,
                MotTestMapper::class => MotTestMapper::class,
                MysteryShopperHelper::class => MysteryShopperHelper::class,
                CertificateReplacementRepository::class => CertificateReplacementRepository::class,
                MotIdentityProviderInterface::class => MotIdentityProviderInterface::class
            ]
        );
    }
}