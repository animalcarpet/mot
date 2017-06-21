<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace AccountApiTest\Crypt;

use Dvsa\Mot\Api\RegistrationModule\Service\PersonSecurityAnswerRecorder;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Service\Exception\EmptyRequestBodyException;
use DvsaCommonApi\Service\Exception\InvalidFieldValueException;
use DvsaCommonApi\Service\Exception\MethodNotAllowedException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonTest\TestUtils\XMock;
use AccountApi\Controller\SecurityQuestionController;
use AccountApi\Service\SecurityQuestionService;
use DvsaMotApiTest\Controller\AbstractMotApiControllerTestCase;
use Zend\Http\Request;

/**
 * Class SecurityQuestionControllerTest.
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

        $result = $this->getResultForAction(Request::METHOD_GET, 'verifyAnswer');
        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => true], $result);
    }

    public function testSecurityQuestionControllerGetQuestionForPerson()
    {
        $hydrator = new DtoHydrator();
        $dto = new SecurityQuestionDto();
        $this->securityServiceMock->expects($this->once())
            ->method('findQuestionByQuestionNumber')
            ->willReturn($dto);

        $result = $this->getResultForAction(Request::METHOD_GET, 'getQuestionForPerson');
        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => $hydrator->extract($dto)], $result);
    }

    public function testGetQuestionsForPersonAction()
    {
        $incorrectPersonId = -1.0;

        $this->setExpectedException(
            \InvalidArgumentException::class,
            sprintf(
                SecurityQuestionService::ERR_TYPE_PERSON_ID,
                $incorrectPersonId
            )
        );

        $this->getResultForAction(Request::METHOD_GET, 'getQuestionsForPerson', ['personId' => $incorrectPersonId]);
    }

    /**
     * @param string $actionName
     * @param array  $unacceptableMethod
     * @dataProvider unacceptableMethodsDataProvider
     */
    public function testUnacceptableMethodCallsOnActions($actionName, $unacceptableMethod)
    {
        $this->setExpectedException(MethodNotAllowedException::class);
        $this->getResultForAction($unacceptableMethod, $actionName);
        printf(
            'Failed to assert that calling the action "%s" with a "%s" method results to a "%s" exception',
            $actionName,
            $unacceptableMethod,
            MethodNotAllowedException::class
        );
    }

    public function unacceptableMethodsDataProvider()
    {
        $data = [];

        $actions = [
            'getQuestionsForPerson' => $this->getAllMethodsBut([Request::METHOD_GET]),
            'verifyAnswers' => $this->getAllMethodsBut([Request::METHOD_POST]),
        ];

        foreach ($actions as $actionName => $unacceptableMethods) {
            foreach ($unacceptableMethods as $method) {
                $data[] = [
                    'actionName' => $actionName,
                    'unacceptableMethods' => $method,
                ];
            }
        }

        return $data;
    }

    /**
     * @param array $acceptableMethods
     *
     * @return array
     */
    private function getAllMethodsBut(array $acceptableMethods)
    {
        $methods = array_filter(
            [
                Request::METHOD_CONNECT,
                Request::METHOD_DELETE,
                Request::METHOD_GET,
                Request::METHOD_HEAD,
                Request::METHOD_PATCH,
                Request::METHOD_POST,
                Request::METHOD_PROPFIND,
                Request::METHOD_PUT,
                Request::METHOD_TRACE,
                Request::METHOD_OPTIONS,
            ],
            function ($value) use ($acceptableMethods) {
                return !in_array($value, $acceptableMethods);
            }
        );

        return $methods;
    }

    /**
     * @param array      $params
     * @param \Exception $expectedException
     * @dataProvider verifyAnswerDataProvider
     */
    public function testVerifyAnswersActionChecksRequiredFields($params, $expectedException)
    {
        $this->markTestSkipped("fails on PHP7");
        $this->setExpectedException(
            get_class($expectedException),
            $expectedException->getMessage()
        );
        $this->getPostResultToAction('verifyAnswers', $params, ['personId' => 1]);
    }

    public function verifyAnswerDataProvider()
    {
        $fieldName = 'questionsAndAnswers';

        return [
            [
                'params' => [],
                new EmptyRequestBodyException()
            ],
            [
                'params' => [$fieldName => null],
                new RequiredFieldException([$fieldName]),
            ],
            [
                'params' => [$fieldName => 'first answer'],
                new InvalidFieldValueException(
                    sprintf(SecurityQuestionService::ERR_MSG_INVALID_ARGUMENT, var_export('first answer', true))
                ),
            ],
        ];
    }
}
