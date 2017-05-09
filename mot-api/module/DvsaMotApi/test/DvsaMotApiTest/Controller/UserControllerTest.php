<?php

namespace DvsaMotApiTest\Controller;

use Doctrine\Tests\ORM\Tools\Export\User;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Http\HttpStatus;
use DvsaCommon\Validator\UsernameValidator;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaMotApi\Controller\UserController;
use DvsaMotApi\Service\UserService;
use Zend\Http\Request;
use Zend\View\Model\JsonModel;

/**
 * Class UserControllerTest.
 */
class UserControllerTest extends AbstractMotApiControllerTestCase
{
    const MAX_USERNAME_LENGTH = 50;

    protected function setUp()
    {
        $usernameValidatorMock = $this->createUsernameValidatorMock(true);
        $this->controller = new UserController($usernameValidatorMock);

        parent::setUp();
    }

    public function testGetListCantBeAccessed()
    {
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(HttpStatus::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertInstanceOf(JsonModel::class, $result);
    }

    public function testGetCanBeAccessed()
    {
        $username = 'tester1';
        $expectedResults = ['username' => 'tester1'];
        $this->routeMatch->setParam('username', $username);

        $mockUserService = $this->getMockUserService();
        $mockUserService
            ->expects($this->once())
            ->method('getUserData')
            ->will($this->returnValue($expectedResults));

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(JsonModel::class, $result);
        $this->assertEquals($expectedResults, $result->getVariable('data'));
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
        $this->routeMatch->setParam('username', $username);

        $mockUserService = $this->getMockUserService();
        $mockUserService
            ->expects($this->once())
            ->method('getUserData')
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

    public function testNotValidInputReturnsUnprocessableEntityResponse()
    {
        $this->setController(new UserController($this->createUsernameValidatorMock(false)));
        $this->setUpController($this->getController());

        $this->routeMatch->setParam('username', str_repeat('a', self::MAX_USERNAME_LENGTH + 1));

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
     * @expectedException     \DvsaCommonApi\Service\Exception\NotFoundException
     * @expectedExceptionCode 404
     */
    public function testGetReturnsErrorForUserWhoDoesNotExist()
    {
        $username = 'doesnotexist';
        $this->routeMatch->setParam('username', $username);

        $mockUserService = $this->getMockUserService();
        $mockUserService
            ->expects($this->once())
            ->method('getUserData')
            ->will($this->throwException(new NotFoundException('User', $username)));

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertUserNotFoundResponse($response, $result, $username);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockUserService()
    {
        return $this->getMockServiceManagerClass(UserService::class, UserService::class);
    }

    /**
     * @param $response
     * @param $result
     * @param $username
     */
    protected function assertUserNotFoundResponse($response, $result, $username)
    {
        $this->assertResponseStatusAndResultHasError(
            $response,
            404,
            $result,
            "User $username not found",
            NotFoundException::ERROR_CODE_NOT_FOUND
        );
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
