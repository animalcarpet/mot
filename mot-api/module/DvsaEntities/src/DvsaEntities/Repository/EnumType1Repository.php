<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;

class EnumType1Repository extends EntityRepository
{
    /**
     * Get enum entity with code.
     *
     * @param string $code from auto-generated enum found in \DvsaCommon\Enum\
     *
     * @return null|object
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
