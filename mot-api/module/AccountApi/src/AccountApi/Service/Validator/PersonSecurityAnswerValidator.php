<?php

namespace AccountApi\Service\Validator;

use DvsaCommon\Crypt\Hash\BCryptHashFunction;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaEntities\Repository\SecurityQuestionRepository;

class PersonSecurityAnswerValidator extends AbstractValidator
{
    private $securityQuestionRepository;

    public function __construct(SecurityQuestionRepository $securityQuestionRepository, $errors = null)
    {
        parent::__construct($errors);

        $this->securityQuestionRepository = $securityQuestionRepository;
    }

    public function validate($data = [])
    {
        if (!$this->validateBasicStructure($data)) {
            $this->errors->add('Expecting array of two elements, each with a questionId and answer');
        } else {
            if (!$this->validateAnswerLength($data)) {
                $this->errors->add('Answers must not be more than ' . BCryptHashFunction::MAX_SECRET_LENGTH . ' characters');
            }
            if (!$this->validateQuestionsExist($data)) {
                $this->errors->add('Security question(s) not found');
            }
        }

        $this->errors->throwIfAny();
    }

    private function validateBasicStructure($data)
    {
        return
            is_array($data) &&
            count($data) == 2 &&
            is_array($data[0]) && is_array($data[1]) &&
            array_key_exists('questionId', $data[0]) && array_key_exists('questionId', $data[1]) &&
            array_key_exists('answer', $data[0]) && array_key_exists('answer', $data[1]) ;
    }

    private function validateAnswerLength($data)
    {
        foreach ($data as $answerData) {
            if (strlen($answerData['answer']) > BCryptHashFunction::MAX_SECRET_LENGTH) {
                return false;
            }
        }

        return true;
    }

    private function validateQuestionsExist($data)
    {
        $questionIds = [$data[0]['questionId'], $data[1]['questionId']];

        $securityQuestions = $this
            ->securityQuestionRepository
            ->findAllByIds($questionIds);

        foreach ($questionIds as $questionId) {
            if (!isset($securityQuestions[$questionId])) {
                return false;
            }
        }

        return true;
    }
}
