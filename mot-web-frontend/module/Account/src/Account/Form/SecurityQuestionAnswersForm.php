<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Account\Form;

use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\InputFilter\Account\SecurityQuestionAnswersInputFilter;
use Zend\Form\Element;
use Zend\Form\Form;

class SecurityQuestionAnswersForm extends Form
{
    /**
     * SecurityQuestionAnswersForm constructor.
     * @param SecurityQuestionDto $firstQuestion
     * @param SecurityQuestionDto $secondQuestion
     */
    public function __construct(SecurityQuestionDto $firstQuestion, SecurityQuestionDto $secondQuestion)
    {
        parent::__construct('SecurityQuestionAnswersFrom');

        $this->initializeInputFields($firstQuestion, $secondQuestion);
    }

    public function setAction($url)
    {
        $this->setAttribute('action', $url);
    }

    /**
     * @return array Pair of question ids and their corresponding answers
     */
    public function getMappedQuestionsAndAnswers()
    {
        $data = $this->getData();
        return [
            $data[SecurityQuestionAnswersInputFilter::FIELD_NAME_FIRST_QUESTION_ID] =>
                $data[SecurityQuestionAnswersInputFilter::FIELD_NAME_FIRST_ANSWER],
            $data[SecurityQuestionAnswersInputFilter::FIELD_NAME_SECOND_QUESTION_ID] =>
                $data[SecurityQuestionAnswersInputFilter::FIELD_NAME_SECOND_ANSWER],
        ];
    }

    public function flagFailedAnswerVerifications($questionId)
    {
        $answerFieldName = $this->getAnswerFieldNameForQuestionId($questionId);

        $this->get($answerFieldName)
            ->setMessages([SecurityQuestionAnswersInputFilter::MSG_FAILED_VERIFICATION]);
    }

    private function getAnswerFieldNameForQuestionId($questionId)
    {
        /** @var Element $element */
        foreach ($this->getElements() as $element) {
            if ($element->getAttribute('type') === 'hidden' && $element->getValue() == $questionId) {
                $questionFieldName = $element->getName();

                return SecurityQuestionAnswersInputFilter::QUESTION_ANSWER_FIELD_PAIR_MAP[$questionFieldName];
            }
        }
    }

    private function createAnswerInputField($question, $name)
    {
        $answerElement = (new Element())
            ->setName($name)
            ->setAttribute('id', $name)
            ->setAttributes(['autocomplete' => 'off', 'class' => 'form-control'])
            ->setLabel($question)
            ->setLabelAttributes(['class' => 'form-label']);

        return $answerElement;
    }

    /**
     * @param SecurityQuestionDto $firstQuestion
     * @param SecurityQuestionDto $secondQuestion
     */
    protected function initializeInputFields(SecurityQuestionDto $firstQuestion, SecurityQuestionDto $secondQuestion)
    {
        $this->add(
            $this->createAnswerInputField(
                $firstQuestion->getText(),
                SecurityQuestionAnswersInputFilter::FIELD_NAME_FIRST_ANSWER
            )
        );

        $this->add(
            $this->createAnswerInputField(
                $secondQuestion->getText(),
                SecurityQuestionAnswersInputFilter::FIELD_NAME_SECOND_ANSWER
            )
        );

        $this->add(
            (new Element\Hidden(SecurityQuestionAnswersInputFilter::FIELD_NAME_FIRST_QUESTION_ID))
                ->setValue($firstQuestion->getId())
        );

        $this->add(
            (new Element\Hidden(SecurityQuestionAnswersInputFilter::FIELD_NAME_SECOND_QUESTION_ID))
                ->setValue($secondQuestion->getId())
        );

        $this->add(
            (new Element\Submit('submitSecurityAnswers'))->setAttribute('class', 'button')->setValue('Continue')
        );
    }
}
