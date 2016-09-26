<?php

namespace AccountApiTest\Service;

use AccountApi\Crypt\SecurityAnswerHashFunction;
use AccountApi\Service\Validator\PersonSecurityAnswerValidator;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManager;
use Dvsa\Mot\Api\RegistrationModule\Service\PersonSecurityAnswerRecorder;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonSecurityAnswer;
use DvsaEntities\Entity\SecurityQuestion;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use AccountApi\Mapper\SecurityQuestionMapper;
use AccountApi\Service\SecurityQuestionService;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\PersonSecurityAnswerRepository;
use DvsaEntities\Repository\SecurityQuestionRepository;
use Zend\ServiceManager\ServiceManager;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class SecurityQuestionServiceTest
 * @package AccountApiTest\Service
 */
class SecurityQuestionServiceTest extends AbstractServiceTestCase
{
    const USER_ID = 9999;
    const QUESTION_ID = 8888;

    /** @var ServiceManager */
    protected $serviceManager;

    protected $mockEntityManager;
    /** @var   SecurityQuestionRepository|MockObj */
    protected $mockSqRepo;
    /** @var  ParamObfuscator|MockObj */
    private $mockParamObfuscator;
    /** @var PersonSecurityAnswerRecorder */
    private $personSecurityAnswerRecorder;
    /** @var PersonSecurityAnswerValidator */
    private $mockPersonSecurityAnswerValidator;
    /** @var PersonRepository */
    private $mockPersonRepo;

    /** @var  SecurityQuestionService */
    protected $service;

    protected function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);

        //  --  mock repo   --
        $this->mockSqRepo = XMock::of(
            SecurityQuestionRepository::class,
            ['isAnswerCorrect', 'find', 'findAll', 'findQuestionByQuestionNumber']
        );
        $this->serviceManager->setService(SecurityQuestionRepository::class, $this->mockSqRepo);

        //  --  mock entity manager --
        $this->mockEntityManager = $this->getMockEntityManager();
        $this->mockMethod(
            $this->mockEntityManager, 'getRepository', $this->any(), $this->mockSqRepo, SecurityQuestion::class
        );
        $this->mockMethod(
            $this->mockEntityManager, 'getConnection', $this->any(), XMock::of(Connection::class)
        );

        //  --  mock obfuscator --
        $this->mockParamObfuscator = XMock::of(ParamObfuscator::class);
        $this->serviceManager->setService(ParamObfuscator::class, $this->mockParamObfuscator);

        // -- PersonSecurityAnswerRecorder --
        $this->personSecurityAnswerRecorder = new PersonSecurityAnswerRecorder($this->mockSqRepo, new SecurityAnswerHashFunction());

        // -- mock person security answer repo
        $mockPersonSecurityAnswerRepo = XMock::of(PersonSecurityAnswerRepository::class);

        // -- mock person security answer validator
        $this->mockPersonSecurityAnswerValidator = XMock::of(PersonSecurityAnswerValidator::class);

        //  --  mock person repo   --
        $this->mockPersonRepo = XMock::of(PersonRepository::class);
        $this->serviceManager->setService(Person::class, $this->mockPersonRepo);

        //  --
        $this->service = new SecurityQuestionService(
            $this->mockSqRepo,
            new SecurityQuestionMapper(),
            $this->personSecurityAnswerRecorder,
            $this->mockPersonRepo,
            $mockPersonSecurityAnswerRepo,
            $this->mockPersonSecurityAnswerValidator,
            $this->mockParamObfuscator,
            $this->mockEntityManager
        );
    }

    /**
    * @expectedException \Exception
    */
    public function testThrowsExceptionWhenQuestionIdIsNonInteger()
    {
        $this->service->isAnswerCorrect([], 1, '');
    }

    /**
    * @expectedException \Exception
    */
    public function testThrowsExceptionWhenUserIdIsNonInteger()
    {
        $this->service->isAnswerCorrect(1, [], '');
    }

    /**
    * @expectedException \Exception
    */
    public function testThrowsExceptionWhenAnswerIsNonString()
    {
        $this->service->isAnswerCorrect(42, 1, []);
    }

    /**
     * @dataProvider dataProviderTestCorrectlyHandlesFailedQuestionByReturning
     */
    public function testCorrectlyHandlesFailedQuestionByReturningFalse($result, $expect)
    {
        $answer = 'pointless answer';

        $this->mockMethod(
            $this->mockSqRepo, 'isAnswerCorrect', $this->once(), $result, [self::QUESTION_ID, self::USER_ID, $answer]
        );

        $actual = $this->service->isAnswerCorrect(self::QUESTION_ID, self::USER_ID, $answer);

        $this->assertEquals($expect, $actual);
    }

    public function dataProviderTestCorrectlyHandlesFailedQuestionByReturning()
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    public function testFindAll()
    {
        $entity = new SecurityQuestion();
        $dto = new SecurityQuestionDto();

        $this->mockSqRepo->expects($this->once())
            ->method('findAll')
            ->willReturn([$entity]);

        $this->assertEquals([$dto], $this->service->getAll());
    }

    public function testFindQuestionByNumber()
    {
        $entity = new SecurityQuestion();
        $dto = new SecurityQuestionDto();
        $qid = 42;
        $uid = 1;

        $this->mockSqRepo->expects($this->once())
            ->method('findQuestionByQuestionNumber')
            ->with($qid, $uid)
            ->willReturn($entity);

        $this->assertEquals($dto, $this->service->findQuestionByQuestionNumber($qid, $uid));
    }

    /**
     * @expectedException \Exception
     */
    public function testFindQuestionByNumberThrowsException()
    {
        $this->service->findQuestionByQuestionNumber('invalid', 'invalid');
    }

    /**
     * @expectedException \Exception
     */
    public function testUpdateAnswersForUserThrowsExceptionIfInvalidData()
    {
        $this->mockPersonSecurityAnswerValidator
            ->expects($this->any())
            ->method('validate')
            ->will($this->throwException(new BadRequestException('Validation failure', 0)));

        $this->service->updateAnswersForUser(1, ["not even an array"]);
    }

    public function testUpdateAnswersForUserCallsPersonRepository()
    {
        $person = new Person();
        $this->mockPersonRepo
            ->expects($this->any())
            ->method('find')
            ->willReturn($person);

        $securityQuestion = new SecurityQuestion();
        $this->mockSqRepo
            ->expects($this->any())
            ->method('find')
            ->willReturn($securityQuestion);

        $savePersonSpy = new MethodSpy($this->mockEntityManager, 'persist');

        //

        $data = [
            ["questionId" => 1, "answer" => "answer to question 1"],
            ["questionId" => 2, "answer" => "answer to question 2"]
        ];

        $this->service->updateAnswersForUser(1, $data);

        //

        /** @var Person $updatedPerson */
        $updatedPerson = $savePersonSpy->paramsForInvocation(0)[0];
        $answers = $updatedPerson->getSecurityAnswers();

        $this->assertSecurityAnswerMatchesExpected($answers[0], $securityQuestion, 'answer to question 1');
        $this->assertSecurityAnswerMatchesExpected($answers[1], $securityQuestion, 'answer to question 2');
    }

    private function assertSecurityAnswerMatchesExpected(
        PersonSecurityAnswer $actualAnswer,
        SecurityQuestion $expectedQuestion,
        $expectedAnswer
    ) {
        $hashFunction = new SecurityAnswerHashFunction();

        $this->assertEquals($expectedQuestion, $actualAnswer->getSecurityQuestion());
        $this->assertTrue($hashFunction->verify($expectedAnswer, $actualAnswer->getAnswer()));
    }
}
