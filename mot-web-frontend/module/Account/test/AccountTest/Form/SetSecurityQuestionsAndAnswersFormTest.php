<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace AccountTest\Form;

use Account\Form\SetSecurityQuestionsAndAnswersForm as TestSubject;
use DvsaClient\Entity\SecurityQuestionSet;
use DvsaClientTest\Entity\SecurityQuestionSetTest;
use DvsaCommon\InputFilter\Account\SetSecurityQuestionsAndAnswersInputFilter;
use Zend\Form\Element\Select;

class SetSecurityQuestionsAndAnswersFormTest extends \PHPUnit_Framework_TestCase
{
    public function testDropBoxesGeneratedCorrectly()
    {
        $securityQuestionSet = new SecurityQuestionSet(SecurityQuestionSetTest::buildRandomQuestionsCollection());

        $testSubjectForm = new TestSubject($securityQuestionSet);

        $elements = $testSubjectForm->getElements();

        $this->assertArrayHasKey(SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_FIRST_QUESTION, $elements);
        $this->assertArrayHasKey(SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_SECOND_QUESTION, $elements);

        $elementA = $testSubjectForm->get(SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_FIRST_QUESTION);
        $elementB = $testSubjectForm->get(SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_SECOND_QUESTION);

        $this->assertContainsOnlyInstancesOf(Select::class, [$elementA, $elementB]);

        $this->assertEquals(TestSubject::OPT_EMPTY_OPTION, $elementA->getEmptyOption());
        $this->assertEquals(TestSubject::OPT_EMPTY_OPTION, $elementB->getEmptyOption());

        $actualQuestionA = $elementA->getValueOptions();
        $expectedQuestionsA = $securityQuestionSet->getGroupOneQuestionList();
        $this->assertEquals($expectedQuestionsA, $actualQuestionA);
        for ($i = 1; $i < count($expectedQuestionsA); $i++) {
            $this->assertEquals(next($expectedQuestionsA), next($actualQuestionA));
        }

        $actualQuestionB = $elementB->getValueOptions();
        $expectedQuestionsB = $securityQuestionSet->getGroupTwoQuestionList();
        $this->assertEquals($expectedQuestionsB, $actualQuestionB);
        for ($i = 1; $i <= count($expectedQuestionsB); $i++) {
            $this->assertEquals(next($expectedQuestionsB), next($actualQuestionB));
        }
    }
}
