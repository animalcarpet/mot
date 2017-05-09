<?php

namespace DvsaClientTest\Mapper;

use DvsaClient\Mapper\UserAdminMapper;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaCommon\Dto\Person\SearchPersonResultDto;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\HttpRestJson\Client;

/**
 * Class UserAdminMapperTest.
 */
class UserAdminMapperTest extends \PHPUnit_Framework_TestCase
{
    const PERSON_ID = 1;
    const QUESTION_ID = 1;
    const ANSWER = 'answer';

    /**
     * @var UserAdminMapper
     */
    private $mapper;

    /** @var $client \PHPUnit_Framework_MockObject_MockBuilder */
    private $client;

    public function setUp()
    {
        $this->client = \DvsaCommonTest\TestUtils\XMock::Of(Client::class, ['get', 'getWithParams', 'post']);
        $this->mapper = new UserAdminMapper($this->client);
    }

    public function testGetSecurityQuestion()
    {
        $this->client->expects($this->any())
            ->method('get')
            ->willReturn(['data' => ['_class' => 'DvsaCommon\\Dto\\Security\\SecurityQuestionDto']]);
        $this->assertInstanceOf(
            SecurityQuestionDto::class,
            $this->mapper->getSecurityQuestion(self::QUESTION_ID, self::PERSON_ID)
        );
    }

    public function testCheckSecurityQuestion()
    {
        $this->client->expects($this->any())
            ->method('getWithParams')
            ->willReturn(['data' => ['_class' => 'DvsaCommon\Dto\Security\SecurityQuestionDto']]);
        $this->assertInstanceOf(
            SecurityQuestionDto::class,
            $this->mapper->checkSecurityQuestion(self::QUESTION_ID, self::PERSON_ID, self::ANSWER)
        );
    }

    public function testGetUserProfile()
    {
        $result = [
            '_class' => 'DvsaCommon\\Dto\\Person\\PersonHelpDeskProfileDto',
            'title' => '',
            'userName' => '',
            'firstName' => '',
            'middleName' => '',
            'lastName' => '',
            'dateOfBirth' => '',
            'email' => '',
            'telephone' => '',
            'town' => '',
            'postcode' => '',
            'addressLine1' => '',
            'addressLine2' => '',
            'addressLine3' => '',
            'addressLine4' => '',
            'roles' => [],
            'drivingLicence' => '',
            'drivingLicenceRegion' => '',
            'authenticationMethod' => [],
        ];
        $this->client->expects($this->any())
            ->method('get')
            ->willReturn(['data' => $result]);
        $this->assertInstanceOf(
            PersonHelpDeskProfileDto::class,
            $this->mapper->getUserProfile(self::PERSON_ID, false)
        );
    }

    public function testSearchUsers()
    {
        $result = [
            '_class' => 'DvsaCommon\\Dto\\Person\\SearchPersonResultDto ',
            'id' => '',
            'title' => '',
            'userName' => '',
            'firstName' => '',
            'middleName' => '',
            'lastName' => '',
            'dateOfBirth' => '',
            'email' => '',
            'telephone' => '',
            'town' => '',
            'postcode' => '',
            'addressLine1' => '',
            'addressLine2' => '',
            'addressLine3' => '',
            'addressLine4' => '',
            'username' => '',
        ];
        $this->client->expects($this->any())
            ->method('get')
            ->willReturn(['data' => [$result]]);
        $this->assertInstanceOf(
            SearchPersonResultDto::class,
            $this->mapper->searchUsers(['username' => 'tester1'])[0]
        );
    }

    public function testResetClaimAccount()
    {
        $this->client->expects($this->any())
            ->method('get')
            ->willReturn(true);
        $this->assertTrue($this->mapper->resetClaimAccount(self::PERSON_ID));
    }

    public function testPostMessage()
    {
        $this->client->expects($this->any())
            ->method('post')
            ->willReturn(true);
        $this->assertTrue($this->mapper->postMessage([]));
    }
}
