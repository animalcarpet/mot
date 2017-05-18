<?php

namespace DvsaClientTest\Entity;

use DvsaClient\Entity\SecurityQuestionSet;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaEntities\Entity\SecurityQuestion;

class SecurityQuestionSetTest extends \PHPUnit_Framework_TestCase
{

    private static function generateQuestions($displayOrder, $group)
    {
        $res = [];
        while ($displayOrder--) {
            $res [] = (new SecurityQuestionDto)
                ->setId(sprintf('%d%d', $group, $displayOrder))
                ->setDisplayOrder($displayOrder)
                ->setGroup($group)
                ->setText('Question ' . $displayOrder);
        }

        return $res;
    }

    public static function buildRandomQuestionsCollection()
    {
        $q1 = self::generateQuestions(3, 1);
        $q2 = self::generateQuestions(3, 2);
        $merged = array_merge($q2, $q1);
        shuffle($merged);

        return $merged;
    }

    public function testExtractGroupOne()
    {
        $set = new SecurityQuestionSet(self::buildRandomQuestionsCollection());
        $groupOneQuestions = $set->getGroupOne();
        $this->assertSorted($groupOneQuestions);
        $this->assertAllOfGroup($groupOneQuestions, 1);
    }

    public function testExtractGroupTwo()
    {
        $set = new SecurityQuestionSet(self::buildRandomQuestionsCollection());
        $groupTwoQuestions = $set->getGroupTwo();
        $this->assertSorted($groupTwoQuestions);
        $this->assertAllOfGroup($groupTwoQuestions, 2);
    }

    public function testGetGroupOneQuestionList()
    {
        $securityQuestionSet = new SecurityQuestionSet(self::buildRandomQuestionsCollection());

        $groupOneQuestions = $securityQuestionSet->getGroupOne();
        $groupOneQuestionList = $securityQuestionSet->getGroupOneQuestionList();
        foreach ($groupOneQuestions as $questionDto) {
            $this->assertArrayHasKey($questionDto->getId(), $groupOneQuestionList);
            $this->assertEquals($questionDto->getText(), $groupOneQuestionList[$questionDto->getId()]);
        }

        $groupTwoQuestions = $securityQuestionSet->getGroupTwo();
        $groupTwoQuestionList = $securityQuestionSet->getGroupTwoQuestionList();
        foreach ($groupTwoQuestions as $questionDto) {
            $this->assertArrayHasKey($questionDto->getId(), $groupTwoQuestionList);
            $this->assertEquals($questionDto->getText(), $groupTwoQuestionList[$questionDto->getId()]);
        }
    }

    /**
     * @param SecurityQuestion[] $questions
     */
    private function assertSorted($questions)
    {
        $previous = 0;
        foreach ($questions as $question) {
            if ($question->getDisplayOrder() < $previous) {
                $this->fail('Questions are in wrong order');
            }
        }
    }

    /**
     * @param SecurityQuestion[] $questions
     * @param int $group
     */
    private function assertAllOfGroup($questions, $group)
    {
        foreach ($questions as $question) {
            if ($question->getGroup() !== $group) {
                $this->fail('Questions are not group: '.$group);
            }
        }
    }
}
