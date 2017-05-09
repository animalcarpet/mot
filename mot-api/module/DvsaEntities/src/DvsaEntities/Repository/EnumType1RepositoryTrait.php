<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\Mapping\Entity;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * EnumType1 support.
 */
trait EnumType1RepositoryTrait
{
    /**
     * Get enum entity with code.
     *
     * @param string $code from auto-generated enum found in \DvsaCommon\Enum\
     *
     * @return EnumType1EntityTrait
     *
     * @throws NotFoundException
     */
    public function getByCode($code)
    {
        $result = $this->findOneBy(['code' => $code]);
        if ($result === null) {
            throw new NotFoundException($this->getEntityName(), $code);
        }

        return $result;
    }
}
