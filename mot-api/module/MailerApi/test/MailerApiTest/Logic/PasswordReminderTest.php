<?php

namespace MailerApiTest\Logic;

use DvsaCommon\Dto\Mailer\MailerDto;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use DvsaMotApi\Service\UserService;
use MailerApi\Logic\PasswordReminder;
use MailerApi\Service\MailerService;
use MailerApi\Validator\MailerValidator;
use PHPUnit_Framework_TestCase;

class PasswordReminderTest extends PHPUnit_Framework_TestCase
{
    protected $validator;
    protected $mockUserService;
    protected $mockMailerService;
    protected $mailDto;
    protected $config;

    public function setUp()
    {
        $this->mockUserService = XMock::of(UserService::class, ['findPerson']);
        $this->validator = new MailerValidator($this->mockUserService);

        $this->mockMailerService = XMock::of(
            MailerService::class,
            ['send', 'validate']
        );

        // Turn off the logging feature
        $this->config = ['mailer' => [], 'helpdesk' => []];
        $this->config['mailer']['logfile'] = '';
        $this->config['mailer']['sendingAllowed'] = false;
    }

    public function testSendingWithUserId()
    {
        // Must pass validating the user-id first
        $mockPerson = XMock::of(Person::class, ['getId', 'getEmail']);
        $this->mockMailerService->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $this->mockMailerService->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $this->mailDto = new MailerDto();
        $this->mailDto->setData(['userid' => 5, 'user' => $mockPerson]);

        $emailAddress = 'passwordremindertest@'.EmailAddressValidator::TEST_DOMAIN;

        $logic = new PasswordReminder(
            $this->config['mailer'],
            $this->config['helpdesk'],
            $this->mockMailerService,
            $this->mailDto,
            $emailAddress
        );

        $this->assertTrue($logic->send());
    }
}
