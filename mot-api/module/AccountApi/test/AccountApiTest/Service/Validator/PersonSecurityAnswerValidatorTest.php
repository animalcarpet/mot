<?php

namespace AccountApiTest\Service\Validator;

use AccountApi\Service\Validator\PersonSecurityAnswerValidator;
use DvsaCommon\Crypt\Hash\BCryptHashFunction;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\SecurityQuestion;
use DvsaEntities\Repository\SecurityQuestionRepository;
use PHPUnit_Framework_TestCase;

class PersonSecurityAnswerValidatorTest extends PHPUnit_Framework_TestCase
{
    private $securityQuestionRepository;

    public function setUp()
    {
        $this->securityQuestionRepository = XMock::of(SecurityQuestionRepository::class);
    }

    /**
     * @dataProvider invalidDataStructure
     */
    public function testExceptionThrownIfStructureNotValid($data)
    {
        $this->setExpectedException(BadRequestException::class);

        $validator = new PersonSecurityAnswerValidator($this->securityQuestionRepository);
        $validator->validate($data);
    }

    public function invalidDataStructure()
    {
        return [
            ["not even an array"],
            [[]],
            [[[],[]]],
            [[["questionId" => 123],["answer" => "this is an answer"]]],
            [[["questionId" => 123, "answer" => "this is an answer"],["questionId" => 321]]],
            [[["questionId" => 123, "answer" => "this is an answer"],["questionId", "answer"]]],
            [[["q" => 123, "a" => "this is an answer"],["q" => 321, "a" => "this is an answer"]]]
        ];
    }

    public function testExceptionThrownIfAnswerTooLong()
    {
        $this->setExpectedException(BadRequestException::class);

        $data = [
            ["questionId" => 123, "answer" => "this is an answer"],
            ["questionId" => 321, "answer" => str_pad('', BCryptHashFunction::MAX_SECRET_LENGTH+1)]
        ];

        $validator = new PersonSecurityAnswerValidator($this->securityQuestionRepository);
        $validator->validate($data);
    }

    public function testExceptionThrownIfQuestionsDoNotExist()
    {
        $this->setExpectedException(BadRequestException::class);

        $this->withNoSecurityQuestionsFoundForGivenIds();

        $data = [
            ["questionId" => 888, "answer" => "this is an answer"],
            ["questionId" => 999, "answer" => "this is an answer"]
        ];

        $validator = new PersonSecurityAnswerValidator($this->securityQuestionRepository);
        $validator->validate($data);
    }

    public function testNoExceptionThrownIfValid()
    {
        $this->withSecurityQuestionsFoundForGivenIds([123, 321]);

        $data = [
            ["questionId" => 123, "answer" => "this is an answer"],
            ["questionId" => 321, "answer" => "this is an answer"]
        ];

        $validator = new PersonSecurityAnswerValidator($this->securityQuestionRepository);
        $validator->validate($data);
    }

    private function withSecurityQuestionsFoundForGivenIds($questionIds)
    {
        $this->securityQuestionRepository
            ->expects($this->any())
            ->method('findAllByIds')
            ->willReturn([
                $questionIds[0] => new SecurityQuestion(),
                $questionIds[1] => new SecurityQuestion()
            ]);

        return $this;
    }

    private function withNoSecurityQuestionsFoundForGivenIds()
    {
        $this->securityQuestionRepository
            ->expects($this->any())
            ->method('findAllByIds')
            ->willReturn([]);

        return $this;
    }
}
