<?php

namespace SiteApiTest\Service\Factory;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Factory\Service\EnforcementSiteAssessmentValidatorFactory;
use SiteApi\Service\Validator\EnforcementSiteAssessmentValidator;
use Zend\ServiceManager\ServiceManager;

class EnforcementSiteAssessmentValidatorFactoryTest extends AbstractServiceTestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $entityManager = XMock::of(EntityManager::class);
        $serviceManager->setService(EntityManager::class, $entityManager);

        $config = XMock::of(MotConfig::class);
        $this->mockMethod(
            $config,
            'get',
            $this->at(0),
            0,
            ['site_assessment', 'green', 'start']
        );
        $this->mockMethod(
            $config,
            'get',
            $this->at(1),
            999.99,
            ['site_assessment', 'red', 'end']
        );

        $serviceManager->setService(MotConfig::class, $config);

        $factory = new EnforcementSiteAssessmentValidatorFactory();

        $this->assertInstanceOf(
            EnforcementSiteAssessmentValidator::class,
            $factory->createService($serviceManager)
        );
    }
}
