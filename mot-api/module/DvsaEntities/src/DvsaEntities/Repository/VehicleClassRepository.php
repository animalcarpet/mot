<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\VehicleClass;

/**
 * Class VehicleClassRepository.
 *
 * @codeCoverageIgnore
 */
class VehicleClassRepository extends AbstractMutableRepository
{
    use EnumType1RepositoryTrait;

    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param int $id
     *
     * @return VehicleClass
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $class = $this->find($id);
        if ($class === null) {
            throw new NotFoundException('VehicleClass', $id);
        }

        return $class;
    }
}
