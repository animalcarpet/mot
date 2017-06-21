<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Step;

use DvsaCommon\InputFilter\Registration\SecurityQuestionsInputFilter;

class SecurityQuestionsStep extends AbstractRegistrationStep
{
    /**
     * const used for the session key, available via getId().
     */
    const STEP_ID = 'SECURITY_QUESTIONS';

    const SECURITY_QUESTIONS_GROUP_A = 'securityQuestionsGroupA';
    const SECURITY_QUESTIONS_GROUP_B = 'securityQuestionsGroupB';

    /**
     * @var string
     */
    private $question1;

    /**
     * @var string
     */
    private $answer1;

    /**
     * @var string
     */
    private $question2;

    /**
     * @var string
     */
    private $answer2;

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
            $this->setQuestion1($values[SecurityQuestionsInputFilter::FIELD_QUESTION_1]);
            $this->setAnswer1($values[SecurityQuestionsInputFilter::FIELD_ANSWER_1]);
            $this->setQuestion2($values[SecurityQuestionsInputFilter::FIELD_QUESTION_2]);
            $this->setAnswer2($values[SecurityQuestionsInputFilter::FIELD_ANSWER_2]);

            $this->filter->setData($this->toArray());
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
            SecurityQuestionsInputFilter::FIELD_QUESTION_1 => $this->getQuestion1(),
            SecurityQuestionsInputFilter::FIELD_ANSWER_1 => $this->getAnswer1(),
            SecurityQuestionsInputFilter::FIELD_QUESTION_2 => $this->getQuestion2(),
            SecurityQuestionsInputFilter::FIELD_ANSWER_2 => $this->getAnswer2(),
        ];
    }

    public function toViewArray()
    {
        $result = parent::toViewArray();
        $result['questions1'] = $this->sessionService->load(self::SECURITY_QUESTIONS_GROUP_A);
        $result['questions2'] = $this->sessionService->load(self::SECURITY_QUESTIONS_GROUP_B);

        return $result;
    }
    /**
     * The route for this step.
     *
     * @return string
     */
    public function route()
    {
        return 'account-register/security-questions';
    }

    /**
     * @return string
     */
    public function getQuestion1()
    {
        return $this->question1;
    }

    /**
     * @param string $question1
     */
    public function setQuestion1($question1)
    {
        $this->question1 = $question1;
    }

    /**
     * @return string
     */
    public function getAnswer1()
    {
        return $this->answer1;
    }

    /**
     * @param string $answer1
     */
    public function setAnswer1($answer1)
    {
        $this->answer1 = $answer1;
    }

    /**
     * @return string
     */
    public function getQuestion2()
    {
        return $this->question2;
    }

    /**
     * @param string $question2
     */
    public function setQuestion2($question2)
    {
        $this->question2 = $question2;
    }

    /**
     * @return string
     */
    public function getAnswer2()
    {
        return $this->answer2;
    }

    /**
     * @param string $answer2
     */
    public function setAnswer2($answer2)
    {
        $this->answer2 = $answer2;
    }
}
