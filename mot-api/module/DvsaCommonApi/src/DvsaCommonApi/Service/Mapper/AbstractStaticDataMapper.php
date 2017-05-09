<?php

namespace DvsaCommonApi\Service\Mapper;

use DvsaCommon\Dto\Common\AbstractStaticDataDto;
use DvsaEntities\Entity\Entity;

/**
 * Class AbstractStaticDataMapper.
 */
abstract class AbstractStaticDataMapper
{
    /**
     * @param Entity $entity
     * @param string $dtoClazz
     *
     * @return AbstractStaticDataDto
     */
    public function toDto($entity, $dtoClazz = null)
    {
        if (!($entity instanceof Entity)) {
            throw new \InvalidArgumentException('$entity argument must be instance of Entity');
        }

        if (!class_exists($dtoClazz)) {
            throw new \InvalidArgumentException('$dtoClazz argument must be correct class path');
        }

        /** @var AbstractStaticDataDto $dto */
        $dto = new $dtoClazz();

        $dto
            ->setId($entity->getId())
            ->setCode($entity->getCode())
            ->setName($entity->getName());

        return $dto;
    }
}
