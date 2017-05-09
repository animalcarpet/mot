<?php

namespace SiteApiTest\Factory\Model;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Factory\Model\NominationVerifierFactory;
use SiteApi\Model\NominationVerifier;
use Zend\ServiceManager\ServiceManager;

class NominationVerifierFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $serviceManager;
    private $mockAuthService;

    public function setUp()
    {
        $this->mockAuthService = XMock::of(AuthorisationServiceInterface::class);

        $this->serviceManager = new ServiceManager();

        $this->serviceManager->setService(
            'DvsaAuthorisationService',
            $this->mockAuthService
        );
    }

    public function testCreateService()
    {
        $factory = new NominationVerifierFactory();

        $this->assertInstanceOf(
            NominationVerifier::class,
            $factory->createService($this->serviceManager)
        );
    }
}
