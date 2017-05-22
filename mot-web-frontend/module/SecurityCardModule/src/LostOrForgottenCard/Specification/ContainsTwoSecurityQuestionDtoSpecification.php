<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Specification;

use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\Specification\SpecificationInterface;

class ContainsTwoSecurityQuestionDtoSpecification implements SpecificationInterface
{
    /**
     * Verify if given candidate object fulfills the specification
     *
     * @param mixed $candidate
     * @return bool
     */
    public function isSatisfiedBy($candidate)
    {
        return
            $this->isNotEmptyArray($candidate) &&
            $this->containsExactlyTwoElements($candidate) &&
            $this->allElementsAreOfSpecificType($candidate);
    }

    /**
     * @param $candidate
     * @return bool
     */
    private function isNotEmptyArray($candidate)
    {
        return
            !empty($candidate) &&
            is_array($candidate);
    }

    /**
     * @param $candidate
     * @return bool
     */
    private function containsExactlyTwoElements($candidate)
    {
        if (2 !== count($candidate)) return false;

        list($questionOne, $questionTwo) = $candidate;

        return
            isset($questionOne) &&
            isset($questionTwo);
    }

    /**
     * @param $candidate
     * @return bool
     */
    private function allElementsAreOfSpecificType($candidate)
    {
        list($questionOne, $questionTwo) = $candidate;

        return
            $questionOne instanceof SecurityQuestionDto &&
            $questionTwo instanceof SecurityQuestionDto;
    }
}