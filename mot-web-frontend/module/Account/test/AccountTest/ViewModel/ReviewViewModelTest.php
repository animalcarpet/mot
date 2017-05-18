<?php

namespace AccountTest\ViewModel;

use Account\ViewModel\ReviewViewModel;
use DvsaCommon\InputFilter\Account\SetSecurityQuestionsAndAnswersInputFilter;

class ReviewViewModelTest extends \PHPUnit_Framework_TestCase
{
    /** @var ReviewViewModel */
    private $model;

    public function setUp()
    {
        $this->model = new ReviewViewModel();
    }

    public function testSetDataWillReturnDtoWithGettersMatchingArray()
    {
        $testData = $this->getTestData();

        $this->model->setData($testData);

        $this->assertEquals($this->model->getPassword(), $testData['confirmPassword']['password']);
        $this->assertEquals($this->model->getSecurityQuestions(), $testData['securityQuestions']);
        $this->assertEquals(
            $this->model->getAnswerA(),
            $testData['setSecurityQuestion'][SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_FIRST_ANSWER]
        );
        $this->assertEquals(
            $this->model->getAnswerB(),
            $testData['setSecurityQuestion'][SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_SECOND_ANSWER]
        );
        $this->assertEquals(
            $this->model->getSecurityQuestionA(),
            $testData['setSecurityQuestion'][SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_FIRST_QUESTION]
        );
        $this->assertEquals(
            $this->model->getSecurityQuestionB(),
            $testData['setSecurityQuestion'][SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_SECOND_QUESTION]
        );
    }

    public function testSetSecurityQuestionDataWillReturnTheQuestionRelevantToTheUsersSelection()
    {
        $testData = $this->getTestData();

        $this->model->setData($testData);

        $securityQuestions = $testData['securityQuestions'];
        $groupAQuestions = $securityQuestions['groupA'];
        $groupBQuestions = $securityQuestions['groupB'];

        $this->assertEquals($this->model->getSecurityQuestionGroupAText(), $groupAQuestions[$this->model->getSecurityQuestionA()]);
        $this->assertEquals($this->model->getSecurityQuestionGroupBText(), $groupBQuestions[$this->model->getSecurityQuestionB()]);
    }

    public function testSetDataWithPasswordOnReturnDisplayPasswordWillReturnHiddenCharacterString()
    {
        $testData = $this->getTestData();

        $this->model->setData($testData);

        $passwordChar = str_repeat('•', strlen($testData['confirmPassword']['password']));
        $encryptedPasswordChar = $this->model->getHiddenPassword();

        $this->assertEquals($passwordChar, $encryptedPasswordChar);
    }

    public function testSetDataWithAnswersOnReturnDisplayAnswersWillReturnHiddenCharacterString()
    {
        $testData = $this->getTestData();

        $this->model->setData($testData);

        $answerA = str_repeat(
            '•',
            strlen($testData['setSecurityQuestion'][SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_FIRST_ANSWER])
        );
        $hiddenAnswerA = $this->model->getHiddenAnswerA();

        $this->assertEquals($answerA, $hiddenAnswerA);

        $answerB = str_repeat(
            '•',
            strlen($testData['setSecurityQuestion'][SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_SECOND_ANSWER])
        );
        $hiddenAnswerB = $this->model->getHiddenAnswerB();

        $this->assertEquals($answerB, $hiddenAnswerB);
    }

    private function getTestData()
    {
        return [
            'confirmPassword' => [
                'password' => 'Password1',
            ],
            'setSecurityQuestion' => [
                SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_FIRST_QUESTION => '1',
                SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_SECOND_QUESTION => '2',
                SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_FIRST_ANSWER => 'test',
                SetSecurityQuestionsAndAnswersInputFilter::FIELD_NAME_SECOND_ANSWER => 'test2',
            ],
            'securityQuestions' => [
                'groupA' => [
                        1 => 'What is your Fav Car',
                    ],
                'groupB' => [
                        2 => 'What is your Fav Colour',
                    ],
            ],
        ];
    }
}
