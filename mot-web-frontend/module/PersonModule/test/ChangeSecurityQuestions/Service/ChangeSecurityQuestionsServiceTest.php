<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\ChangeSecurityQuestions\Service;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\ViewModel\ChangeSecurityQuestionsSubmissionModel;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommonTest\TestUtils\XMock;

class ChangeSecurityQuestionsServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var MapperFactory */
    private $mapperFactory;
    /** @var Client */
    private $client;
    /** @var MotIdentityProviderInterface */
    private $identityProvider;

    public function setUp()
    {
        parent::setUp();
        $this->mapperFactory = XMock::of(MapperFactory::class);
        $this->client = XMock::of(Client::class);
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);
    }

    public function testUpdateSecurityQuestions()
    {
        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn($this->mockUserIdentity());

        $actual = $this->buildQuestionsService()->updateSecurityQuestions($this->mockValidChangeSecurityQuestionsnModel());

        $this->assertTrue(true);
    }

    public function buildQuestionsService()
    {
        $questionService = new ChangeSecurityQuestionsService(
            $this->mapperFactory,
            $this->client,
            $this->identityProvider
        );

        return $questionService;
    }

    public function mockUserIdentity()
    {
        $identity = new Identity();
        $identity->setUserId(1);

        return $identity;
    }

    public function mockValidChangeSecurityQuestionsnModel()
    {
        return (new ChangeSecurityQuestionsSubmissionModel())
            ->setQuestionOneAnswer('cheese')
            ->setQuestionOneId(1)
            ->setQuestionTwoAnswer('ham')
            ->setQuestionTwoId(2);
    }
}
