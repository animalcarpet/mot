<?php

namespace DvsaCommonApi\Service\Mapper;

use DvsaCommon\Dto\Common\ReasonForCancelDto;
use DvsaEntities\Entity\MotTestReasonForCancel;

/**
 * Class map data of entity ReasonForCancel to dto ReasonForCancel.
 */
class ReasonForCancelMapper extends AbstractApiMapper
{
    /**
     * @return ReasonForCancelDto[]
     */
    public function manyToDto($colours)
    {
        return parent::manyToDto($colours);
    }

    /**
     * @param MotTestReasonForCancel $rfc
     *
     * @return ReasonForCancelDto
     */
    public function toDto($rfc)
    {
        $dto = new ReasonForCancelDto();

        if ($rfc instanceof MotTestReasonForCancel) {
            $dto
                ->setId($rfc->getId())
                ->setReason($rfc->getReason())
                ->setReasonCy($rfc->getReasonCy())
                ->setAbandoned($rfc->getAbandoned())
                ->setIsDisplayable($rfc->isDisplayable());
        }

        return $dto;
    }
}
