<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTestType;

/**
 * Retrieves MotTestTypes.
 */
class MotTestTypeRepository extends EntityRepository
{
    /**
     * Finds MotTestType by code. Throws NotFoundException if not found.
     *
     * @param $code
     *
     * @return MotTestType
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getByCode($code)
    {
        $result = $this->findOneBy(['code' => $code]);

        if (is_null($result)) {
            throw new NotFoundException($this->getEntityName(), $code);
        }

        return $result;
    }
}
