<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\ChangeSecurityQuestions\Service;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\PasswordValidationService;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\Response;
use DvsaCommon\HttpRestJson\Client;

class PasswordValidationServiceTest extends \PHPUnit_Framework_TestCase
{
    private $client;

    private $passwordValidationService;

    public function setUp()
    {
        $this->client = XMock::of(Client::class);
        $this->passwordValidationService = new PasswordValidationService($this->client);
    }

    public function testDataIsSetCorrectlyToApi()
    {
        $password = 'testPassword';

        $this->client
            ->expects($this->once())
            ->method('post')
            ->with(PasswordValidationService::ROUTE, ['password' => $password]);

        $actual = $this->passwordValidationService->isPasswordValid($password);
        $this->assertTrue($actual);
    }

    public function testWhenValidationExceptionThrownFalseIsReturned()
    {
        $password = 'testPassword';

        $this->client
            ->expects($this->once())
            ->method('post')
            ->will($this->throwException(new ValidationException('', '', [], 422)));

        $actual = $this->passwordValidationService->isPasswordValid($password);
        $this->assertFalse($actual);
    }
}