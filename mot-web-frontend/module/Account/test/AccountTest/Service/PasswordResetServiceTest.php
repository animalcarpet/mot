<?php

namespace AccountTest\Service;

use CoreTest\Service\AbstractFrontendServiceTestCase;
use DvsaClient\Entity\Person;
use Account\Service\PasswordResetService;
use DvsaClient\Mapper\AccountMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Dto\Account\MessageDto;
use DvsaCommonTest\TestUtils\XMock;

/**
 * Class ResetServiceTest.
 *
 * Class PasswordResetServiceTest
 */
class PasswordResetServiceTest extends AbstractFrontendServiceTestCase
{
    const PERSON_ID = 99999;
    const USER_NAME = 'unit_userName';
    const TOKEN = 'unit_token12345';

    /** @var PasswordResetService */
    private $resetService;
    /** @var MapperFactory */
    private $mockMapperFactory;
    /** @var AccountMapper */
    private $mockAccountMapper;

    public function setUp()
    {
        $this->mockMapperFactory = $this->getMapperFactory();

        $this->resetService = new PasswordResetService($this->mockMapperFactory);
    }

    public function testValidateUsername()
    {
        $person = new Person();
        $person->setId(self::PERSON_ID);

        $this->mockMethod($this->mockAccountMapper, 'validateUsername', null, $person, self::USER_NAME);

        $this->assertEquals($person, $this->resetService->validateUsername(self::USER_NAME));
    }

    public function testGetToken()
    {
        //  --  check if token is NOT empty --
        $message = new MessageDto();
        $message->setId(self::TOKEN);

        $this->mockMethod($this->mockAccountMapper, 'getMessageByToken', null, $message, self::TOKEN);

        $this->assertEquals($message, $this->resetService->getToken(self::TOKEN));

        //  --  check if token is empty --
        $this->assertNull($this->resetService->getToken(''));
    }

    private function getMapperFactory()
    {
        $mapperFactory = XMock::of(MapperFactory::class);

        $this->mockAccountMapper = XMock::of(AccountMapper::class);

        $map = [
            [MapperFactory::ACCOUNT, $this->mockAccountMapper],
        ];

        $mapperFactory->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mapperFactory;
    }
}
