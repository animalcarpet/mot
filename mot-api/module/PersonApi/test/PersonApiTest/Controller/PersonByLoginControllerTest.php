<?php

namespace PersonApiTest\Controller;

use PersonApi\Controller\PersonByLoginController;
use DvsaCommon\Validator\UsernameValidator;
use PersonApi\Service\PersonService;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommon\Http\HttpStatus;
use Zend\View\Model\JsonModel;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;

/**
 * Unit tests for PersonByLoginControllerTest.
 */
class PersonByLoginControllerTest extends AbstractRestfulControllerTestCase
{
    const MAX_USERNAME_LENGTH = 50;

    public function setUp()
    {
        $usernameValidatorMock = $this->createUsernameValidatorMock(true);
        $this->controller = new PersonByLoginController($usernameValidatorMock);

        parent::setUp();
    }

    /**
     * Route /person/tester1.
     */
    public function testGetCanBeAccessed()
    {
        $username = 'tester1';
        $expectedResults = ['username' => 'tester1'];
        $this->routeMatch->setParam('login', $username);

        $personServiceMock = $this->getPersonServiceMock();
        $personServiceMock
            ->expects($this->once())
            ->method('getPersonByIdentifierArray')
            ->will($this->returnValue($expectedResults));

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(HttpStatus::HTTP_OK, $response->getStatusCode());
        $this->assertInstanceOf(JsonModel::class, $result);
        $this->assertEquals($expectedResults, $result->getVariable('data'));
    }

    /**
     * PersonByLoginController::getList() should not be available.
     */
    public function testGetListCantBeAccessed()
    {
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(HttpStatus::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        $this->assertInstanceOf(JsonModel::class, $result);
    }

    /**
     * Tests the implementation built for VM-8488.
     *
     * NOTE: The acceptance criteria may change in the future. Rewrite this test as necessary. What you need to look at
     * is if an UnauthorisedException will be transformed into a 404 or not.
     */
    public function testGetThrowsNotFoundResponseIfNotAuthorised()
    {
        $username = 'tester1';
        $this->routeMatch->setParam('login', $username);

        $personServiceMock = $this->getPersonServiceMock();
        $personServiceMock
            ->expects($this->once())
            ->method('getPersonByIdentifierArray')
            ->will($this->throwException(new UnauthorisedException('')));

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(HttpStatus::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertInstanceOf(JsonModel::class, $result);
        $this->assertInternalType('array', $result->getVariable('errors'));
        $errors = $result->getVariable('errors');
        $this->assertArrayHasKey('problem', $errors);
        $problem = $errors['problem'];
        $this->assertEquals('Resource not found', $problem['detail']);
        $this->assertEquals('http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html', $problem['type']);
        $this->assertEquals(HttpStatus::HTTP_NOT_FOUND, $problem['status']);
        $this->assertEquals(HttpStatus::$statusTexts[HttpStatus::HTTP_NOT_FOUND], $problem['title']);
    }

    /**
     * The UsernameValidator should throw a Validation exception when a username longer than MAX_USERNAME_LENGTH
     * characters is used.
     */
    public function testNotValidInputReturnsUnprocessableEntityResponse()
    {
        $this->setController(new PersonByloginController($this->createUsernameValidatorMock(false)));
        $this->setUpController($this->getController());

        $this->routeMatch->setParam('login', str_repeat('a', self::MAX_USERNAME_LENGTH + 1));

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(HttpStatus::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertInstanceOf(JsonModel::class, $result);

        $this->assertInternalType('array', $result->getVariable('errors'));
        $errors = $result->getVariable('errors');
        $this->assertArrayHasKey('problem', $errors);
        $problem = $errors['problem'];

        $this->assertEquals('Failed Validation', $problem['detail']);
        $this->assertEquals('http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html', $problem['type']);
        $this->assertEquals(HttpStatus::HTTP_UNPROCESSABLE_ENTITY, $problem['status']);
        $this->assertEquals(HttpStatus::$statusTexts[HttpStatus::HTTP_UNPROCESSABLE_ENTITY], $problem['title']);

        $validationMessages = $problem['validation_messages'];
        $this->assertArrayHasKey('username', $validationMessages);
        $this->assertArrayHasKey('stringLengthTooLong', $validationMessages['username']);
        $this->assertEquals(sprintf('Username must be less than %s characters long.', self::MAX_USERNAME_LENGTH),
            $validationMessages['username']['stringLengthTooLong']);
    }

    /**
     * Querying the endpoint for a person that doesn't exist should return a 404 NOT FOUND.
     *
     * @expectedException     \DvsaCommonApi\Service\Exception\NotFoundException
     * @expectedExceptionCode 404
     */
    public function testGetReturnsErrorForPersonWhoDoesNotExist()
    {
        $username = 'doesnotexist';
        $this->routeMatch->setParam('login', $username);

        $personServiceMock = $this->getPersonServiceMock();
        $personServiceMock
            ->expects($this->once())
            ->method('getPersonByIdentifierArray')
            ->will($this->throwException(new NotFoundException('Person', $username)));

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertPersonNotFoundResponse($response, $result, $username);
    }

    /**
     * @param $response
     * @param $result
     * @param $username
     */
    protected function assertPersonNotFoundResponse($response, $result, $username)
    {
        $this->assertResponseStatusAndResultHasError(
            $response,
            HttpStatus::HTTP_NOT_FOUND,
            $result,
            "Person $username not found",
            NotFoundException::ERROR_CODE_NOT_FOUND
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPersonServiceMock()
    {
        return $this->getMockServiceManagerClass(PersonService::class, PersonService::class);
    }

    /**
     * @param bool $isValid The value returned by UsernameValidator::isValid()
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createUsernameValidatorMock($isValid = true)
    {
        $usernameValidatorMock = $this
            ->getMockBuilder(UsernameValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $usernameValidatorMock
            ->expects($this->any())
            ->method('isValid')
            ->willReturn($isValid);

        if (!$isValid) {
            $messages = ['stringLengthTooLong' => sprintf('Username must be less than %s characters long.',
                self::MAX_USERNAME_LENGTH)];

            $usernameValidatorMock
                ->expects($this->any())
                ->method('getMessages')
                ->willReturn($messages);
        }

        return $usernameValidatorMock;
    }
}
