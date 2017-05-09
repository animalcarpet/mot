<?php

use AccountApi\Service\OpenAmIdentityService;
use DvsaAuthentication\Identity;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\MessageTypeCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Message;
use DvsaEntities\Entity\MessageType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\MessageRepository;
use DvsaEntities\Repository\MessageTypeRepository;
use DvsaEntities\Repository\PersonRepository;
use UserApi\Message\Service\MessageService;
use UserApi\Message\Service\Validator\MessageValidator;
use DvsaCommonApi\Service\Exception\NotFoundException;

class MessageServiceTest extends AbstractServiceTestCase
{
    protected $mockEntityManager;

    /** @var MessageRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockMessageRepo;

    /** @var MessageTypeRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockMessageTypeRepo;

    /** @var PersonRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockPersonRepository;

    /** @var MotAuthorisationServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockAuthorisationService;

    /** @var MessageValidator */
    protected $messageValidator;

    /** @var DateTimeHolder|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockDateTimeHolder;

    /** @var MessageService */
    protected $service;

    protected $openAMIdentityServiceMock;

    private $currentTime;

    protected function setUp()
    {
        $this->currentTime = new \DateTime('now');

        // Mock Entity Manager
        $this->mockEntityManager = $this->getMockEntityManager();

        $this->mockMessageRepo = XMock::of(
            MessageRepository::class,
            ['hasAlreadyRequestedMessage', 'persist', 'flush']
        );

        // Mock Message Repository
        $this->mockMessageRepo->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(true));

        $this->mockMessageRepo->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(true));

        // Mock Message Type Repository
        $this->mockMessageTypeRepo = XMock::of(
            MessageTypeRepository::class,
            ['getByCode']
        );

        // Mock Person Repository
        $this->mockPersonRepository = XMock::of(PersonRepository::class);
        $this->mockPersonRepository->expects($this->any())
            ->method('get')
            ->with(4)
            ->will($this->returnValue($this->getMockPerson()->getPerson()));

        // Message Validator
        $this->messageValidator = new MessageValidator();

        //  --  mock date time holder --
        $this->mockDateTimeHolder = XMock::of(DateTimeHolder::class);

        $this->mockDateTimeHolder
            ->expects($this->any())
            ->method('getCurrent')
            ->will($this->returnValue($this->currentTime));

        $this->mockDateTimeHolder
            ->expects($this->any())
            ->method('getCurrentDate')
            ->will($this->returnValue($this->currentTime));

        // Mock AuthorisationService
        $this->mockAuthorisationService = XMock::of(MotAuthorisationServiceInterface::class);

        $this->openAMIdentityServiceMock = $this
            ->getMockBuilder(OpenAmIdentityService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = $this->getService();
    }

    private function getService($mockPersonRepository = false, $mockMessageRepo = false)
    {
        if ($mockPersonRepository) {
            $this->mockPersonRepository = $mockPersonRepository;
        }

        if ($mockMessageRepo) {
            $this->mockMessageRepo = $mockMessageRepo;
        }

        $this->service = new MessageService(
            $this->mockMessageRepo,
            $this->mockMessageTypeRepo,
            $this->mockPersonRepository,
            $this->mockAuthorisationService,
            $this->messageValidator,
            $this->mockDateTimeHolder,
            $this->openAMIdentityServiceMock
        );

        return $this->service;
    }

    public function testCreateMessageWithInvalidParameters()
    {
        $this->setExpectedException(RequiredFieldException::class, 'A required field is missing');

        $data = [
          'personId' => '',
            'mesageTypeCoder' => '', // Invalid Parameter
        ];

        $this->service->createMessage($data);
    }

    public function testCreateMessageWithInvalidPersonWillThrowException()
    {
        $this->setExpectedException(NotFoundException::class, 'Person '. 5 .' not found');
        $data = [
            'personId' => 5,
            'messageTypeCode' => MessageTypeCode::ACCOUNT_RESET_BY_LETTER,
        ];

        // Below doesn't exist according to our mocking
        $mockPersonRepository = XMock::of(PersonRepository::class);
        $mockPersonRepository->expects($this->once())
            ->method('get')
            ->with(5)
            ->will($this->throwException(new NotFoundException('Person '. 5 .' not found')));

        $service = $this->getService($mockPersonRepository);

        $service->createMessage($data);
    }

    public function testCreateMessage_givenAccountResetByLetterAndAlreadyRequested_shouldThrowError()
    {
        $this->setExpectedException(BadRequestException::class, "A request to re-set this user's account has already been made today");
        $messageType = new MessageType();
        $messageType->setCode(MessageTypeCode::ACCOUNT_RESET_BY_LETTER);

        //mock messageType get call
        $this->mockMessageTypeRepo->expects($this->any())
            ->method('getByCode')
            ->with(\DvsaCommon\Enum\MessageTypeCode::ACCOUNT_RESET_BY_LETTER)
            ->will($this->returnValue($messageType));

        $this->mockMessageRepo->expects($this->any())
            ->method('hasAlreadyRequestedMessage')
            ->withAnyParameters()
            ->willReturn(true);

        $data = ['personId' => 4, 'messageTypeCode' => MessageTypeCode::ACCOUNT_RESET_BY_LETTER];
        $this->service->createMessage($data);
    }

    public function testCreateMessage_givenAccountResetByLetterAndNotRequestedYet_shouldDoNothing()
    {
        $messageType = new MessageType();
        $messageType->setCode(MessageTypeCode::ACCOUNT_RESET_BY_LETTER);

        //mock messageType get call
        $this->mockMessageTypeRepo->expects($this->any())
            ->method('getByCode')
            ->with(\DvsaCommon\Enum\MessageTypeCode::ACCOUNT_RESET_BY_LETTER)
            ->will($this->returnValue($messageType));

        $this->mockMessageRepo->expects($this->any())
            ->method('hasAlreadyRequestedMessage')
            ->withAnyParameters()
            ->willReturn(false);

        $data = ['personId' => 4, 'messageTypeCode' => MessageTypeCode::ACCOUNT_RESET_BY_LETTER];
        $this->service->createMessage($data);
    }

    public function testCreateMessage_givenPasswordResetByLetterAndAlreadyRequested_shouldThrowError()
    {
        $person = $this->getMockPerson()->getPerson();
        $this->setExpectedException(
            BadRequestException::class,
            'A password reset letter has already been requested for '.
            $person->getFirstName().' '.$person->getFamilyName()
            .' today.');
        $messageType = new MessageType();
        $messageType->setCode(MessageTypeCode::PASSWORD_RESET_BY_LETTER);

        //mock messageType get call
        $this->mockMessageTypeRepo->expects($this->any())
            ->method('getByCode')
            ->with(\DvsaCommon\Enum\MessageTypeCode::PASSWORD_RESET_BY_LETTER)
            ->will($this->returnValue($messageType));

        $this->mockMessageRepo->expects($this->any())
            ->method('hasAlreadyRequestedMessage')
            ->withAnyParameters()
            ->willReturn(true);

        $data = ['personId' => 4, 'messageTypeCode' => MessageTypeCode::PASSWORD_RESET_BY_LETTER];
        $this->service->createMessage($data);
    }

    public function testCreateMessage_givenPasswordResetByLetterAndNotRequestedYet_shouldDoNothing()
    {
        $messageType = new MessageType();
        $messageType->setCode(MessageTypeCode::PASSWORD_RESET_BY_LETTER);

        //mock messageType get call
        $this->mockMessageTypeRepo->expects($this->any())
            ->method('getByCode')
            ->with(\DvsaCommon\Enum\MessageTypeCode::PASSWORD_RESET_BY_LETTER)
            ->will($this->returnValue($messageType));

        $this->mockMessageRepo->expects($this->any())
            ->method('hasAlreadyRequestedMessage')
            ->withAnyParameters()
            ->willReturn(false);

        $data = ['personId' => 4, 'messageTypeCode' => MessageTypeCode::PASSWORD_RESET_BY_LETTER];
        $this->service->createMessage($data);
    }

    protected function getMockPerson()
    {
        $person = new Person();
        $person->setId(4);
        $person->setFirstName('Bob');
        $person->setFamilyName('Gill');
        $person->setAccountClaimRequired(true);

        return new Identity($person);
    }

    protected function getMockMessage()
    {
        $message = new Message();
        $message->setId(2);
    }
}
