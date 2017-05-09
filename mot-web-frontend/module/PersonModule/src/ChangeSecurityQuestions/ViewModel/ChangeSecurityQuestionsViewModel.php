<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\ViewModel;

use Zend\Form\Form;

class ChangeSecurityQuestionsViewModel
{
    private $form;

    private $question;

    private $answer;

    public function getForm()
    {
        return $this->form;
    }

    public function setForm(Form $form)
    {
        $this->form = $form;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function setQuestion($question)
    {
        $this->question = $question;
    }

    public function getAnswer()
    {
        return $this->answer;
    }

    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }
}
