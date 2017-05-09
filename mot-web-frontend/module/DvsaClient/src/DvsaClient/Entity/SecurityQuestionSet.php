<?php

namespace DvsaClient\Entity;

use DvsaCommon\Domain\SecurityQuestionGroup;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\Utility\ArrayUtils;

class SecurityQuestionSet
{
    private static $ORDER_FUNCTION_NAME = 'getDisplayOrder';

    private $groupOneQuestions;
    private $groupTwoQuestions;

    /**
     * @param $securityQuestionDtos SecurityQuestionDto[]
     */
    public function __construct($securityQuestionDtos)
    {
        $this->groupOneQuestions = $this->extractGroupOne($securityQuestionDtos);
        $this->groupTwoQuestions = $this->extractGroupTwo($securityQuestionDtos);
    }

    /**
     * @return \DvsaCommon\Dto\Security\SecurityQuestionDto[]
     */
    public function getGroupOne()
    {
        return $this->groupOneQuestions;
    }

    /**
     * @return \DvsaCommon\Dto\Security\SecurityQuestionDto[]
     */
    public function getGroupTwo()
    {
        return $this->groupTwoQuestions;
    }

    /**
     * @param SecurityQuestionDto[] $securityQuestionDtos
     *
     * @return SecurityQuestionDto[]
     */
    private function extractGroupOne($securityQuestionDtos)
    {
        return $this->extractGroup($securityQuestionDtos, SecurityQuestionGroup::GROUP_ONE);
    }

    /**
     * @param SecurityQuestionDto[] $securityQuestionDtos
     *
     * @return SecurityQuestionDto[]
     */
    private function extractGroupTwo($securityQuestionDtos)
    {
        return $this->extractGroup($securityQuestionDtos, SecurityQuestionGroup::GROUP_TWO);
    }

    /**
     * @param SecurityQuestionDto[] $securityQuestionDtos
     * @param $group
     *
     * @return SecurityQuestionDto[]
     */
    private function extractGroup($securityQuestionDtos, $group)
    {
        $questions = ArrayUtils::filter(
            $securityQuestionDtos, function (SecurityQuestionDto $question) use ($group) {
                return $question->getGroup() == $group;
            }
        );

        $questions = ArrayUtils::sortBy($questions, self::$ORDER_FUNCTION_NAME);

        return $questions;
    }
}
