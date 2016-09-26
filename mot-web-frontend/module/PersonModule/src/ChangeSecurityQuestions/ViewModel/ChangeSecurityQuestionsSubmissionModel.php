<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\ViewModel;

class ChangeSecurityQuestionsSubmissionModel
{
    private $questionOneId;

    private $questionOneAnswer;

    private $questionTwoId;

    private $questionTwoAnswer;

    /**
     * @return mixed
     */
    public function getQuestionOneId()
    {
        return $this->questionOneId;
    }

    /**
     * @param mixed $questionOneId
     * @return ChangeSecurityQuestionsSubmissionModel
     */
    public function setQuestionOneId($questionOneId)
    {
        $this->questionOneId = $questionOneId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuestionOneAnswer()
    {
        return $this->questionOneAnswer;
    }

    /**
     * @param mixed $questionOneAnswer
     * @return ChangeSecurityQuestionsSubmissionModel
     */
    public function setQuestionOneAnswer($questionOneAnswer)
    {
        $this->questionOneAnswer = $questionOneAnswer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuestionTwoId()
    {
        return $this->questionTwoId;
    }

    /**
     * @param mixed $questionTwoId
     * @return ChangeSecurityQuestionsSubmissionModel
     */
    public function setQuestionTwoId($questionTwoId)
    {
        $this->questionTwoId = $questionTwoId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuestionTwoAnswer()
    {
        return $this->questionTwoAnswer;
    }

    /**
     * @param mixed $questionTwoAnswer
     * @return ChangeSecurityQuestionsSubmissionModel
     */
    public function setQuestionTwoAnswer($questionTwoAnswer)
    {
        $this->questionTwoAnswer = $questionTwoAnswer;
        return $this;
    }
}