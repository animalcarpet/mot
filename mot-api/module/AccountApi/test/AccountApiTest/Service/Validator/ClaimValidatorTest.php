<?php

namespace AccountApiTest\Service\Validator;

use AccountApi\Service\SecurityQuestionService;
use AccountApi\Service\Validator\ClaimValidator;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\SecurityQuestion;
use DvsaEntities\Repository\SecurityQuestionRepository;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class ClaimValidatorTest extends AbstractServiceTestCase
{
    const SECURITY_QUESTION_ONE_ID = 1;
    const SECURITY_QUESTION_TWO_ID = 2;

    use TestCaseTrait;

    /** @var ClaimValidator */
    protected $validator;
    protected $mockSecurityRepository;
    /** @var  SecurityQuestionService|MockObj */
    protected $mockSecurityQuestionService;
    protected $mockEntityManager;
    /** @var  ParamObfuscator|MockObj */
    private $mockParamObfuscator;

    public function setUp()
    {
        $this->mockSecurityRepository = $this->getMockRepository(SecurityQuestionRepository::class);
        $this->mockMethod($this->mockSecurityRepository, 'find', null, $this->getMockSecurityQuestion());

        $this->mockEntityManager = $this->getMockEntityManager();
        $this->mockParamObfuscator = XMock::of(ParamObfuscator::class);

        $this->mockSecurityQuestionService = XMock::of(SecurityQuestionService::class);

        $this->validator = new ClaimValidator(
            $this->mockSecurityQuestionService,
            $this->mockSecurityRepository
        );
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function testSpecifyingNoParametersReturnsException()
    {
        $this->validator->validate();
    }

    public function testSpecifyingEmailWithNoValueReturnNoError()
    {
        $params = $this->params();
        $params['email'] = '';
        $params['emailConfirmation'] = '';
        $params['emailOptOut'] = true;

        $this->validator->validate($params);
    }

    public function testValidParametersPassValidation()
    {
        $params = $this->params();

        $this->validator->validate($params);
    }

    public function testPasswordNotMatchExceptionError()
    {
        $this->setExpectedException('Exception');

        $params = $this->params();
        $params['passwordConfirmation'] = 'password2';

        $this->validator->validate($params);
    }

    public function testPasswordConfirmationNotSentExceptionError()
    {
        $this->setExpectedException('Exception');

        $params = $this->params();
        unset($params['passwordConfirmation']);

        $this->validator->validate($params);
    }

    public function testPasswordNotMoreThanEightCharsLongExceptionError()
    {
        $this->setExpectedException('Exception');

        $params = $this->params();
        $params['password'] = 'abc';
        $params['passwordConfirmation'] = 'abc';

        $this->validator->validate($params);
    }

    public function testPasswordMustHaveAnIntegerOrExceptionError()
    {
        $this->setExpectedException('Exception');

        $params = $this->params();
        $params['password'] = 'password';
        $params['passwordConfirmation'] = 'password';

        $this->validator->validate($params);
    }

    public function testPasswordMustHaveAtLeastOneUpperCaseCharacterOrExceptionError()
    {
        $this->setExpectedException('Exception');

        $params = $this->params();
        $params['password'] = 'password';
        $params['passwordConfirmation'] = 'password';

        $this->validator->validate($params);
    }

    public function testPasswordMustHaveBothUpperAndLowerCharsOrExceptionError()
    {
        $this->setExpectedException('Exception');

        $params = $this->params();
        $params['password'] = 'password1';
        $params['passwordConfirmation'] = 'password1';

        $this->validator->validate($params);
    }

    public function testPasswordHasBothUpperAndLowerCharsReturnValid()
    {
        $params = $this->params();
        $this->validator->validate($params);
    }

    public function testSecurityQuestionReceivedExist()
    {
        $params = $this->params();
        $params['securityQuestionOneId'] = self::SECURITY_QUESTION_ONE_ID;
        $params['securityQuestionOneId'] = self::SECURITY_QUESTION_TWO_ID;

        $securityRepositoryMock = $this
            ->getMockBuilder(SecurityQuestionRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $securityRepositoryMock
            ->expects($this->once())
            ->method('findAllByIds')
            ->willReturn([
                self::SECURITY_QUESTION_ONE_ID => $this->getMockSecurityQuestion(self::SECURITY_QUESTION_ONE_ID),
                self::SECURITY_QUESTION_TWO_ID => $this->getMockSecurityQuestion(self::SECURITY_QUESTION_TWO_ID),
            ]);

        $validator = new ClaimValidator(
            $this->mockSecurityQuestionService,
            $securityRepositoryMock
        );

        $validator->validateSecurityQuestions($params);
    }

    public function testSecurityQuestionReceivedDoesntExist()
    {
        $this->setExpectedException('Exception');

        $params = $this->params();
        $params['securityQuestionOneId'] = self::SECURITY_QUESTION_TWO_ID;

        $securityRepositoryMock = $this
            ->getMockBuilder(SecurityQuestionRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $securityRepositoryMock
            ->expects($this->once())
            ->method('findAllByIds')
            ->willReturn([]);

        $validator = new ClaimValidator(
            $this->mockSecurityQuestionService,
            $securityRepositoryMock
        );

        $validator->validateSecurityQuestions($params);
    }



    /**
     * @param int $id
     *
     * @return SecurityQuestion
     */
    public function getMockSecurityQuestion($id = self::SECURITY_QUESTION_ONE_ID)
    {
        $securityQuestion = new SecurityQuestion('Who am I?', $id);
        $securityQuestion->setId($id);

        return $securityQuestion;
    }

    protected function params()
    {
        return [
            'personId'              => 5,
            'email'                 => 'claimvalidatortest@' . EmailAddressValidator::TEST_DOMAIN,
            'emailConfirmation'     => 'claimvalidatortest@' . EmailAddressValidator::TEST_DOMAIN,
            'emailOptOut'           => false,
            'password'              => 'Password1',
            'passwordConfirmation'  => 'Password1',
            'securityQuestionOneId' => '1',
            'securityAnswerOne'     => 'I got the answer',
            'securityQuestionTwoId' => '1',
            'securityAnswerTwo'     => 'I got the answer as well'
        ];
    }
}
