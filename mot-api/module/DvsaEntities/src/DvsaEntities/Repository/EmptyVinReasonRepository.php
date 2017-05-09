<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\EmptyVinReason;

/**
 * @method EmptyVinReason getByCode(string $code)
 */
class EmptyVinReasonRepository extends AbstractMutableRepository
{
    use EnumType1RepositoryTrait;

    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param $id
     *
     * @return EmptyVinReason
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
