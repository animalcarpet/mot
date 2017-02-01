<?php
namespace MailerApiTest\Logic;

use DvsaCommon\Dto\Mailer\MailerDto;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Entity\Person;
use DvsaMotApi\Service\UserService;
use MailerApi\Logic\ClaimAccountReminder;
use MailerApi\Service\MailerService;
use MailerApi\Validator\MailerValidator;
use PHPUnit_Framework_TestCase;
use Zend\Log\Logger;

class ClaimAccountReminderTest extends PHPUnit_Framework_TestCase
{
    protected $validator;
    protected $mockUserService;
    protected $mockMailerService;
    protected $mailDto;
    protected $config;
    protected $authenticationMethod;

    public function setUp()
    {
        $this->mockUserService = XMock::of(UserService::class, ['findPerson']);
        $this->validator = new MailerValidator($this->mockUserService);
        $this->authenticationMethod = XMock::of(AuthenticationMethod::class);

        $this->mockMailerService = XMock::of(
            MailerService::class,
            ['send', 'validate']
        );

        // Turn off the logging feature
        $this->config = [
            'mailer' => [],
            'helpdesk' => []
        ];
        $this->config['mailer']['logfile'] = '';
        $this->config['mailer']['sendingAllowed'] = false;
    }

    public function testSendingWithUserId()
    {
        // Must pass validating the user-id first
        $mockPerson = XMock::of(Person::class, ['getId', 'getEmail', 'isCard']);
        $this->mockMailerService->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $this->mockMailerService->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $this->mailDto = new MailerDto();
        $this->mailDto->setData(['userid' => 5, 'user' => $mockPerson]);

        $emailAddress = 'claimaccountremindertest@' . EmailAddressValidator::TEST_DOMAIN;

        $logic = new ClaimAccountReminder(
            $this->config['mailer'],
            $this->config['helpdesk'],
            $this->mockMailerService,
            $this->mailDto,
            $emailAddress
        );

        $this->assertTrue($logic->send());
    }

    public function test2FAUserReclaimAccount_viaCSCO_shouldSendCustomised2FAEmail()
    {
        // Must pass validating the user-id first
        $mockPerson = XMock::of(Person::class, ['getId', 'getEmail']);
        $this->mockMailerService->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $this->mockMailerService->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $this->authenticationMethod
            ->expects($this->any())
            ->method('isCard')
            ->willReturn(true);

        $this->mailDto = new MailerDto();
        $this->mailDto->setData(['userid' => 5, 'user' => $mockPerson]);

        $emailAddress = 'claimaccountremindertest@' . EmailAddressValidator::TEST_DOMAIN;

        $logic = new ClaimAccountReminder(
            $this->config['mailer'],
            $this->config['helpdesk'],
            $this->mockMailerService,
            $this->mailDto,
            $emailAddress
        );

        $this->assertTrue($logic->send());
    }
}
