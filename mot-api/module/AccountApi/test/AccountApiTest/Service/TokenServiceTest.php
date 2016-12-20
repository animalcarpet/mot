<?php

namespace AccountApiTest\Service;

use AccountApi\Service\Exception\OpenAmChangePasswordException;
use AccountApi\Service\Mapper\MessageMapper;
use AccountApi\Service\OpenAmIdentityService;
use AccountApi\Service\TokenService;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Dto\Account\MessageDto;
use DvsaCommon\Enum\MessageTypeCode;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Message;
use DvsaEntities\Entity\MessageType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Entity\PersonContactType;
use DvsaEntities\Repository\MessageRepository;
use DvsaEntities\Repository\MessageTypeRepository;
use DvsaEntities\Repository\PersonRepository;
use MailerApi\Logic\AbstractMailerLogic;
use MailerApi\Service\MailerService;
use Zend\Log\LoggerInterface;
use Zend\ServiceManager\ServiceManager;
use DvsaAuthorisation\Service\AuthorisationService;
use Zend\Authentication\AuthenticationService;

class TokenServiceTest extends AbstractServiceTestCase
{
    const MESSAGE_ID = 9999;
    const TOKEN = 'd1c103e634efff7a54288b5cf8f7e57cd5566caaac5c9ca98d450c2de4b56805';
    const USER_ID = 8888;
    const USER_NAME = 'unit_userName1';
    const USER_EMAIL = 'success@simulator.amazonses.com';
    const USER_PASSWORD = 'Password123';

    const CFG_EXPIRE_TIME = 1000;
    const ISSUED_DATE_TS = 1428569861.654321;

    /**  @var TokenService */
    private $tokenService;

    /** @var \DvsaEntities\Repository\MessageRepository */
    private $mockMessageRepo;
    /** @var \DvsaEntities\Repository\MessageTypeRepository */
    private $mockMessageTypeRepo;
    /** @var \DvsaEntities\Repository\PersonRepository */
    private $mockPersonRepo;
    /** @var  EntityManager */
    private $mockEntityManager;
    /** @var  array */
    private $mockConfig;
    /** @var  DateTimeHolder */
    private $mockDateTimeHolder;
    /** @var OpenAmIdentityService */
    private $mockOpenAmIdentityService;
    /** @var  MailerService */
    private $mockMailerService;
    /** @var  ParamObfuscator */
    private $mockObfuscator;
    /** @var  AuthenticationService */
    private $authenticationService;
    /** @var  AuthorisationService */
    private $authorisationService;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        //  --  mock repositories   --
        $this->mockEntityManager = $this->getMockEntityManager();

        $this->mockMessageRepo = XMock::of(MessageRepository::class);
        $this->mockMessageTypeRepo = XMock::of(MessageTypeRepository::class);
        $this->mockPersonRepo = XMock::of(PersonRepository::class);
        $this->mockOpenAmIdentityService = XMock::of(OpenAmIdentityService::class);

        $this->mockMailerService = XMock::of(MailerService::class);
        $this->mockObfuscator = XMock::of(ParamObfuscator::class);

        $this->authenticationService = XMock::of(AuthenticationService::class);
        $this->authorisationService = XMock::of(AuthorisationService::class);

        //  --  mock config --
        $this->mockConfig = $serviceManager->get('Config');
        $this->mockConfig[TokenService::CFG_PASSWORD_RESET][TokenService::CFG_PASSWORD_RESET_EXPIRE_TIME]
            = self::CFG_EXPIRE_TIME;
        $this->mockConfig[TokenService::CFG_PASSWORD_RESET][TokenService::CFG_PASSWORD_RESET_SECRET]
            = 'secretdeoufmilediou';
        $this->mockConfig[TokenService::CFG_PASSWORD_RESET][TokenService::CFG_PASSWORD_RESET_HASH_METHOD]
            = 'sha256';
        $this->mockConfig[AbstractMailerLogic::CONFIG_KEY] = [
            'sendingAllowed' => true,
            'recipient'   => 'tokenservicetest@' . EmailAddressValidator::TEST_DOMAIN,
            AbstractMailerLogic::CONFIG_KEY_BASE_URL => 'http://mot-web-frontend.mot.gov.uk',
        ];
        $this->mockConfig[TokenService::CFG_HELPDESK] = [
            'name' => 'TEST HELPDESK',
            'phoneNumber' => '42424242'
        ];

        //  --  mock date time holder --
        $this->mockDateTimeHolder = XMock::of(DateTimeHolder::class, ['getTimestamp']);

        $this->mockMessageRepo->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue([]));

        //  --  create service instance --
        $this->tokenService = new TokenService(
            $this->mockEntityManager,
            $this->mockMessageRepo,
            $this->mockMessageTypeRepo,
            $this->mockPersonRepo,
            XMock::of(LoggerInterface::class),
            $this->mockMailerService,
            $this->mockOpenAmIdentityService,
            $this->mockConfig,
            $this->mockObfuscator,
            $this->authenticationService,
            $this->authorisationService
        );

        XMock::mockClassField($this->tokenService, 'dateTimeHolder', $this->mockDateTimeHolder);
    }

    /**
     * @dataProvider dataProviderTestEmailForgottenPassword
     */
    public function testEmailForgottenPassword($mocks, $expect)
    {
        if ($mocks !== null) {
            foreach ($mocks as $repo) {
                $invocation = ArrayUtils::tryGet($repo, 'invocation', $this->once());
                $params = ArrayUtils::tryGet($repo, 'params', null);

                $this->mockMethod(
                    $this->{$repo['class']}, $repo['method'], $invocation, $repo['result'], $params
                );
            }
        }

        //  --  set expected exception  --
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message']);
        }

        //  --  call    --
        $actual = $this->tokenService->createTokenAndEmailForgottenLink(self::USER_ID);

        $this->assertEquals($expect['result'], $actual);
    }

    public function dataProviderTestEmailForgottenPassword()
    {
        $person = $this->getMockPerson(true);
        $personNoMail = $this->getMockPerson(false);

        return [
            [
                'mocks'  => [
                    [
                        'class'  => 'mockPersonRepo',
                        'method' => 'getByIdOrUsername',
                        'params' => [self::USER_ID],
                        'result' => new NotFoundException('Person'),
                    ],
                ],
                'expect' => [
                    'exception' => [
                        'class'   => NotFoundException::class,
                        'message' => 'Person not found',
                    ],
                ],
            ],
            [
                'mocks'  => [
                    [
                        'class'  => 'mockPersonRepo',
                        'method' => 'getByIdOrUsername',
                        'params' => [self::USER_ID],
                        'result' => $person,
                    ],
                    [
                        'class'  => 'mockMessageTypeRepo',
                        'method' => 'getByCode',
                        'params' => [MessageTypeCode::PASSWORD_RESET_BY_EMAIL],
                        'result' => new NotFoundException('MessageType'),
                    ],
                ],
                'expect' => [
                    'exception' => [
                        'class'   => NotFoundException::class,
                        'message' => 'MessageType not found',
                    ],
                ],
            ],
            [
                'mocks'  => [
                    [
                        'class'  => 'mockPersonRepo',
                        'method' => 'getByIdOrUsername',
                        'params' => [self::USER_ID],
                        'result' => $person,
                    ],
                    [
                        'class'  => 'mockMessageTypeRepo',
                        'method' => 'getByCode',
                        'params' => [MessageTypeCode::PASSWORD_RESET_BY_EMAIL],
                        'result' => new MessageType(),
                    ],
                    [
                        'class'      => 'mockDateTimeHolder',
                        'method'     => 'getTimestamp',
                        'params'     => [true],
                        'invocation' => $this->any(),
                        'result'     => self::ISSUED_DATE_TS,
                    ],
                    [
                        'class'      => 'mockMessageRepo',
                        'method'     => 'getHydratedMessageByToken',
                        'params'     => [self::TOKEN],
                        'invocation' => $this->any(),
                        'result'     => (new Message()),
                    ],
                    [
                        'class'      => 'mockMessageRepo',
                        'method'     => 'findBy',
                        'params'     => [['person' => $person, 'isAcknowledged' => false]],
                        'invocation' => $this->any(),
                        'result'     => [(new Message())],
                    ],
                ],
                'expect' => [
                    'exception' => [
                        'class'   => \Exception::class,
                        'message' => TokenService::ERROR_GENERATION_TOKEN,
                    ],
                ],
            ],
            [
                'mocks'  => [
                    [
                        'class'  => 'mockPersonRepo',
                        'method' => 'getByIdOrUsername',
                        'params' => [self::USER_ID],
                        'result' => $person,
                    ],
                    [
                        'class'  => 'mockMessageTypeRepo',
                        'method' => 'getByCode',
                        'params' => [MessageTypeCode::PASSWORD_RESET_BY_EMAIL],
                        'result' => new MessageType(),
                    ],
                    [
                        'class'      => 'mockDateTimeHolder',
                        'method'     => 'getTimestamp',
                        'params'     => [true],
                        'invocation' => $this->once(),
                        'result'     => self::ISSUED_DATE_TS,
                    ],
                    [
                        'class'      => 'mockMessageRepo',
                        'method'     => 'getHydratedMessageByToken',
                        'params'     => [self::TOKEN],
                        'invocation' => $this->once(),
                        'result'     => new NotFoundException('Token not found'),
                    ],
                    [
                        'class'      => 'mockMessageRepo',
                        'method'     => 'findBy',
                        'params'     => [['person' => $person, 'isAcknowledged' => false]],
                        'invocation' => $this->any(),
                        'result'     => [(new Message())],
                    ],
                ],
                'expect' => [
                    'result' => $this->getMessageDto($person),
                ],
            ],
        ];
    }

    public function testAssertTokenIsValid()
    {
        //  --  mock repositories   --
        $message = new Message();
        $message->setId(self::MESSAGE_ID);

        $this->mockMethod(
            $this->mockMessageRepo, 'getHydratedMessageByToken', $this->once(), $message, [self::TOKEN, true]
        );

        //  --  call    --
        $actual = $this->tokenService->assertTokenIsValid(self::TOKEN);

        $this->assertEquals(self::MESSAGE_ID, $actual);
    }

    public function testGetToken()
    {
        //  --  mock --
        $onlyValid = false;
        $person = $this->getMockPerson();
        $message = $this->getMessage($person);
        $messageDto = $this->getMessageDto($person);

        $this->mockMethod(
            $this->mockMessageRepo, 'getHydratedMessageByToken', $this->once(), $message, [self::TOKEN, $onlyValid]
        );

        //  --  call    --
        $actual = $this->tokenService->getToken(self::TOKEN, $onlyValid);

        $this->assertInstanceOf(MessageDto::class, $actual);
        $this->assertEquals($messageDto, $actual);
    }

    protected function getMockPerson($withEmail = true)
    {
        $contactDetail = new ContactDetail();

        if ($withEmail === true) {
            $email = new Email();
            $email->setEmail(self::USER_EMAIL)->setIsPrimary(true);

            $contactDetail->addEmail($email);
        }

        $person = new Person();
        $person
            ->setId(self::USER_ID)
            ->setUsername(self::USER_NAME);

        $personType = new PersonContactType();
        $personType->setName(\DvsaCommon\Constants\PersonContactType::PERSONAL);

        $contact = new PersonContact($contactDetail, $personType, $person);

        $person->addContact($contact);

        return $person;
    }

    private function getMessage(Person $person, $issuedDateTs = null, $expiryDateTs = null)
    {
        //  --  mock dates  --
        if ($issuedDateTs === null) {
            $issuedDateTs = self::ISSUED_DATE_TS;
        }

        if ($expiryDateTs === null) {
            $expiryDateTs = $issuedDateTs + self::CFG_EXPIRE_TIME;
        }

        return (new Message())
            ->setId(self::MESSAGE_ID)
            ->setMessageType(
                (new MessageType())
            )
            ->setPerson($person)
            ->setIssueDate((new \DateTime())->setTimestamp($issuedDateTs))
            ->setExpiryDate((new \DateTime())->setTimestamp($expiryDateTs))
            ->setIsAcknowledged(false)
            ->setToken(self::TOKEN);
    }

    private function getMessageDto(Person $person, $issuedDateTs = null, $expiryDateTs = null)
    {
        $message  = $this->getMessage($person, $issuedDateTs, $expiryDateTs);
        return (new MessageMapper)->toDto($message);
    }

    public function testAcknowledge()
    {
        //  --  mock --
        $person = $this->getMockPerson();
        $message = $this->getMessage($person);

        $this->mockMethod(
            $this->mockMessageRepo, 'getHydratedMessageByToken', $this->any(), $message, [self::TOKEN]
        );

        //  --  call    --
        $this->tokenService->acknowledge(self::TOKEN);
    }

    public function testChangePassword()
    {
        //  --  mock --
        $onlyValid = true;
        $person = $this->getMockPerson();
        $message = $this->getMessage($person);

        $this->mockMethod(
            $this->mockMessageRepo, 'getHydratedMessageByToken', $this->any(), $message, [self::TOKEN, $onlyValid]
        );

        $this->mockMethod(
            $this->mockOpenAmIdentityService, 'changePassword', $this->once()
        );
        $this->mockMethod($this->mockOpenAmIdentityService, 'unlockAccount', $this->once(), true);

        //  --  call    --
        $actual = $this->tokenService->changePassword(self::TOKEN, self::USER_PASSWORD);

        $this->assertEquals($actual, ['success' => true]);
    }

    public function testChangePasswordThrowException()
    {
        //  --  mock --
        $onlyValid = true;
        $person = $this->getMockPerson();
        $message = $this->getMessage($person);

        $this->mockMethod(
            $this->mockMessageRepo, 'getHydratedMessageByToken', $this->any(), $message, [self::TOKEN, $onlyValid]
        );

        $exception = new OpenAmChangePasswordException('test');

        $this->mockMethod(
            $this->mockOpenAmIdentityService, 'changePassword', $this->once(), $exception
        );

        // The unlockAccount procedure is only performed after a successful password change
        $this->mockMethod($this->mockOpenAmIdentityService, 'unlockAccount', $this->never(), true);

        $this->setExpectedException(OpenAmChangePasswordException::class);
        //  --  call    --
        $this->tokenService->changePassword(self::TOKEN, self::USER_PASSWORD);

    }
}
