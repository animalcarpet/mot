<?php

namespace AccountApiTest\Service;

use AccountApi\Service\OpenAmIdentityService;
use Dvsa\OpenAM\Exception\OpenAMClientException;
use Dvsa\OpenAM\OpenAMClient;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Service\PasswordExpiryNotificationService;

class OpenAmIdentityServiceTest extends AbstractServiceTestCase
{
    const TEST_REALM = 'mot';
    const TEST_USERNAME = 'test-username';
    const TEST_PASSWORD = 'test-password';
    const MOCKED_METHOD = 'updateIdentity';

    /**
     * @var OpenAmIdentityService
     */
    protected $openAmIdentityService;

    /**
     * @var OpenAMClient|MockObj
     */
    protected $mockedOpenAmClient;

    protected function setUp()
    {
        $this->mockedOpenAmClient = XMock::of(OpenAMClient::class, [self::MOCKED_METHOD]);
        $this->openAmIdentityService = new OpenAmIdentityService(
            $this->mockedOpenAmClient,
            $this->createPasswordExpiryNotificationService(),
            self::TEST_REALM
        );
    }

    /**
     * @expectedException \AccountApi\Service\Exception\OpenAmChangePasswordException
     */
    public function testThrowsChangePasswordExceptionWhenOpenAMCallFails()
    {
        $openAmClientMock = $this
            ->getMockBuilder(OpenAMClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $openAmClientMock
            ->expects($this->once())
            ->method('updateIdentity')
            ->will($this->throwException(new OpenAMClientException('Exception message')));

        $openAmIdentityService = new OpenAmIdentityService(
            $openAmClientMock,
            $this->createPasswordExpiryNotificationService(),
            self::TEST_REALM
        );
        $openAmIdentityService->changePassword(self::TEST_USERNAME, self::TEST_PASSWORD);
    }

    public function testChangesPasswordSuccessfully()
    {
        $this->mockMethod($this->mockedOpenAmClient, self::MOCKED_METHOD, $this->once());

        $this->openAmIdentityService->changePassword(self::TEST_USERNAME, self::TEST_PASSWORD);
    }

    public function testLockAccountIsSuccessful()
    {
        $openAmClientMock = $this
            ->getMockBuilder(OpenAMClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $openAmClientMock
            ->expects($this->once())
            ->method('unlockAccount')
            ->willReturn(true);

        $openAmIdentityService = new OpenAmIdentityService(
            $openAmClientMock,
            $this->createPasswordExpiryNotificationService(),
            self::TEST_REALM
        );

        $this->assertTrue($openAmIdentityService->unlockAccount(self::TEST_USERNAME));
    }

    private function createPasswordExpiryNotificationService()
    {
        return XMock::of(PasswordExpiryNotificationService::class);
    }
}
