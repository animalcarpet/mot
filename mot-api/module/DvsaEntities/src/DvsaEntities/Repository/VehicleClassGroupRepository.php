<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\VehicleClassGroup;
use Doctrine\ORM\EntityRepository;

class VehicleClassGroupRepository extends EntityRepository
{
    /**
     * @param string $code
     *
     * @return VehicleClassGroup
     *
     * @throws NotFoundException
     */
    public function getByCode($code)
    {
        $result = $this->findOneBy(['code' => $code]);

        if ($result === null) {
            throw new NotFoundException($this->getClassName(), $code);
        }

        return $result;
    }
}
