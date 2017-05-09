<?php

namespace AccountApi\Mapper;

use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;
use DvsaEntities\Entity\SecurityQuestion;

/**
 * Class SecurityQuestionMapper.
 */
class SecurityQuestionMapper extends AbstractApiMapper
{
    /**
     * @param SecurityQuestion[] $questions
     *
     * @return SecurityQuestionDto[]
     */
    public function manyToDto($questions)
    {
        return parent::manyToDto($questions);
    }

    /**
     * @param SecurityQuestion $question
     *
     * @return SecurityQuestionDto
     */
    public function toDto($question)
    {
        $dto = new SecurityQuestionDto();

        $dto->setId($question->getId());
        $dto->setText($question->getText());
        $dto->setGroup($question->getGroup());
        $dto->setDisplayOrder($question->getDisplayOrder());

        return $dto;
    }
}
