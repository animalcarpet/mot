<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Core\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\View\Helper\FormLabel;

class MotFormLabel extends FormLabel
{
    /**
     * Generate a form label, optionally with content
     *
     * Always generates a "for" statement, as we cannot assume the form input
     * will be provided in the $labelContent.
     *
     * @param  ElementInterface $element
     * @param  bool $renderErrors
     * @param  null|string $labelContent
     * @param  string $position
     * @return string|FormLabel
     */
    public function __invoke(ElementInterface $element = null, $renderErrors = null, $labelContent = null, $position = null)
    {
        if (!$element) {
            return $this;
        }

        $openTag = $this->openTag($element);
        $label = '';
        if ($labelContent === null || $position !== null) {
            $label = $element->getLabel();
            if (empty($label)) {
                throw new Exception\DomainException(
                    sprintf(
                        '%s expects either label content as the second argument, ' .
                        'or that the element provided has a label attribute; neither found',
                        __METHOD__
                    )
                );
            }

            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate($label, $this->getTranslatorTextDomain());
            }

            if (!$element instanceof LabelAwareInterface || !$element->getLabelOption('disable_html_escape')) {
                $escapeHtmlHelper = $this->getEscapeHtmlHelper();
                $label = $escapeHtmlHelper($label);
            }

            if ($renderErrors) {

                $messages = $element->getMessages();

                if (!empty($messages)) {

                    $message = array_shift($messages);

                    if (!is_null($translator = $this->getTranslator())) {
                        $message = $translator->translate($message, $this->getTranslatorTextDomain());
                    }

                    $validationMessageClassKey = MotFormConstant::KEY_VALIDATION_MESSAGE_CLASS;
                    $validationMessageClass = is_null($element->getLabelOption($validationMessageClassKey)) ?
                        MotFormConstant::DEFAULT_VALIDATION_MESSAGE_CLASS :
                        $element->getLabelOption($validationMessageClassKey);

                    $label .= sprintf('<span class="%s">%s</span>', $validationMessageClass, ucfirst($message));
                }
            }
        }

        if ($label && $labelContent) {
            switch ($position) {
                case self::APPEND:
                    $labelContent .= $label;
                    break;
                case self::PREPEND:
                default:
                    $labelContent = $label . $labelContent;
                    break;
            }
        }

        if ($label && null === $labelContent) {
            $labelContent = $label;
        }

        return $openTag . $labelContent . $this->closeTag();
    }
}
