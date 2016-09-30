<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Step;

use DvsaCommon\InputFilter\Registration\SecurityQuestionFirstInputFilter;

class SecurityQuestionOneStep extends AbstractRegistrationStep
{
    /**
     * const used for the session key, available via getId().
     */
    const STEP_ID = "SECURITY_QUESTION_ONE";

    const QUESTIONS_GROUP = 'securityQuestionsGroupA';

    /**
     * @var string
     */
    private $question;

    /**
     * @var string
     */
    private $answer;

    /**
     * @return string
     */
    public function getId()
    {
        return self::STEP_ID;
    }

    /**
     * Load the steps data from the session storage.
     *
     * @return $this
     */
    public function load()
    {
        $this->sessionService->checkQuestionsAvailable();
        $values = $this->sessionService->load(self::STEP_ID);
        $this->readFromArray($values);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCleanFilterWhiteList()
    {
        return [];
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public function readFromArray(array $values)
    {
        if (is_array($values) && count($values)) {
            $this->setQuestion($values[SecurityQuestionFirstInputFilter::FIELD_QUESTION]);
            $this->setAnswer($values[SecurityQuestionFirstInputFilter::FIELD_ANSWER]);
        }
    }

    /**
     * Export the step values as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            SecurityQuestionFirstInputFilter::FIELD_QUESTION => $this->getQuestion(),
            SecurityQuestionFirstInputFilter::FIELD_ANSWER   => $this->getAnswer(),
        ];
    }

    public function toViewArray()
    {
        $result = parent::toViewArray();
        $result['questions'] = $this->sessionService->load(self::QUESTIONS_GROUP);

        return $result;
    }
    /**
     * The route for this step.
     *
     * @return mixed
     */
    public function route()
    {
        return 'account-register/security-question-one';
    }

    /**
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param string $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @param string $answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }
}
