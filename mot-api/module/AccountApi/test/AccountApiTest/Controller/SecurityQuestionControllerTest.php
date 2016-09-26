<?php

namespace AccountApiTest\Crypt;

use Dvsa\Mot\Api\RegistrationModule\Service\PersonSecurityAnswerRecorder;
use DvsaCommon\Crypt\Hash\BCryptHashFunction;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonTest\TestUtils\XMock;
use AccountApi\Controller\SecurityQuestionController;
use AccountApi\Service\SecurityQuestionService;
use DvsaMotApiTest\Controller\AbstractMotApiControllerTestCase;

/**
 * Class SecurityQuestionControllerTest
 * @package AccountApiTest\Crypt
 */
class SecurityQuestionControllerTest extends AbstractMotApiControllerTestCase
{
    private $securityServiceMock;

    private $personSecurityAnswerRecorderMock;

    public function setUp()
    {
        $this->securityServiceMock = XMock::of(
            SecurityQuestionService::class,
            ['getAll', 'isAnswerCorrect', 'findQuestionByQuestionNumber']
        );

        $this->personSecurityAnswerRecorderMock = XMock::of(PersonSecurityAnswerRecorder::class);

        $this->setController(
            new SecurityQuestionController($this->securityServiceMock, $this->personSecurityAnswerRecorderMock)
        );

        parent::setUp();

        $this->serviceManager->setService(SecurityQuestionService::class, $this->securityServiceMock);
    }

    public function testSecurityQuestionControllerGetList()
    {
        $hydrator = new DtoHydrator();
        $dto = new SecurityQuestionDto();

        $this->securityServiceMock->expects($this->once())
            ->method('getAll')
            ->willReturn([$dto]);

        $result = $this->controller->dispatch($this->request);
        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => [$hydrator->extract($dto)]], $result);
    }

    public function testSecurityQuestionControllerVerifyAnswer()
    {
        $this->securityServiceMock->expects($this->once())
            ->method('isAnswerCorrect')
            ->willReturn(true);

        $result = $this->getResultForAction('GET', 'verifyAnswer');
        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => true], $result);
    }

    public function testSecurityQuestionControllerGetQuestionForPerson()
    {
        $hydrator = new DtoHydrator();
        $dto = new SecurityQuestionDto();
        $this->securityServiceMock->expects($this->once())
            ->method('findQuestionByQuestionNumber')
            ->willReturn($dto);

        $result = $this->getResultForAction('GET', 'getQuestionForPerson');
        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => $hydrator->extract($dto)], $result);
    }
}
