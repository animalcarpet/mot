<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace AccountApiTest\Service;

use AccountApi\Crypt\SecurityAnswerHashFunction;
use AccountApi\Service\Validator\PersonSecurityAnswerValidator;
use Doctrine\DBAL\Driver\Connection;
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
 * Class SecurityQuestionServiceTest.
 */
class SecurityQuestionServiceTest extends AbstractServiceTestCase
{
    const PERSON_ID = 9999;
    const QUESTION_ID = 8888;
    const QUESTION_ID_FOR_NON_EXISTING_ANSWER = 8686;
    const ANSWER = 'Pointless Answer';

    /** @var ServiceManager */
    protected $serviceManager;

    protected $mockEntityManager;

    /** @var SecurityQuestionRepository|MockObj */
    protected $mockSqRepo;

    /** @var ParamObfuscator|MockObj */
    private $mockParamObfuscator;

    /** @var PersonSecurityAnswerRecorder */
    private $personSecurityAnswerRecorder;

    /** @var PersonSecurityAnswerValidator */
    private $mockPersonSecurityAnswerValidator;

    /** @var PersonRepository */
    private $mockPersonRepo;

    /** @var SecurityQuestionService */
    protected $service;

    protected function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);

        //  --  mock repo   --
        $this->mockSqRepo = XMock::of(
            SecurityQuestionRepository::class,
            ['isAnswerCorrect', 'find', 'findAll', 'findQuestionByQuestionNumber', 'findQuestionsByPersonId']
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

        $mockPersonSecurityAnswer = XMock::of(PersonSecurityAnswer::class);
        $mockPersonSecurityAnswer->expects($this->any())
            ->method('getAnswer')
            ->willReturn((new SecurityAnswerHashFunction())->hash(self::ANSWER));

        // -- mock person security answer repo
        $mockPersonSecurityAnswerRepo = XMock::of(PersonSecurityAnswerRepository::class);
        $mockPersonSecurityAnswerRepo->expects($this->any())
            ->method('getPersonAnswerForQuestion')
            ->willReturnMap(
                [
                    [self::PERSON_ID, self::QUESTION_ID, $mockPersonSecurityAnswer],
                    [self::PERSON_ID, self::QUESTION_ID_FOR_NON_EXISTING_ANSWER, null],
                ]
            );

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
            $this->mockEntityManager,
            new SecurityAnswerHashFunction(),
            1
        );
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

        $this->service->updateAnswersForUser(1, ['not even an array']);
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

        $data = [
            ['questionId' => 1, 'answer' => 'answer to question 1'],
            ['questionId' => 2, 'answer' => 'answer to question 2'],
        ];

        $this->service->updateAnswersForUser(1, $data);

        /** @var Person $updatedPerson */
        $updatedPerson = $savePersonSpy->paramsForInvocation(0)[0];
        $answers = $updatedPerson->getSecurityAnswers();

        $this->assertSecurityAnswerMatchesExpected($answers[0], $securityQuestion, 'answer to question 1');
        $this->assertSecurityAnswerMatchesExpected($answers[1], $securityQuestion, 'answer to question 2');
    }

    /**
     * @param $personId
     * @param \Exception $expectedException
     * @dataProvider questionsForPersonArgumentAssertionDataProvider
     */
    public function testGetQuestionsForPersonArgumentAssertion($personId, $expectedException)
    {
        $this->setExpectedException(
            get_class($expectedException),
            $expectedException->getMessage()
        );

        $this->service->getQuestionsForPerson($personId);
    }

    /**
     * @return array
     */
    public function questionsForPersonArgumentAssertionDataProvider()
    {
        $dataSet = [];
        foreach ($this->getUnexpectedPersonIds() as $personId) {
            $expectedException = new \InvalidArgumentException(
                sprintf(SecurityQuestionService::ERR_TYPE_PERSON_ID, var_export($personId, true))
            );

            $dataSet[] = [
                'personId' => $personId,
                'expectedException' => $expectedException,
            ];
        }

        return $dataSet;
    }

    public function testGetQuestionsForPersonReturnType()
    {
        $securityQuestions = [
            new SecurityQuestion(),
            new SecurityQuestion(),
        ];

        $this->mockSqRepo
            ->expects($this->once())
            ->method('findQuestionsByPersonId')
            ->willReturn($securityQuestions);

        $this->assertContainsOnlyInstancesOf(
            SecurityQuestionDto::class,
            $this->service->getQuestionsForPerson(self::PERSON_ID)
        );
    }

    /**
     * @param $personId
     * @param $questionsAndAnswers
     * @param \Exception $expectedException
     * @dataProvider verifySecurityAnswersForPersonDataProvider
     */
    public function testVerifySecurityAnswersForPerson($personId, $questionsAndAnswers, $expectedException)
    {
        $this->setExpectedException(
            get_class($expectedException),
            $expectedException->getMessage()
        );

        $this->service->verifySecurityAnswersForPerson($personId, $questionsAndAnswers);
    }

    public function verifySecurityAnswersForPersonDataProvider()
    {
        $dataSet = [];
        foreach ($this->getUnexpectedPersonIds() as $personId) {
            $expectedException = new \InvalidArgumentException(
                sprintf(SecurityQuestionService::ERR_TYPE_PERSON_ID, var_export($personId, true))
            );

            $dataSet[] = [
                'personId' => $personId,
                'questionsAndAnswers' => [],
                'expectedException' => $expectedException,
            ];
        }

        $unexpectedQuestionsAndAnswersType = $this->getUnexpectedPersonIds();

        $unexpectedQuestionsAndAnswersType[] = [1 => 0];
        $unexpectedQuestionsAndAnswersType[] = [1 => 1];
        $unexpectedQuestionsAndAnswersType[] = [1 => null];
        $unexpectedQuestionsAndAnswersType[] = [1 => true];
        $unexpectedQuestionsAndAnswersType[] = [1 => false];
        $unexpectedQuestionsAndAnswersType[] = [1 => []];
        $unexpectedQuestionsAndAnswersType[] = [0 => 'Acceptable answer'];
        $unexpectedQuestionsAndAnswersType[] = [null => 'Acceptable answer'];
        $unexpectedQuestionsAndAnswersType[] = ['string' => 'Acceptable answer'];

        foreach ($unexpectedQuestionsAndAnswersType as $questionsAndAnswers) {
            $expectedException = new \InvalidArgumentException(
                sprintf(SecurityQuestionService::ERR_MSG_INVALID_ARGUMENT, var_export($questionsAndAnswers, true))
            );

            $dataSet[] = [
                'personId' => 105,
                'questionsAndAnswers' => $questionsAndAnswers,
                'expectedException' => $expectedException,
            ];
        }

        return $dataSet;
    }

    public function testVerifySecurityAnswersForPersonReturnType()
    {
        $map = [
            self::QUESTION_ID => true,
            self::QUESTION_ID_FOR_NON_EXISTING_ANSWER => false,
            self::QUESTION_ID => true,
        ];

        $acceptableQuestionsAndAnswers = [
            self::QUESTION_ID => self::ANSWER,
            self::QUESTION_ID_FOR_NON_EXISTING_ANSWER => 'Answer to question with id 20 assume incorrect',
            self::QUESTION_ID => self::ANSWER,
        ];

        $this->assertEquals(
            $map,
            $this->service->verifySecurityAnswersForPerson(self::PERSON_ID, $acceptableQuestionsAndAnswers)
        );
    }

    private function getUnexpectedPersonIds()
    {
        return [-1, null, true, false, '', ' ', 'a', '.'];
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

    /**
     * @param array  $questionsAndAnswers
     * @param int    $expectedDelay
     * @param string $testSubjectType
     * @dataProvider testDelayDatProvider
     */
    public function testDelay(array $questionsAndAnswers, $expectedDelay, $testSubjectType)
    {
        $delayAcceptableMargin = 0.5;

        $start = microtime(true);

        $this->service->verifySecurityAnswersForPerson(self::PERSON_ID, $questionsAndAnswers);

        $respondTime = round(microtime(true) - $start);

        $expectedLowerBound = $expectedDelay - $delayAcceptableMargin;
        $expectedHigherBound = $expectedDelay + $delayAcceptableMargin;

        $this->assertTrue(
            $respondTime < $expectedHigherBound &&
            $respondTime > $expectedLowerBound,
            sprintf(
                'Failed to assert calling the verification method with %s caused a delay about %s seconds, '.
                'instead we measured a %s second delay',
                $testSubjectType,
                $expectedDelay,
                $respondTime
            )
        );
    }

    public function testDelayDatProvider()
    {
        return [
            [
                'correctAnswers' => [
                    self::QUESTION_ID => self::ANSWER,
                    self::QUESTION_ID => self::ANSWER,
                    self::QUESTION_ID => self::ANSWER,
                ],
                'expectedDelay' => 0,
                'test subject' => 'correct answers',
            ],
            [
                'incorrectAnswers' => [
                    self::QUESTION_ID => self::ANSWER,
                    self::QUESTION_ID_FOR_NON_EXISTING_ANSWER => self::ANSWER,
                    self::QUESTION_ID => self::ANSWER,
                ],
                'expectedDelay' => 1,
                'test subject' => 'incorrect answers',
            ],
        ];
    }
}
