<?php

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Step;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\SecurityQuestionsStep;
use DvsaCommonTest\TestUtils\XMock;
use Zend\InputFilter\InputFilter;

class SecurityQuestionsStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor.
     *
     * @throws \Exception
     */
    public function testConstructor()
    {
        $step = new SecurityQuestionsStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertInstanceOf(SecurityQuestionsStep::class, $step);
    }

    /**
     * Placeholder test until validation stories are implemented.
     */
    public function testId()
    {
        $step = new SecurityQuestionsStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertEquals(SecurityQuestionsStep::STEP_ID, $step->getId());
    }

    /**
     * Test loading data returned from the session.
     *
     * @throws \Exception
     */
    public function testLoad()
    {
        $fixture = $this->getFixture();

        $session = XMock::of(RegistrationSessionService::class);
        $session->expects($this->once())
            ->method('load')
            ->with(SecurityQuestionsStep::STEP_ID)
            ->willReturn($fixture);

        $step = new SecurityQuestionsStep(
            $session,
            XMock::of(InputFilter::class)
        );

        $step->load();

        $this->assertEquals($step->getQuestion1(), $fixture['question1']);
        $this->assertEquals($step->getAnswer1(), $fixture['answer1']);
        $this->assertEquals($step->getQuestion2(), $fixture['question2']);
        $this->assertEquals($step->getAnswer2(), $fixture['answer2']);
    }

    /**
     * Test extracting values into an array.
     */
    public function testToArray()
    {
        $step = new SecurityQuestionsStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $step->setQuestion1('question1');
        $step->setAnswer1('answer1');
        $step->setQuestion2('question2');
        $step->setAnswer2('answer2');

        $values = $step->toArray();

        $this->assertEquals('question1', $values['question1']);
        $this->assertEquals('answer1', $values['answer1']);
        $this->assertEquals('question2', $values['question2']);
        $this->assertEquals('answer2', $values['answer2']);
    }

    /**
     * Test all the property getters and setters.
     */
    public function testGettersSetters()
    {
        $step = new SecurityQuestionsStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $step->setQuestion1('question1');
        $step->setAnswer1('answer1');
        $step->setQuestion2('question2');
        $step->setAnswer2('answer2');

        $this->assertEquals('question1', $step->getQuestion1());
        $this->assertEquals('answer1', $step->getAnswer1());
        $this->assertEquals('question2', $step->getQuestion2());
        $this->assertEquals('answer2', $step->getAnswer2());
    }

    /**
     * @return array
     */
    public function getFixture()
    {
        $fixture = [
            'question1' => __METHOD__.'_question1',
            'answer1' => __METHOD__.'_answer1',
            'question2' => __METHOD__.'_question2',
            'answer2' => __METHOD__.'_answer2',
        ];

        return $fixture;
    }
}
