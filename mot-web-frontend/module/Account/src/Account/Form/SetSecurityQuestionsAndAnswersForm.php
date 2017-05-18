<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace Account\Form;

use DvsaClient\Entity\SecurityQuestionSet;
use DvsaCommon\InputFilter\Account\SecurityQuestionAnswersInputFilter;
use DvsaCommon\InputFilter\Account\SetSecurityQuestionsAndAnswersInputFilter;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;
use Zend\Form\Fieldset;

class SetSecurityQuestionsAndAnswersForm extends AbstractSecurityAnswersForm
{
    const FIELD_NAME_STEP_NAME = 'submitted_step';

    /** Commonly used label for answer's text input */
    const LABEL_QUESTION = 'Choose a question';

    /** Commonly used attributes on answer fields */
    const ATTR_QUESTION_FIELD = ['class' => 'form-control-select form-control-1-2'];

    /** What to show as the first option in each drop-box */
    const OPT_EMPTY_OPTION = 'Please select';

    /**
     * SetSecurityQuestionsAndAnswersForm constructor.
     * @param SecurityQuestionSet $questions
     * @param string|null $stepName
     */
    public function __construct(SecurityQuestionSet $questions, $stepName = null)
    {
        parent::__construct('SecurityQuestionAnswersFrom');

        $this->add(
            $this->createQuestionsDropBox(
                $questions->getGroupOneQuestionList(),
                SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_FIRST_QUESTION
            )
        );

        $this->renameAbstractedElement(
            SecurityQuestionAnswersInputFilter::FIELD_NAME_FIRST_ANSWER,
            SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_FIRST_ANSWER
        );

        $this->add(
            $this->createQuestionsDropBox(
                $questions->getGroupTwoQuestionList(),
                SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_SECOND_QUESTION
            )
        );

        $this->renameAbstractedElement(
            SecurityQuestionAnswersInputFilter::FIELD_NAME_SECOND_ANSWER,
            SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_SECOND_ANSWER
        );

        if (!is_null($stepName)) {
            $this->add((new Hidden('submitted_step'))->setValue($stepName));
        }
    }

    /**
     * @param array $questions
     * @param string $name
     * @return Select
     */
    private function createQuestionsDropBox(array $questions, $name)
    {
        $questionElement = new Select();
        $questionElement->setName($name)
            ->setAttribute('id', $name)
            ->setLabel(self::LABEL_QUESTION)
            ->setAttributes(self::ATTR_QUESTION_FIELD)
            ->setLabelAttributes(parent::ATTR_LABEL)
            ->setEmptyOption(self::OPT_EMPTY_OPTION)
            ->setValueOptions($questions);

        return $questionElement;
    }
}
