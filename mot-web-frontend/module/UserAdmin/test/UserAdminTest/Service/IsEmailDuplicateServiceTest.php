<?php

namespace UserAdminTest\Service;

use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Service\IsEmailDuplicateService;

class IsEmailDuplicateServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var HttpRestJsonClient */
    private $jsonClient;

    public function setUp()
    {
        parent::setUp();
        $this->jsonClient = XMock::of(HttpRestJsonClient::class);
    }

    public function testWhenEmailIsDuplicated_responseBodyShouldBeTrue()
    {
        $this->jsonClient
            ->expects($this->once())
            ->method('get')
            ->with(IsEmailDuplicateService::URL.$this->validEmailEncoded())
            ->willReturn(['data' => ['isDuplicate' => true]]);

        $actual = $this->buildService()->isEmailDuplicate($this->validDuplicateEmail());

        $this->assertTrue($actual);
    }

    public function testWhenEmailIsNotDuplicated_responseBodyShouldBeFalse()
    {
        $this->jsonClient
            ->expects($this->once())
            ->method('get')
            ->with(IsEmailDuplicateService::URL.$this->validEmailEncoded())
            ->willReturn(['data' => ['isDuplicate' => false]]);

        $actual = $this->buildService()->isEmailDuplicate($this->validDuplicateEmail());

        $this->assertFalse($actual);
    }

    public function testWhenEmailContainsSpecialCharacters_specialCharactersShouldBeEncoded()
    {
        $this->jsonClient
            ->expects($this->once())
            ->method('get')
            ->with(IsEmailDuplicateService::URL.'test%2B10%40email.com')
            ->willReturn(['data' => ['isDuplicate' => false]]);

        $this->buildService()->isEmailDuplicate('test+10@email.com');
    }

    private function buildService()
    {
        return new IsEmailDuplicateService($this->jsonClient);
    }

    private function validDuplicateEmail()
    {
        return 'dummy@email.com';
    }

    private function validEmailEncoded()
    {
        return urlencode($this->validDuplicateEmail());
    }
}
