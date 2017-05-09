<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\TransmissionType;

/**
 * Class TransmissionTypeRepository.
 *
 * @codeCoverageIgnore
 */
class TransmissionTypeRepository extends AbstractMutableRepository
{
    use EnumType1RepositoryTrait;

    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param $id
     *
     * @return TransmissionType
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $entity = $this->find($id);
        if (!$entity) {
            throw new NotFoundException($this->getEntityName(), $id);
        }

        return $entity;
    }
}
