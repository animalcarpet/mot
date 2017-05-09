<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\ViewModel;

class ChangeSecurityQuestionReviewViewModel
{
    private $questionOneText;

    private $questionTwoText;

    public function getQuestionOneText()
    {
        return $this->questionOneText;
    }

    public function setQuestionOneText($questionOneText)
    {
        $this->questionOneText = $questionOneText;
    }

    public function getQuestionTwoText()
    {
        return $this->questionTwoText;
    }

    public function setQuestionTwoText($questionTwoText)
    {
        $this->questionTwoText = $questionTwoText;
    }
}
