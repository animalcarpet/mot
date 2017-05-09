<?php

namespace PersonApiTest\Controller;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use PersonApi\Controller\ResetClaimAccountController;
use UserApi\HelpDesk\Service\ResetClaimAccountService;

/**
 * Class ResetClaimAccountControllerTest.
 */
class ResetClaimAccountControllerTest extends AbstractRestfulControllerTestCase
{
    const PERSON_ID = 1;

    private $mockEntity;
    private $service;
    private $auth;

    protected function setUp()
    {
        $this->mockEntity = XMock::of(EntityManager::class);
        $this->service = XMock::of(ResetClaimAccountService::class);

        $this->auth = XMock::of(AuthorisationServiceInterface::class, ['getIdentity']);

        $this->setController(new ResetClaimAccountController($this->mockEntity, $this->service));

        parent::setUp();

        $this->serviceManager->setService(ResetClaimAccountService::class, $this->service);
        $this->serviceManager->setService('DvsaAuthenticationService', $this->auth);
    }

    public function testResetClaimAccountControllerGet()
    {
        $identity = XMock::of(Person::class);

        $this->service->expects($this->once())
            ->method('resetClaimAccount')
            ->willReturn(true);

        $this->mockEntity->expects($this->once())
            ->method('flush')
            ->willReturn(true);

        $this->auth->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        $identity->expects($this->once())
            ->method('getUsername')
            ->willReturn('tester1');

        $result = $this->getResultForAction('get', null, ['id' => self::PERSON_ID]);
        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => true], $result);
    }
}
