<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Core\Form\View\Helper;

use Zend\Form\Element\Button;
use Zend\Form\Element\Captcha;
use Zend\Form\Element\MonthSelect;
use Zend\Form\ElementInterface;
use Zend\Form\LabelAwareInterface;
use Zend\Form\View\Helper\FormRow;

class MotFormRow extends FormRow
{
    public function __construct()
    {
        $this->labelHelper = new MotFormLabel();
    }

    public function render(ElementInterface $element, $labelPosition = null)
    {
        $escapeHtmlHelper = $this->getEscapeHtmlHelper();
        $labelHelper = $this->getLabelHelper();
        $elementHelper = $this->getElementHelper();

        $label = $element->getLabel();
        $inputErrorClass = $this->getInputErrorClass();

        if (is_null($labelPosition)) {
            $labelPosition = $this->labelPosition;
        }

        if (isset($label) && '' !== $label) {
            // Translate the label
            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate($label, $this->getTranslatorTextDomain());
            }
        }

        // Does this element have errors ?
        if (count($element->getMessages()) > 0 && !empty($inputErrorClass)) {
            $classAttributes = ($element->hasAttribute('class') ? $element->getAttribute('class').' ' : '');
            $classAttributes = $classAttributes.$inputErrorClass;

            $element->setAttribute('class', $classAttributes);
        }

        if ($this->partial) {
            $vars = array(
                'element' => $element,
                'label' => $label,
                'labelAttributes' => $this->labelAttributes,
                'labelPosition' => $labelPosition,
                'renderErrors' => $this->renderErrors,
            );

            return $this->view->render($this->partial, $vars);
        }

        $elementString = $elementHelper->render($element);

        // hidden elements do not need a <label> -https://github.com/zendframework/zf2/issues/5607
        $type = $element->getAttribute('type');
        if (isset($label) && '' !== $label && $type !== 'hidden') {
            $labelAttributes = array();

            if ($element instanceof LabelAwareInterface) {
                $labelAttributes = $element->getLabelAttributes();
            }

            if (!$element instanceof LabelAwareInterface || !$element->getLabelOption('disable_html_escape')) {
                $label = $escapeHtmlHelper($label);
            }

            if (empty($labelAttributes)) {
                $labelAttributes = $this->labelAttributes;
            }

            // Multicheckbox elements have to be handled differently as the HTML standard does not allow nested
            // labels. The semantic way is to group them inside a fieldset
            if ($type === 'multi_checkbox'
                || $type === 'radio'
                || $element instanceof MonthSelect
                || $element instanceof Captcha
            ) {
                $markup = sprintf(
                    '<fieldset><legend>%s</legend>%s</fieldset>',
                    $label,
                    $elementString
                );
            } else {
                // Ensure element and label will be separated if element has an `id`-attribute.
                // If element has label option `always_wrap` it will be nested in any case.
                if ($element->hasAttribute('id')
                    && ($element instanceof LabelAwareInterface && !$element->getLabelOption('always_wrap'))
                ) {
                    $labelOpen = '';
                    $labelClose = '';
                    $label = $labelHelper($element, $this->renderErrors);
                } else {
                    $labelOpen = $labelHelper->openTag($labelAttributes);
                    $labelClose = $labelHelper->closeTag();
                }

                // Button element is a special case, because label is always rendered inside it
                if ($element instanceof Button) {
                    $labelOpen = $labelClose = $label = '';
                }

                if ($element instanceof LabelAwareInterface && $element->getLabelOption('label_position')) {
                    $labelPosition = $element->getLabelOption('label_position');
                }

                switch ($labelPosition) {
                    case self::LABEL_PREPEND:
                        $markup = $labelOpen.$label.$elementString.$labelClose;
                        break;
                    case self::LABEL_APPEND:
                    default:
                        $markup = $labelOpen.$elementString.$label.$labelClose;
                        break;
                }
            }
        } else {
            $markup = $elementString;
        }

        if ($this->renderErrors) {
        }

        $disableWrapper = $element->getOption(MotFormConstant::KEY_DISABLE_WRAPPER);

        $disableWrapper = empty($disableWrapper) ? MotFormConstant::DEFAULT_DISABLE_WRAPPER : $disableWrapper;

        if (!$disableWrapper && isset($label) && '' !== $label && $type !== 'hidden') {
            $wrapperClasses = [];

            $optionWrapperClass = $element->getOption(MotFormConstant::KEY_WRAPPER_CLASS);

            $wrapperDefaultClass = empty($optionWrapperClass) ?
                MotFormConstant::DEFAULT_WRAPPER_CLASS :
                $optionWrapperClass;

            $wrapperClasses[] = $wrapperDefaultClass;

            $errorMessages = $element->getMessages();
            if ($this->renderErrors && !empty($errorMessages)) {
                $optionHasErrorClass = $element->getOption(MotFormConstant::KEY_HAS_ERROR_CLASS);

                $hasErrorClass = empty($optionHasErrorClass) ?
                    MotFormConstant::DEFAULT_HAS_ERROR_CLASS :
                    $optionHasErrorClass;

                $wrapperClasses[] = $hasErrorClass;
            }

            $wrappedMarkup = sprintf(
                '<div class="%s">%s</div>',
                implode(' ', $wrapperClasses),
                $markup
            );

            return $wrappedMarkup;
        }

        return $markup;
    }
}
