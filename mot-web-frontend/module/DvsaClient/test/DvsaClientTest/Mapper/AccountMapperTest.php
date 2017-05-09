<?php

namespace DvsaClientTest\Mapper;

use DvsaClient\Mapper\AccountMapper;
use DvsaCommon\Dto\Account\MessageDto;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\AccountUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonTest\TestUtils\TestCaseTrait;

/**
 * Class AccountMapperTest.
 */
class AccountMapperTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    const USER_ID = 1;
    const USERNAME = 'tester1';
    const PASSWORD = 'Password1New';
    const TOKEN = '9999999';

    /**
     * @var AccountMapper
     */
    private $mapper;

    /** @var $client \PHPUnit_Framework_MockObject_MockBuilder */
    private $client;

    public function setUp()
    {
        $this->client = \DvsaCommonTest\TestUtils\XMock::of(Client::class, ['post', 'get', 'getWithParams', 'put']);
        $this->mapper = new AccountMapper($this->client);
    }

    public function testResetPassword()
    {
        $result = 'unit_Result';
        $url = AccountUrlBuilder::resetPassword();
        $data = ['userId' => self::USER_ID];

        //  --  mock    --
        $this->mockMethod($this->client, 'post', $this->once(), ['data' => $result], [$url, $data]);

        //  --  call    --
        $actual = $this->mapper->resetPassword(self::USER_ID);

        //  --  check   --
        $this->assertEquals($result, $actual);
    }

    public function testValidateUsername()
    {
        $this->client->expects($this->any())
            ->method('getWithParams')
            ->willReturn(['data' => self::USER_ID]);

        $this->assertEquals(1, $this->mapper->validateUsername(self::USERNAME));
    }

    public function testGetMessageByToken()
    {
        $dto = (new MessageDto())->setId(123456798);

        $result = DtoHydrator::dtoToJson($dto);
        $url = AccountUrlBuilder::resetPassword(self::TOKEN);

        //  --  mock    --
        $this->mockMethod($this->client, 'get', $this->once(), ['data' => $result], $url);

        //  --  call    --
        $actual = $this->mapper->getMessageByToken(self::TOKEN);

        //  --  check   --
        $this->assertInstanceOf(MessageDto::class, $actual);
        $this->assertEquals($dto, $actual);
    }

    public function testChangePassword()
    {
        $result = 'unit_Result';
        $url = AccountUrlBuilder::changePassword();

        //  --  mock    --
        $this->mockMethod($this->client, 'post', $this->once(), ['data' => $result], $url);

        //  --  call    --
        $actual = $this->mapper->changePassword(self::USER_ID, self::PASSWORD);

        //  --  check   --
        $this->assertEquals($result, $actual);
    }

    public function testGetPin()
    {
        $result = 'unit_Result';
        $url = UrlBuilder::claimAccount(self::USER_ID);

        //  --  mock    --
        $this->mockMethod($this->client, 'get', $this->once(), ['data' => $result], $url);

        //  --  call    --
        $actual = $this->mapper->getClaimData(self::USER_ID);

        //  --  check   --
        $this->assertEquals($result, $actual);
    }

    public function testClaimUpdate()
    {
        $result = 'unit_Result';
        $url = UrlBuilder::claimAccount(self::USER_ID);
        $data = ['key' => 'value'];

        //  --  mock    --
        $this->mockMethod($this->client, 'put', $this->once(), ['data' => $result], [$url, $data]);

        //  --  call    --
        $actual = $this->mapper->claimUpdate(self::USER_ID, $data);

        //  --  check   --
        $this->assertEquals($result, $actual);
    }
}
