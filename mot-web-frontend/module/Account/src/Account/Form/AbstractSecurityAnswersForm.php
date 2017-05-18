<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace Account\Form;

use DvsaCommon\InputFilter\Account\SecurityQuestionAnswersInputFilter;
use Zend\Form\Element;
use Zend\Form\Form;

abstract class AbstractSecurityAnswersForm extends Form
{
    const FIELD_NAME_SUBMIT = 'btSubmitForm';

    /** Commonly used label for answer's text input */
    const LABEL_ANSWER = 'Memorable answer';

    /** Commonly used attributes on answer labels */
    const ATTR_LABEL = ['class' => 'form-label'];

    /** Commonly used attributes on answer fields */
    const ATTR_ANSWER_FIELD = ['autocomplete' => 'off', 'class' => 'form-control'];

    /**
     * SecurityAnswersForm constructor.
     * @param string|null $name Optional element name
     * @param array $options Optional
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);

        $this->add(
            $this->createAnswerInputField(
                self::LABEL_ANSWER,
                SecurityQuestionAnswersInputFilter::FIELD_NAME_FIRST_ANSWER
            )
        );

        $this->add(
            $this->createAnswerInputField(
                self::LABEL_ANSWER,
                SecurityQuestionAnswersInputFilter::FIELD_NAME_SECOND_ANSWER
            )
        );

        $this->add(
            (new Element\Submit(self::FIELD_NAME_SUBMIT))
                ->setAttribute('id', self::FIELD_NAME_SUBMIT)
                ->setAttribute('class', 'button')
                ->setValue('Continue')
        );
    }

    /**
     * @param string $url
     */
    public function setAction($url)
    {
        $this->setAttribute('action', $url);
    }

    /**
     * @param string $label
     * @param $name
     * @return Element|\Zend\Form\ElementInterface
     */
    protected function createAnswerInputField($label, $name)
    {
        $answerElement = (new Element\Text())
            ->setName($name)
            ->setLabel($label)
            ->setAttribute('id', $name)
            ->setAttributes(self::ATTR_ANSWER_FIELD)
            ->setLabelAttributes(self::ATTR_LABEL);

        return $answerElement;
    }
    
    /**
     * @param string $currentName
     * @param string $newName
     */
    protected function renameAbstractedElement($currentName, $newName)
    {
        $element = $this->get($currentName);
        $this->remove($currentName);
        $element->setName($newName)
            ->setAttribute('id', $newName);

        $this->add($element);
    }
}
