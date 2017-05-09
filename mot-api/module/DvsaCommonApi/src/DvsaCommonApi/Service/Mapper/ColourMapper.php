<?php

namespace DvsaCommonApi\Service\Mapper;

use DvsaCommon\Dto\Common\ColourDto;
use DvsaEntities\Entity\Colour;

/**
 * Class map data of entity Colour to dto Colour.
 */
class ColourMapper extends AbstractApiMapper
{
    /**
     * @return ColourDto[]
     */
    public function manyToDto($colours)
    {
        return parent::manyToDto($colours);
    }

    /**
     * @param Colour $colour
     *
     * @return ColourDto
     */
    public function toDto($colour)
    {
        $dto = new ColourDto();

        if ($colour instanceof Colour) {
            $dto
                ->setCode($colour->getCode())
                ->setName($colour->getName());
        }

        return $dto;
    }
}
