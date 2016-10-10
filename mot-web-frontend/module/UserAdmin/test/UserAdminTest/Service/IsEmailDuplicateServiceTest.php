<?php

namespace UserAdminTest\Service;

use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Service\IsEmailDuplicateService;

class IsEmailDuplicateServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  HttpRestJsonClient */
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
            ->with(IsEmailDuplicateService::URL . $this->validDuplicateEmail())
            ->willReturn(['data' => ['isDuplicate' => true]]);

        $actual = $this->buildService()->isEmailDuplicate($this->validDuplicateEmail());

        $this->assertTrue($actual);
    }

    public function testWhenEmailIsNotDuplicated_responseBodyShouldBeFalse()
    {
        $this->jsonClient
            ->expects($this->once())
            ->method('get')
            ->with(IsEmailDuplicateService::URL . $this->validDuplicateEmail())
            ->willReturn(['data' => ['isDuplicate' => false]]);

        $actual = $this->buildService()->isEmailDuplicate($this->validDuplicateEmail());

        $this->assertFalse($actual);
    }

    private function buildService()
    {
        return new IsEmailDuplicateService($this->jsonClient);
    }

    private function validDuplicateEmail()
    {
        return "dummy@email.com";
    }
}