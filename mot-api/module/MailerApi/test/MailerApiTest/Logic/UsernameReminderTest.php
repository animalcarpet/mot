<?php

namespace MailerApiTest\Logic;

use DvsaCommon\Dto\Mailer\MailerDto;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use DvsaMotApi\Service\UserService;
use MailerApi\Logic\UsernameReminder;
use MailerApi\Service\MailerService;
use MailerApi\Validator\MailerValidator;
use PersonApi\Service\PersonalDetailsService;
use PHPUnit_Framework_TestCase;

class UsernameReminderTest extends PHPUnit_Framework_TestCase
{
    protected $validator;
    protected $mockUserService;
    protected $mockDetailsService;
    protected $mockMailerService;
    protected $mailDto;
    protected $logic;
    protected $config;

    public function setUp()
    {
        $this->mockUserService = XMock::of(UserService::class, ['findPerson']);
        $this->validator = new MailerValidator($this->mockUserService);

        $this->mockDetailsService = XMock::of(PersonalDetailsService::class, ['get']);

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
        $mockPerson->expects($this->once())->method('getId')->willReturn(5);
        // Note: overloading mock with getEmail to avoid yet another mock object
        $mockPerson->expects($this->once())->method('getEmail')->willReturn('blah');

        $this->mockDetailsService->expects($this->once())
            ->method('get')
            ->willReturn($mockPerson);

        $this->mockMailerService->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $this->mockMailerService->expects($this->once())
            ->method('send')
            ->willReturn(false);

        $this->mailDto = new MailerDto();
        $this->mailDto->setData(['userid' => 5, 'user' => $mockPerson]);

        $this->logic = new UsernameReminder(
            $this->config['mailer'],
            $this->config['helpdesk'],
            $this->mockMailerService,
            $this->mockDetailsService,
            $this->mailDto
        );

        // Config inhibits sending we should get false here not true
        $this->assertFalse($this->logic->send());
    }
}
