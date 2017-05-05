<?php

namespace CoreTest\View\Partial\Forms;

use Zend\View\View;
use Zend\View\ViewEvent;

class ValidationSummaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Target Partial Path
     */
    const TPP = '/forms/validationSummary.twig';

    const TITLE = 'There was a problem with the information';

    const MSG_FORM_VALIDATION = [
        'second_question' => [
            'isEmpty' => 'enter your memorable answer'
        ]
    ];

    const MSG_FORM_VERIFICATION = [
        'first_question' => [
            'your answer wasn’t right'
        ]
    ];

    const MSG_ORPHAN = [
        [
            'you have 7 more tries'
        ]
    ];

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \DOMDocument
     */
    private $domdoc;

    /**
     * This setup can be abstracted to facilitate testing more of our partials
     */
    public function setUp()
    {
        $partialsBaseFolder = __DIR__ . str_repeat('/..', 5) . '/view/partial/';
        $loader = new \Twig_Loader_Filesystem($partialsBaseFolder);
        $this->twig = new \Twig_Environment($loader);
        $this->domdoc = new \DOMDocument();
    }

    public function testItWontRenderAnythingIfThereIsNoMessage()
    {
        $this->assertEmpty($this->renderPartial(self::TPP));
    }

    public function testItWontIncludeHeadingIfThereIsNoTitle()
    {
        $this->assertEqualXMLStructure(
            $this->getExpectedValidationSummaryPodWithoutTitle(),
            $this->convertToDomElement(
                $this->renderPartial(self::TPP,
                    [
                        'validationMessages' => $this->getMessagesZendFormCompatible(),
                    ]
                )
            )
        );
    }

    public function testItWillIncludeHeading()
    {
        $this->assertEqualXMLStructure(
            $this->getExpectedValidationSummaryPod(),
            $this->convertToDomElement(
                $this->renderPartial(self::TPP,
                    [
                        'title' => self::TITLE,
                        'validationMessages' => $this->getMessagesZendFormCompatible(),
                    ]
                )
            )
        );
    }

    public function testNamedMessagesArePrintingCorrectly()
    {
        $identifier = 'some_DOM_id';
        $message = 'To make sure it will change from camel case to a printable, first letter capital!';

        $liValue = trim(
            $this->convertToDomElement(
                $this->renderPartial(self::TPP,
                    [
                        'validationMessages' => [$identifier => [$message]],
                    ]
                )
            )->getElementsByTagName('li')
                ->item(0)
                ->nodeValue
        );

        $renderedNameSegment = substr(
            $liValue,
            0,
            strpos($liValue, '-') + 1
        );

        $expectedNameSegment = ucfirst(strtolower(
            str_replace('_', ' ', $identifier)
        )) . ' -';

        $this->assertEquals($expectedNameSegment, $renderedNameSegment);
    }

    public function testUnnamedMessagesArePrintingCorrectly()
    {
        $message = 'To make sure it will change from camel case to a printable, first letter capital!';

        $liValue = trim(
            $this->convertToDomElement(
                $this->renderPartial(self::TPP,
                    [
                        'validationMessages' => [0 => [$message]],
                    ]
                )
            )->getElementsByTagName('li')
                ->item(0)
                ->nodeValue
        );

        $this->assertEquals($message, $liValue);
    }

    /**
     * @return array
     */
    private function getMessagesZendFormCompatible()
    {

        $validationMessages =
            self::MSG_FORM_VALIDATION +
            self::MSG_FORM_VERIFICATION +
            self::MSG_ORPHAN;

        return $validationMessages;
    }

    /**
     * @param bool $asDomElement
     * @return \DOMElement|string
     */
    private function getExpectedValidationSummaryPodWithoutTitle($asDomElement = true)
    {
        $markUp = str_replace(
            '<h2 class="heading-medium">There was a problem with the information</h2>',
            '',
            $this->getExpectedValidationSummaryPod(false)
        );

        if ($asDomElement) {
            return $this->convertToDomElement($markUp);
        } else {
            return $markUp;
        }
    }

    private function getExpectedValidationSummaryPod($asDomElement = true)
    {
        $markUp = <<<EOT
<div class="validation-summary">
    <h2 class="heading-medium">There was a problem with the information</h2>
    <ol>
        <li>Second question - enter your memorable answer</li>
        <li>First question - your answer wasn’t right</li>
        <li>you have 7 more tries</li>
    </ol>
</div>
EOT;

        if ($asDomElement) {
            return $this->convertToDomElement($markUp);
        } else {
            return $markUp;
        }
    }

    /**
     * @param string $path partial relative path to mot-web-frontend/module/Core/view/partial
     * @param array $params
     * @return string
     */
    private function renderPartial($path, array $params = [])
    {
        return $this->twig->render($path, $params);
    }

    /**
     * @param string $markUp
     * @return \DOMElement
     */
    private function convertToDomElement($markUp)
    {
        $this->domdoc->loadHTML($markUp);
        return $this->domdoc->getElementsByTagName('div')->item(0);
    }
}
