<?php

namespace DvsaMotApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\NumbProbe;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\ConfigurationRepository;
use DvsaMotApi\Service\TesterExpiryService;

/**
 * Class TesterExpiryServiceTest.
 */
class TesterExpiryServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testChangeStatusOfInactiveTesters()
    {
        $authService = XMock::of(\DvsaAuthorisation\Service\AuthorisationServiceInterface::class);
        $cfgRepository = XMock::of(ConfigurationRepository::class);

        $service = new TesterExpiryService($this->emMock(), $authService, $cfgRepository);

        $service->changeStatusOfInactiveTesters();
    }

    private function emMock()
    {
        $conn = XMock::of(\Doctrine\DBAL\Connection::class);
        $conn->expects($this->any())->method('prepare')
            ->will($this->returnValue(new NumbProbe()));

        $em = XMock::of(EntityManager::class);
        $em->expects($this->any())->method('getConnection')
            ->will($this->returnValue($conn));

        return $em;
    }
}
