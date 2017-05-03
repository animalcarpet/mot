<?php

namespace AccountTest\Service;

use Account\Service\SecurityQuestionService;
use DvsaClient\Entity\Person;
use DvsaClient\Mapper\PersonMapper;
use DvsaClient\Mapper\UserAdminMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\Stdlib\Parameters;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Account\Validator\ClaimValidator;

/**
 * Class SecurityQuestionServiceTest.
 */
class SecurityQuestionServiceTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    const INVALID_PARAM = 'invalid';
    const PERSON_ID = 1;
    const FIRST_QUESTION = 1;
    const SECOND_QUESTION = 2;
    const SEARCH_PARAM_TEST = 'searchParamsFromTheSession';
    const QUESTION_ID = 99999;

    /** @var SecurityQuestionService|MockObj */
    private $service;

    /** @var UserAdminSessionManager|MockObj */
    private $session;
    /** @var MapperFactory */
    private $mapper;

    /** @var PersonMapper */
    private $person;
    /** @var UserAdminMapper|MockObj */
    private $userAdmin;

    /** @var  FlashMessenger|MockObj */
    private $messenger;

    public function setUp()
    {
        $this->session = XMock::of(UserAdminSessionManager::class);
        $this->mapper = $this->getMapperFactory();

        $this->service = new SecurityQuestionService($this->mapper, $this->session);
        $this->messenger = XMock::of(FlashMessenger::class);
    }

    public function testGetterSetter()
    {
        $this->session->expects($this->any())
            ->method('getElementOfUserAdminSession')
            ->willReturn(false);

        $this->assertInstanceOf(
            SecurityQuestionService::class, $this->service->setUserAndQuestion(self::PERSON_ID, self::FIRST_QUESTION)
        );
        $this->assertSame(self::FIRST_QUESTION, $this->service->getQuestionNumber());
        $this->assertSame(self::PERSON_ID, $this->service->getUserId());
        $this->assertSame(['First security question - your answer was ', 'correct'], $this->service->getSuccessMessage());
        $this->assertSame(['First security question - your answer is ', 'not correct'], $this->service->getErrorMessage());
        $this->assertFalse($this->service->getQuestionSuccess());

        $this->service->setUserAndQuestion(self::PERSON_ID, self::SECOND_QUESTION);
        $this->assertSame(self::SECOND_QUESTION, $this->service->getQuestionNumber());
        $this->assertSame(['Second security question - your answer is ', 'not correct'], $this->service->getErrorMessage());
        $this->assertFalse($this->service->getQuestionSuccess());
    }

    public function testGetSearchParams()
    {
        $this->session->expects($this->once())
            ->method('getElementOfUserAdminSession')
            ->willReturn(self::SEARCH_PARAM_TEST);

        $this->assertSame(self::SEARCH_PARAM_TEST, $this->service->getSearchParams());
    }

    public function testGetNumberOfAttemptMessageOne()
    {
        $this->session->expects($this->any())
            ->method('getElementOfUserAdminSession')
            ->willReturn(1);

        $this->assertSame(
            sprintf(SecurityQuestionService::NUMBER_ATTEMPT_ONE, 1),
            $this->service->getNumberOfAttemptMessage()
        );
    }

    public function testGetNumberOfAttemptMessageTwo()
    {
        $this->session->expects($this->any())
            ->method('getElementOfUserAdminSession')
            ->willReturn(2);

        $this->assertSame(
            sprintf(SecurityQuestionService::NUMBER_ATTEMPT_SEVERAL, 2),
            $this->service->getNumberOfAttemptMessage()
        );
    }

    public function testGetNumberOfAttemptMessageThree()
    {
        $this->session->expects($this->any())
            ->method('getElementOfUserAdminSession')
            ->willReturn(3);

        $this->assertSame('', $this->service->getNumberOfAttemptMessage());
    }

    public function testInitializeSession()
    {
        $this->session->expects($this->at(0))
            ->method('getElementOfUserAdminSession')
            ->willReturn(self::PERSON_ID);
        $this->session->expects($this->at(1))
            ->method('getElementOfUserAdminSession')
            ->willReturn(UserAdminSessionManager::MAX_NUMBER_ATTEMPT);

        $this->service->setUserAndQuestion(self::PERSON_ID, self::FIRST_QUESTION);
        $this->service->initializeSession(self::SEARCH_PARAM_TEST);
    }

    public function testIsBeginningOfTheJourney()
    {
        $this->session->expects($this->at(0))
            ->method('getElementOfUserAdminSession')
            ->willReturn(self::PERSON_ID);
        $this->session->expects($this->at(1))
            ->method('getElementOfUserAdminSession')
            ->willReturn(UserAdminSessionManager::MAX_NUMBER_ATTEMPT);

        $this->service->setUserAndQuestion(self::PERSON_ID, self::FIRST_QUESTION);
        $this->assertTrue($this->service->isBeginningOfTheJourney());
    }

    public function testIsNotBeginningOfTheJourney()
    {
        $this->mockMethod(
            $this->session, 'getElementOfUserAdminSession', $this->at(0), UserAdminSessionManager::MAX_NUMBER_ATTEMPT
        );
        $this->mockMethod(
            $this->session, 'getElementOfUserAdminSession', $this->at(1), self::PERSON_ID
        );

        $this->service->setUserAndQuestion(self::PERSON_ID, self::SECOND_QUESTION);
        $this->assertFalse($this->service->isBeginningOfTheJourney());
    }

    public function testIsRedirectNeeded()
    {
        $this->session->expects($this->at(0))
            ->method('isUserAuthenticated')
            ->willReturn(true);

        $this->assertTrue($this->service->isRedirectionIsNeeded());
    }

    public function testIsNotRedirectNeeded()
    {
        $this->session->expects($this->at(0))
            ->method('isUserAuthenticated')
            ->willReturn(false);

        $this->session->expects($this->at(1))
            ->method('getElementOfUserAdminSession')
            ->willReturn(false);

        $this->session->expects($this->at(2))
            ->method('getElementOfUserAdminSession')
            ->willReturn(3);

        $this->assertFalse($this->service->isRedirectionIsNeeded());
    }

    public function testGetPerson()
    {
        $this->person->expects($this->any())
            ->method('getById')
            ->with(self::PERSON_ID)
            ->will($this->returnValue(new Person()));

        $this->service->setUserAndQuestion(self::PERSON_ID, self::FIRST_QUESTION);
        $this->assertInstanceOf(Person::class, $this->service->getPerson());
    }

    public function testGetQuestion()
    {
        $this->userAdmin->expects($this->any())
            ->method('getSecurityQuestion')
            ->with(self::FIRST_QUESTION - 1, self::PERSON_ID)
            ->will($this->returnValue(new SecurityQuestionDto()));

        $this->service->setUserAndQuestion(self::PERSON_ID, self::FIRST_QUESTION);
        $this->assertInstanceOf(SecurityQuestionDto::class, $this->service->getQuestion());
    }

    /**
     * @dataProvider dataProviderTestValidateQuestion
     */
    public function testValidateQuestionSuccess($apiResult, $expect)
    {
        $answer = 'unit_Answer';

        $question = (new SecurityQuestionDto())->setId(self::QUESTION_ID);

        $this->userAdmin->expects($this->at(0))
            ->method('getSecurityQuestion')
            ->with(self::FIRST_QUESTION - 1, self::PERSON_ID)
            ->willReturn($question);

        $this->userAdmin->expects($this->at(1))
            ->method('checkSecurityQuestion')
            ->with(self::QUESTION_ID, self::PERSON_ID, ['answer' => $answer])
            ->willReturn($apiResult);

        $this->service->setUserAndQuestion(self::PERSON_ID, self::FIRST_QUESTION);
        $this->assertEquals($expect, $this->service->validateQuestion($answer));
    }

    public function dataProviderTestValidateQuestion()
    {
        return [
            [
                'apiResult' => true,
                'expect' => true,
            ],
            [false, false],
        ];
    }

    public function testManageSessionQuestionInit()
    {
        $request = new FakeRequest();
        $this->service->setUserAndQuestion(self::PERSON_ID, self::FIRST_QUESTION);
        $this->assertTrue($this->service->manageSessionQuestion($request, $this->messenger));
    }

    public function testManageSessionQuestionInitNoAnswer()
    {
        $request = new FakeRequest(true, '');

        $this->service->setUserAndQuestion(self::PERSON_ID, self::FIRST_QUESTION);
        $this->assertTrue($this->service->manageSessionQuestion($request, $this->messenger));
    }

    public function testManageSessionQuestionAnswerTooLong()
    {
        $answer = str_pad('A', ClaimValidator::MAX_ANSWER + 1, 'A');
        $request = new FakeRequest(true, $answer);

        $this->service->setUserAndQuestion(self::PERSON_ID, self::FIRST_QUESTION);
        $this->assertTrue($this->service->manageSessionQuestion($request, $this->messenger));
    }

    /**
     * @dataProvider dataProviderTestManageSessionQuestionInitPostX
     */
    public function testManageSessionQuestionInitPostX($apiResult)
    {
        $answer = 'unit_Answer';

        $question = (new SecurityQuestionDto())->setId(self::QUESTION_ID);

        $this->userAdmin->expects($this->at(0))
            ->method('getSecurityQuestion')
            ->with(self::FIRST_QUESTION - 1, self::PERSON_ID)
            ->will($this->returnValue($question));

        $this->userAdmin->expects($this->at(1))
            ->method('checkSecurityQuestion')
            ->with(self::QUESTION_ID, self::PERSON_ID, ['answer' => $answer])
            ->will($this->returnValue($apiResult));

        $request = new FakeRequest(true, $answer);
        $this->service->setUserAndQuestion(self::PERSON_ID, self::FIRST_QUESTION);
        $this->assertTrue($this->service->manageSessionQuestion($request, $this->messenger));
    }

    public function dataProviderTestManageSessionQuestionInitPostX()
    {
        return [
            [
                'apiResult' => true,
            ],
            [false],
        ];
    }

    private function getMapperFactory()
    {
        $mockMapperFactory = XMock::of(MapperFactory::class);

        $map = [
            [MapperFactory::PERSON, $this->getPersonMapperMock()],
            [MapperFactory::USER_ADMIN, $this->getUserAdminMapperMock()],
        ];

        $mockMapperFactory->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mockMapperFactory;
    }

    private function getPersonMapperMock()
    {
        $this->person = XMock::of(PersonMapper::class);

        return $this->person;
    }

    private function getUserAdminMapperMock()
    {
        $this->userAdmin = XMock::of(UserAdminMapper::class);

        return $this->userAdmin;
    }
}

class FakeRequest
{
    protected $isPost;
    protected $answer;

    public function __construct($isPost = false, $answer = 'blah')
    {
        $this->isPost = $isPost;
        $this->answer = $answer;
    }

    public function getQuery()
    {
        return new Parameters();
    }

    public function isPost()
    {
        return $this->isPost;
    }

    public function getPost()
    {
        return $this->answer;
    }
}
