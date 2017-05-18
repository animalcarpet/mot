<?php

namespace Account\ViewModel;

use DvsaCommon\InputFilter\Account\SetSecurityQuestionsAndAnswersInputFilter;
use DvsaCommon\Utility\ArrayUtils;

/**
 * ViewModel for the Claim Account Review screen.
 */
class ReviewViewModel
{
    const FIELD_PASSWORD = 'password';
    const FIELD_SECURITY_QUESTIONS = 'securityQuestions';
    const FIELD_CONFIRM_PASSWORD = 'confirmPassword';
    const FIELD_SET_SECURITY_QUESTIONS = 'setSecurityQuestion';
    const FIELD_SECURITY_QUESTION_GROUP_A = 'groupA';
    const FIELD_SECURITY_QUESTION_GROUP_B = 'groupB';

    /** @var string */
    private $password;

    /** @var array */
    private $securityQuestions;

    /** @var string */
    private $securityQuestionA;

    /** @var string */
    private $securityQuestionB;

    /** @var string */
    private $answerA;

    /** @var string */
    private $answerB;

    public function setData(array $data)
    {
        $this->setSecurityQuestions(ArrayUtils::tryGet($data, self::FIELD_SECURITY_QUESTIONS));

        if (isset($data[self::FIELD_CONFIRM_PASSWORD])) {
            $confirmEmailPassword = $data[self::FIELD_CONFIRM_PASSWORD];
            $this->setPassword(ArrayUtils::tryGet($confirmEmailPassword, self::FIELD_PASSWORD));
        }

        if (isset($data[self::FIELD_SET_SECURITY_QUESTIONS])) {
            $securityQuestions = $data[self::FIELD_SET_SECURITY_QUESTIONS];
            $this->setAnswerA(ArrayUtils::tryGet(
                $securityQuestions, SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_FIRST_ANSWER
            ));
            $this->setAnswerB(ArrayUtils::tryGet(
                $securityQuestions,
                SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_SECOND_ANSWER
            ));
            $this->setSecurityQuestionA(ArrayUtils::tryGet(
                $securityQuestions,
                SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_FIRST_QUESTION
            ));
            $this->setSecurityQuestionB(ArrayUtils::tryGet(
                $securityQuestions,
                SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_SECOND_QUESTION));
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getHiddenPassword()
    {
        return str_repeat('•', strlen($this->getPassword()));
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return array
     */
    public function getSecurityQuestions()
    {
        return $this->securityQuestions;
    }

    public function getSecurityQuestionGroupAText()
    {
        if (empty($this->securityQuestions)) {
            return '';
        }

        if (isset($this->securityQuestions[self::FIELD_SECURITY_QUESTION_GROUP_A])) {
            $securityQuestionGroupA = $this->securityQuestions[self::FIELD_SECURITY_QUESTION_GROUP_A];

            if (is_array($securityQuestionGroupA)) {
                return $securityQuestionGroupA[$this->getSecurityQuestionA()];
            }
        }

        return '';
    }

    public function getSecurityQuestionGroupBText()
    {
        if (empty($this->securityQuestions)) {
            return '';
        }

        if (isset($this->securityQuestions[self::FIELD_SECURITY_QUESTION_GROUP_B])) {
            $securityQuestionGroupB = $this->securityQuestions[self::FIELD_SECURITY_QUESTION_GROUP_B];

            if (is_array($securityQuestionGroupB)) {
                return $securityQuestionGroupB[$this->getSecurityQuestionB()];
            }
        }

        return '';
    }

    /**
     * @param array $securityQuestions
     */
    public function setSecurityQuestions($securityQuestions)
    {
        $this->securityQuestions = $securityQuestions;

        return $this;
    }

    /**
     * @return string
     */
    public function getAnswerA()
    {
        return $this->answerA;
    }

    /**
     * @param string $answerA
     */
    public function setAnswerA($answerA)
    {
        $this->answerA = $answerA;

        return $this;
    }

    /**
     * @return string
     */
    public function getHiddenAnswerA()
    {
        return str_repeat('•', strlen($this->getAnswerA()));
    }

    /**
     * @return string
     */
    public function getAnswerB()
    {
        return $this->answerB;
    }

    /**
     * @param string $answerB
     */
    public function setAnswerB($answerB)
    {
        $this->answerB = $answerB;

        return $this;
    }

    /**
     * @return string
     */
    public function getHiddenAnswerB()
    {
        return str_repeat('•', strlen($this->getAnswerB()));
    }

    /**
     * @return string
     */
    public function getSecurityQuestionA()
    {
        return $this->securityQuestionA;
    }

    /**
     * @param string $securityQuestionA
     */
    public function setSecurityQuestionA($securityQuestionA)
    {
        $this->securityQuestionA = $securityQuestionA;
    }

    /**
     * @return string
     */
    public function getSecurityQuestionB()
    {
        return $this->securityQuestionB;
    }

    /**
     * @param string $securityQuestionB
     */
    public function setSecurityQuestionB($securityQuestionB)
    {
        $this->securityQuestionB = $securityQuestionB;
    }
}
