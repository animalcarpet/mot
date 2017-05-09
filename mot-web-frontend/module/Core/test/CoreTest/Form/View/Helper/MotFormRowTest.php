<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace CoreTest\Form\View\Helper;

use Core\Form\View\Helper\MotFormConstant;
use Core\Form\View\Helper\MotFormRow;
use Zend\Form\Element;
use Zend\Form\View\HelperConfig;
use Zend\View\Renderer\PhpRenderer;

class MotFormRowTest extends \PHPUnit_Framework_TestCase
{
    public function testCanGenerateLabel()
    {
        $inputName = 'Input-field-identifier';
        $labelContent = sprintf('Labels content for "%s" input:', $inputName);
        $expectedMarkup = sprintf(
            '<div class="%s"><label for="%s">%s</label></div>',
            MotFormConstant::DEFAULT_WRAPPER_CLASS,
            $inputName,
            htmlspecialchars($labelContent)
        );

        $helper = new MotFormRow();

        $element = new Element('anything');
        $element->setAttribute('id', $inputName);
        $element->setLabel($labelContent);

        $this->assertEquals($expectedMarkup, $helper->render($element));
    }

    public function testCanGenerateLabelWithValidationMessage()
    {
        $inputName = 'Input-field-identifier';
        $labelContent = sprintf('Labels content for "%s" input:', $inputName);
        $validationMessage = 'must be less than 70 characters long';
        $expectedMarkup = sprintf(
            '<div class="%s %s"><label for="%s">%s<span class="%s">%s</span></label></div>',
            MotFormConstant::DEFAULT_WRAPPER_CLASS,
            MotFormConstant::DEFAULT_HAS_ERROR_CLASS,
            $inputName,
            htmlspecialchars($labelContent),
            MotFormConstant::DEFAULT_VALIDATION_MESSAGE_CLASS,
            ucfirst($validationMessage)
        );

        $helper = new MotFormRow();

        $element = new Element('anything');
        $element->setAttribute('id', $inputName);
        $element->setLabel($labelContent);
        $element->setMessages([$validationMessage]);

        $this->assertEquals($expectedMarkup, $helper->render($element));
    }

    /**
     * @param Element $element
     * @param string  $expectedMarkup
     * @dataProvider dataProvider
     */
    public function testWrapperAndLabelGeneratedAsExpectedForDifferentTypes(Element $element, $expectedMarkup)
    {
        $helper = new MotFormRow();
        $renderer = new PhpRenderer();
        $helpers = $renderer->getHelperPluginManager();
        $config = new HelperConfig();
        $config->configureServiceManager($helpers);
        $helper->setView($renderer);

        $this->assertEquals($expectedMarkup, $helper->render($element));
    }

    public function dataProvider()
    {
        return [
            [
                'element' => (new Element\Submit('foo'))
                    ->setLabel('bar')
                    ->setOption(MotFormConstant::KEY_DISABLE_WRAPPER, true),
                'expectedMarkup' => '<label>bar<input type="submit" name="foo" value=""></label>',
            ],
            [
                'element' => (new Element\Checkbox('foo'))
                    ->setLabel('bar')
                    ->setOption(MotFormConstant::KEY_DISABLE_WRAPPER, true),
                'expectedMarkup' => '<label>bar<input type="hidden" name="foo" value="0"><input type="checkbox" name="foo" value="1"></label>',
            ],
            [
                'element' => (new Element\Hidden('foo'))->setLabel('bar'),
                'expectedMarkup' => '<input type="hidden" name="foo" value="">',
            ],
            [
                'element' => (new Element\Button('foo'))->setLabel('bar')
                    ->setOption(MotFormConstant::KEY_DISABLE_WRAPPER, true),
                'expectedMarkup' => '<button type="button" name="foo" value="">bar</button>',
            ],
            [
                'element' => (new Element\Select('foo'))->setOption(MotFormConstant::KEY_DISABLE_WRAPPER, true),
                'expectedMarkup' => '<select name="foo"></select>',
            ],
            [
                'element' => (new Element\Submit('foo'))->setLabel('bar'),
                'expectedMarkup' => '<div class="form-group"><label>bar<input type="submit" name="foo" value=""></label></div>',
            ],
            [
                'element' => (new Element\Checkbox('foo'))->setLabel('bar'),
                'expectedMarkup' => '<div class="form-group"><label>bar<input type="hidden" name="foo" value="0"><input type="checkbox" name="foo" value="1"></label></div>',
            ],
            [
                'element' => (new Element\Button('foo'))->setLabel('bar'),
                'expectedMarkup' => '<button type="button" name="foo" value="">bar</button>',
            ],
            [
                'element' => (new Element\Select('foo'))->setLabel('bar'),
                'expectedMarkup' => '<div class="form-group"><label>bar<select name="foo"></select></label></div>',
            ],
        ];
    }
}
