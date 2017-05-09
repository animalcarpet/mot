<?php

namespace DvsaCommonApi\Service\Mapper;

use DvsaCommon\Dto\Common\ReasonForRefusalDto;
use DvsaEntities\Entity\ReasonForRefusal;

/**
 * Class map data of entity ReasonForRefusal to dto ReasonForRefusal.
 */
class ReasonForRefusalMapper extends AbstractApiMapper
{
    /**
     * @return ReasonForRefusalDto[]
     */
    public function manyToDto($colours)
    {
        return parent::manyToDto($colours);
    }

    /**
     * @param ReasonForRefusal $rfr
     *
     * @return ReasonForRefusalDto
     */
    public function toDto($rfr)
    {
        $dto = new ReasonForRefusalDto();

        if ($rfr instanceof ReasonForRefusal) {
            $dto
                ->setId($rfr->getId())
                ->setReason($rfr->getReason())
                ->setReasonCy($rfr->getReasonCy());
        }

        return $dto;
    }
}
