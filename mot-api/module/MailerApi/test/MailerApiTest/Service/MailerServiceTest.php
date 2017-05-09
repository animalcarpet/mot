<?php

namespace MailerApiTest\Service;

use DvsaCommon\Validator\EmailAddressValidator;
use MailerApi\Model\Attachment;
use MailerApi\Validator\MailerValidator;
use PHPUnit_Framework_TestCase;
use MailerApi\Service\MailerService;
use MailerApiTest\Mixin\ServiceManager;
use Zend\Log\Writer\Mail;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Mime\Message;
use Zend\Validator\EmailAddress;

class MailerServiceTest extends PHPUnit_Framework_TestCase
{
    use ServiceManager;

    /** @var MailerService */
    protected $mailerService;

    /** @var MailerValidator */
    protected $mockValidator;

    /**
     * @var EmailAddress
     */
    private $mockEmailValidator;

    public function setUp()
    {
        $this->prepServiceManager();
        $this->newEngineWithConfig([]);
    }

    public function newEngineWithConfig($config)
    {
        $this->setConfig($config);

        $this->mockValidator = $this
            ->getMockBuilder(MailerValidator::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();

        $this->mockValidator->expects($this->any())
            ->method('validate')
            ->willReturn(true);

        $this->mockEmailValidator = $this
            ->getMockBuilder(EmailAddressValidator::class)
            ->setMethods(['isValid'])
            ->getMock();

        $this->mockEmailValidator->expects($this->any())
            ->method('isValid')
            ->willReturn(true);

        $this->mailerService = new MailerService($config, null, $this->mockValidator, $this->mockEmailValidator);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage recipient
     */
    public function testRejectsNonStringRecipientWithException()
    {
        $this->mailerService->send(42, '', '');
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage recipient
     */
    public function testRejectsEmptyStringRecipientWithException()
    {
        $this->mailerService->send('', '', '');
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage subject
     */
    public function testRejectsNonStringSubjectWithException()
    {
        $this->mailerService->send('hello', -1, '');
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage subject
     */
    public function testRejectsEmptyStringSubjectWithException()
    {
        $this->mailerService->send('hello', '', '');
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage subject
     */
    public function testRejectsNonStringMessageWithException()
    {
        $this->mailerService->send('hello', '', []);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage subject
     */
    public function testRejectsEmptyMessageSubjectWithException()
    {
        $this->mailerService->send('hello', '', '');
    }

    public function testSendDoesNothingWhenConfiguredNotToSend()
    {
        $this->setConfig(['sendingAllowed' => false]);
        $this->mailerService = new MailerService($this->sm()->get('Config'), null, $this->mockValidator, $this->mockEmailValidator);

        $this->assertFalse(
            $this->mailerService->send(
                'mailerservicetest@'.EmailAddressValidator::TEST_DOMAIN,
                'test subject',
                'Hello, World!'
            )
        );
    }

    public function testSendsMessageWithCorrectlySuppliedDetails()
    {
        $mockMessage = 'Hello, World!';
        $mockMail = XMock::of(
            '\\Zend\\Mail\\Message',
            ['setFrom', 'setBody', 'setSubject', 'addTo']
        );

        $mockMail->expects($this->once())->method('setFrom')->with('a unit test');
        $mockMail->expects($this->once())->method('setBody')->with($this->callback(function ($body) use ($mockMessage) {
            return ($body instanceof Message) && ($body->getPartContent(0) == $mockMessage);
        }));
        $mockMail->expects($this->once())->method('setSubject')->with('test subject');
        $mockMail->expects($this->once())->method('addTo')->with('mailerservicetest@'.EmailAddressValidator::TEST_DOMAIN);

        $mockMta = XMock::of('\\Zend\\Mail\\Transport\\Sendmail', ['send']);

        $mockMta->expects($this->once())
            ->method('send')
            ->with($mockMail);

        $this->setConfig(['sendingAllowed' => true,
                'mta-class' => $mockMta,
                'mail-class' => $mockMail,
                'sender' => 'a unit test',
            ]
        );
        $this->mailerService = new MailerService($this->sm()->get('Config'), null, $this->mockValidator, $this->mockEmailValidator);

        $this->assertTrue(
            $this->mailerService->send(
                'mailerservicetest@'.EmailAddressValidator::TEST_DOMAIN,
                'test subject',
                $mockMessage
            )
        );
    }

    public function testGivenValidDataIsPassed_whenSendingAnEmailWithAnAttachment_shouldWorkCorrectly()
    {
        $mockMessage = 'Hello, World!';
        $mockAttachment = new Attachment('i am attached!', 'text/plain', 'nope.txt');
        $mockMail = XMock::of(
            '\\Zend\\Mail\\Message',
            ['setFrom', 'setBody', 'setSubject', 'addTo']
        );

        $mockMail->expects($this->once())->method('setFrom')->with('a unit test');
        $mockMail->expects($this->once())->method('setBody')->with($this->callback(function ($body) use ($mockMessage, $mockAttachment) {
            return ($body instanceof Message) && ($body->getPartContent(0) == $mockMessage)
            && (base64_decode($body->getPartContent(1)) == $mockAttachment->getContent()); //the second part will be base64 encoded
        }));
        $mockMail->expects($this->once())->method('setSubject')->with('test subject');
        $mockMail->expects($this->once())->method('addTo')->with('mailerservicetest@'.EmailAddressValidator::TEST_DOMAIN);

        $mockMta = XMock::of('\\Zend\\Mail\\Transport\\Sendmail', ['send']);

        $mockMta->expects($this->once())
            ->method('send')
            ->with($mockMail);

        $this->setConfig(['sendingAllowed' => true,
                'mta-class' => $mockMta,
                'mail-class' => $mockMail,
                'sender' => 'a unit test',
            ]
        );
        $this->mailerService = new MailerService($this->sm()->get('Config'), null, $this->mockValidator, $this->mockEmailValidator);

        $this->assertTrue(
            $this->mailerService->send(
                'mailerservicetest@'.EmailAddressValidator::TEST_DOMAIN,
                'test subject',
                $mockMessage,
                $mockAttachment
            )
        );
    }
}
