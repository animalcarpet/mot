<?php

namespace UserApiTest\HelpDesk\Service;

use AccountApi\Service\Exception\OpenAmChangePasswordException;
use AccountApi\Service\OpenAmIdentityService;
use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use DvsaEventApi\Service\EventService;
use MailerApi\Service\MailerService;
use UserApi\HelpDesk\Service\ResetClaimAccountService;
use DvsaCommonTest\Bootstrap;

/**
 * Unit tests for ResetClaimAccountServiceTest.
 */
class ResetClaimAccountServiceTest extends AbstractServiceTestCase
{
    const PERSON_ID = 3;
    const PERSON_USERNAME = 'csco';
    const PERSON_EMAIL = MailerService::AWS_MAIL_SIMULATOR_SUCCESS;

    /** @var ResetClaimAccountService */
    private $service;
    /** @var EntityManager */
    private $mockEntityManager;
    /** @var PersonRepository */
    private $mockPersonRepo;
    /** @var MailerService */
    private $mockMailerService;
    /** @var OpenAmIdentityService */
    private $mockOpenAmIdentityService;
    /** @var EventService */
    private $mockEventService;
    /** @var AuthorisationServiceInterface */
    private $mockAuthService;
    /** @var array */
    private $mockConfig;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        //  --  mock repositories   --
        $this->mockEntityManager = $this->getMockEntityManager();

        $this->mockPersonRepo = XMock::of(PersonRepository::class);
        $this->mockMailerService = XMock::of(MailerService::class);
        $this->mockOpenAmIdentityService = XMock::of(OpenAmIdentityService::class);
        $this->mockEventService = XMock::of(EventService::class);
        $this->mockAuthService = XMock::of(AuthorisationServiceInterface::class);

        //  --  mock config --
        $this->mockConfig = $serviceManager->get('Config');
        $this->mockConfig['helpdesk'] = [
            'name' => 'TEST HELPDESK',
            'phoneNumber' => '42424242',
        ];

        //  --  create service instance --
        $this->service = new ResetClaimAccountService(
            $this->mockEntityManager,
            $this->mockPersonRepo,
            $this->mockMailerService,
            $this->mockOpenAmIdentityService,
            $this->mockEventService,
            $this->mockAuthService,
            $this->mockConfig,
            new DateTimeHolder()
        );

        $this->mockAuthService->expects($this->any())
            ->method('assertGranted')
            ->willReturn(true);
    }

    public function testResetClaimAccount()
    {
        $mockPerson = XMock::of(Person::class);
        $mockAuthenticationMethod = XMock::of(AuthenticationMethod::class);

        $this->mockPersonRepo->expects($this->once())
            ->method('get')
            ->with(self::PERSON_ID)
            ->willReturn($mockPerson);
        $mockPerson->expects($this->once())
            ->method('getPrimaryEmail')
            ->willReturn(self::PERSON_EMAIL);
        $this->mockOpenAmIdentityService->expects($this->once())
            ->method('changePassword')
            ->willReturn(true);
        $mockPerson->expects($this->any())
            ->method('getAuthenticationMethod')
            ->willReturn($mockAuthenticationMethod);

        $this->assertTrue($this->service->resetClaimAccount(self::PERSON_ID, self::PERSON_USERNAME));
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testResetClaimAccountNoEmail()
    {
        $mockPerson = XMock::of(Person::class);
        $this->mockPersonRepo->expects($this->once())
            ->method('get')
            ->with(self::PERSON_ID)
            ->willReturn($mockPerson);
        $mockPerson->expects($this->once())
            ->method('getPrimaryEmail')
            ->willReturn(null);

        $this->service->resetClaimAccount(self::PERSON_ID, self::PERSON_USERNAME);
    }

    /**
     * @expectedException \AccountApi\Service\Exception\OpenAmChangePasswordException
     */
    public function testResetClaimAccountFailPasswordChange()
    {
        $mockPerson = XMock::of(Person::class);
        $this->mockPersonRepo->expects($this->once())
            ->method('get')
            ->with(self::PERSON_ID)
            ->willReturn($mockPerson);
        $mockPerson->expects($this->once())
            ->method('getPrimaryEmail')
            ->willReturn(self::PERSON_EMAIL);
        $this->mockOpenAmIdentityService->expects($this->once())
            ->method('changePassword')
            ->willThrowException(new OpenAmChangePasswordException('error'));

        $this->service->resetClaimAccount(self::PERSON_ID, self::PERSON_USERNAME);
    }
}
