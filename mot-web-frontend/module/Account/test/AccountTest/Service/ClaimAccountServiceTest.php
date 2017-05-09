<?php

namespace AccountTest\Service;

use Account\Service\ClaimAccountService;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use DvsaClient\Mapper\AccountMapper;
use DvsaClient\Mapper\SecurityQuestionMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Domain\SecurityQuestionGroup;
use DvsaCommon\Dto\Account\ClaimStartDto;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\Session\Container;
use Zend\Stdlib\Parameters;

/**
 * Class ClaimAccountServiceTest.
 */
class ClaimAccountServiceTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    const USER_ID = 9999;
    const PASSWORD = 'unit_Password';
    const QUESTION_1_ID = 8101;
    const QUESTION_2_ID = 8102;
    const QUESTION_1_ANSWER = 'unit_QuestionAnswer1';
    const QUESTION_2_ANSWER = 'unit_QuestionAnswer2';
    const PIN = 123456;

    /** @var ClaimAccountService */
    private $claimAccountService;
    /** @var Identity|MockObj */
    private $mockIdentity;
    /** @var MotFrontendAuthorisationServiceInterface|MockObj */
    private $mockAuthSrv;
    /** @var MapperFactory|MockObj */
    private $mockMapper;
    /** @var ParamObfuscator|MockObj */
    private $mockParamObfuscator;

    /** @var AccountMapper|MockObj */
    private $mockAccountMapper;
    /** @var SecurityQuestionMapper|MockObj */
    private $mockSecurityQuestionMapper;

    public function setUp()
    {
        $this->mockAuthSrv = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $this->mockIdentity = XMock::of(Identity::class, ['getUserId', 'getUserName']);
        $this->mockMapper = $this->getMapperFactory();
        $this->mockParamObfuscator = XMock::of(ParamObfuscator::class);

        $this->claimAccountService = new ClaimAccountService(
            $this->mockAuthSrv,
            $this->mockIdentity,
            $this->mockMapper,
            $this->mockParamObfuscator
        );

        $claimStartDto = (new ClaimStartDto())->setPin(self::PIN);
        $this->mockAccountMapper
            ->expects($this->any())
            ->method('getClaimData')
            ->will($this->returnValue($claimStartDto));
    }

    public function testSaveOnSession()
    {
        $sampleData = $this->getSampleData();

        $sessionAsArray = $this->claimAccountService->sessionToArray();

        $pinNumberBeforeSave = $sessionAsArray[ClaimAccountService::KEY_NAME_PIN];

        foreach ($sampleData as $key => $value) {
            $this->claimAccountService->saveOnSession($key, $value);
        }

        $sessionAsArray = $this->claimAccountService->sessionToArray();
        $pinNumberAfterSave = $sessionAsArray['pin'];
        $this->unPin($sessionAsArray);

        $this->assertEquals(
            $sampleData,
            $sessionAsArray
        );

        $this->assertEquals($pinNumberBeforeSave, $pinNumberAfterSave);
    }

    public function testMarkClaimedSuccessfully()
    {
        $this->mockIdentity->setAccountClaimRequired(true);

        $this->assertTrue($this->mockIdentity->isAccountClaimRequired());
        $this->claimAccountService->markClaimedSuccessfully();
        $this->assertFalse($this->mockIdentity->isAccountClaimRequired());
    }

    public function testGetFromSession()
    {
        $sampleData = $this->getSampleData();

        foreach ($sampleData as $key => $value) {
            $this->claimAccountService->saveOnSession($key, $value);

            $this->assertEquals($value, $this->claimAccountService->getFromSession($key));
        }
    }

    public function testSessionToArray()
    {
        $sampleData = $this->getSampleData();

        foreach ($sampleData as $key => $value) {
            $this->claimAccountService->saveOnSession($key, $value);
        }

        $sessionAsArray = $this->claimAccountService->sessionToArray();

        $this->assertTrue(is_array($sessionAsArray));
        $this->assertEquals(
            array_slice($sampleData, 0, 3),
            array_slice($sessionAsArray, 0, 3)
        );
    }

    public function testCaptureStep()
    {
        $sampleData = $this->getSampleData();

        $postData = array_slice($sampleData, 3, 2);
        $postData['submitted_step'] = 'firstStep';
        $mockPost = new Parameters($postData);

        foreach (array_slice($sampleData, 0, 3) as $key => $value) {
            $this->claimAccountService->saveOnSession($key, $value);
        }

        $this->claimAccountService->sessionToArray();

        $this->claimAccountService->captureStep($mockPost);
        $dataOnSession = $this->claimAccountService->sessionToArray();

        $this->assertEquals(
            [
                'user_id' => self::USER_ID,
                'is_tester' => false,
                'username' => 'tester1',
                'firstStep' => [
                    'password' => self::PASSWORD,
                    'password-confirm' => self::PASSWORD,
                    'submitted_step' => 'firstStep',
                    'username' => null,
                ],
            ],
            $this->unPin($dataOnSession)
        );
    }

    public function testClearSession()
    {
        $this->claimAccountService->clearSession();
        $this->assertEmpty($this->claimAccountService->sessionToArray());
    }

    public function testCheckIfStepsRecorded()
    {
        $service = $this->claimAccountService;

        $stepOneName = 'step_1_name';
        $stepTwoName = 'step_2_name';
        $stepThreeName = 'step_3_name';

        $this->assertFalse($service->isStepRecorded($stepOneName));
        $this->assertFalse($service->isStepRecorded($stepTwoName));
        $this->assertFalse($service->isStepRecorded($stepThreeName));

        $service->saveOnSession($stepOneName, 'some string');

        $this->assertTrue($service->isStepRecorded($stepOneName));
        $this->assertFalse($service->isStepRecorded($stepTwoName));
        $this->assertFalse($service->isStepRecorded($stepThreeName));

        $service->saveOnSession($stepTwoName, ['some', 'items', 'in', 'an', 'array']);

        $this->assertTrue($service->isStepRecorded($stepOneName));
        $this->assertTrue($service->isStepRecorded($stepTwoName));
        $this->assertFalse($service->isStepRecorded($stepThreeName));

        $service->saveOnSession($stepThreeName, []);

        $this->assertTrue($service->isStepRecorded($stepOneName));
        $this->assertTrue($service->isStepRecorded($stepTwoName));
        $this->assertTrue($service->isStepRecorded($stepThreeName));
    }

    /**
     * @dataProvider dataProviderTestGetSecurityQuestions
     */
    public function testGetSecurityQuestions($questions, $expect)
    {
        //  --  mock    --
        $dtos = [];
        foreach ($questions as $item) {
            $dtos[] = (new SecurityQuestionDto())
                ->setId($item[0])
                ->setText($item[1])
                ->setGroup($item[2]);
        }

        $this->mockMethod($this->mockSecurityQuestionMapper, 'fetchAll', $this->once(), $dtos);

        //  --  call    --
        $actual = $this->claimAccountService->getSecurityQuestions();

        //  --  check   --
        $this->assertEquals($expect, $actual);
    }

    public function dataProviderTestGetSecurityQuestions()
    {
        return [
            [
                'questions' => [
                    [9001, 'unit_questionA1', SecurityQuestionGroup::GROUP_ONE],
                    [8001, 'unit_questionB1', SecurityQuestionGroup::GROUP_TWO],
                    [9002, 'unit_questionA2', SecurityQuestionGroup::GROUP_ONE],
                ],
                'expect' => [
                    'groupA' => [
                        9001 => 'unit_questionA1',
                        9002 => 'unit_questionA2',
                    ],
                    'groupB' => [
                        8001 => 'unit_questionB1',
                    ],
                ],
            ],
            [
                'questions' => [
                    [8001, 'unit_questionB1', SecurityQuestionGroup::GROUP_TWO],
                    [8002, 'unit_questionB2', SecurityQuestionGroup::GROUP_TWO],
                ],
                'expect' => [
                    'groupA' => [],
                    'groupB' => [
                        8001 => 'unit_questionB1',
                        8002 => 'unit_questionB2',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestSendToApi
     */
    public function testSendToApi($apiResult, $expect)
    {
        //  --  mock    --
        $this->mockMethod($this->mockIdentity, 'getUserId', $this->once(), self::USER_ID);
        $this->mockMethod($this->mockAccountMapper, 'claimUpdate', $this->once(), $apiResult);

        //  --  call    --
        $actual = $this->claimAccountService->sendToApi($this->getSampleSessionData());

        //  --  check   --
        $this->assertEquals($expect, $actual);
    }

    public function dataProviderTestSendToApi()
    {
        return [
            [
                'apiResult' => false,
                'expect' => false,
            ],
            [true, true],
        ];
    }

    public function testGetPrepareDataForApi()
    {
        //  ----  prepare data    ----
        //  --  session data    --
        $sessionData = $this->getSampleSessionData();

        //  --  prepared data --
        $passwordObf = 'unit_password_Obfuscated';

        $answer1Obf = 'unit_answer_A_Obfuscated';
        $answer2Obf = 'unit_answer_B_Obfuscated';

        $expect = [
            'personId' => self::USER_ID,
            'password' => self::PASSWORD,
            'passwordConfirmation' => self::PASSWORD,

            'securityQuestionOneId' => self::QUESTION_1_ID,
            'securityAnswerOne' => self::QUESTION_1_ANSWER,

            'securityQuestionTwoId' => self::QUESTION_2_ID,
            'securityAnswerTwo' => self::QUESTION_2_ANSWER,
        ];

        //  --  call    --
        $actual = XMock::invokeMethod($this->claimAccountService, 'prepareDataForApi', [$sessionData]);

        //  --  check   --
        $this->assertEquals($expect, $actual);
    }

    public function testClaimGetSessionInitClaimWhenNoDataInSession()
    {
        // Expected
        $this->mockAccountMapper
            ->expects($this->once())
            ->method('getClaimData')
            ->will($this->returnValue(new ClaimStartDto()));

        // When
        $session = $this->claimAccountService->getSession();
    }

    public function testGetPinReturnsPinFromStartData()
    {
        // Given
        $expectedPin = 123456;
        $claimInitData = (new ClaimStartDto())->setPin($expectedPin);
        $this->mockAccountMapper
            ->expects($this->any())
            ->method('getClaimData')
            ->will($this->returnValue($claimInitData));

        // When
        $result = $this->claimAccountService->getFromSession(ClaimAccountService::KEY_NAME_PIN);

        // Then
        $this->assertEquals($expectedPin, $result);
    }

    public function testGetPinReturnShouldFromSessionIfInitialised()
    {
        // Given
        $expectedPin = 98765;
        $session = new Container(self::class);
        $session->offsetSet(ClaimAccountService::KEY_NAME_PIN, $expectedPin);
        XMock::mockClassField($this->claimAccountService, 'sessionContainer', $session);

        // When
        $result = $this->claimAccountService->getFromSession(ClaimAccountService::KEY_NAME_PIN);

        // Then
        $this->assertEquals($expectedPin, $result);
    }

    public function getSampleData()
    {
        return [
            'user_id' => self::USER_ID,
            'is_tester' => false,
            'username' => 'tester1',
            'password' => self::PASSWORD,
            'password-confirm' => self::PASSWORD,
            'security-question-one-id' => self::QUESTION_1_ID,
            'security-answer-one-id' => self::QUESTION_1_ANSWER,
            'security-question-two-id' => self::QUESTION_2_ID,
            'security-answer-two-id' => self::QUESTION_2_ANSWER,
        ];
    }

    public function getSampleSessionData()
    {
        return [
            'user_id' => self::USER_ID,
            'is_tester' => true,
            'username' => 'tester1',
            'pin' => '908986',
            'confirmPassword' => [
                'username' => 'tester1',
                'password' => self::PASSWORD,
                'confirm_password' => self::PASSWORD,
                '_csrf_token' => 'F590F72E-D9FB-82FD-493E-D661EC0CF06E',
                'submitted_step' => 'confirmPassword',
                'btSubmitForm' => '',
            ],
            'setSecurityQuestion' => [
                'question_a' => self::QUESTION_1_ID,
                'answer_a' => self::QUESTION_1_ANSWER,
                'question_b' => self::QUESTION_2_ID,
                'answer_b' => self::QUESTION_2_ANSWER,
                'btSubmitForm' => '',
                '_csrf_token' => 'F590F72E-D9FB-82FD-493E-D661EC0CF06E',
                'submitted_step' => 'setSecurityQuestion',
            ],
        ];
    }

    private function unPin(&$dataSet)
    {
        unset($dataSet['pin']);

        return $dataSet;
    }

    private function getMapperFactory()
    {
        $mapperFactory = XMock::of(MapperFactory::class);

        $this->mockAccountMapper = XMock::of(AccountMapper::class);
        $this->mockSecurityQuestionMapper = XMock::of(SecurityQuestionMapper::class, ['fetchAll']);

        $map = [
            [MapperFactory::ACCOUNT, $this->mockAccountMapper],
            [MapperFactory::SECURITY_QUESTION, $this->mockSecurityQuestionMapper],
        ];

        $mapperFactory->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mapperFactory;
    }
}
