<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\EmptyVrmReason;

/**
 * @method EmptyVrmReason getByCode(string $code)
 */
class EmptyVrmReasonRepository extends AbstractMutableRepository
{
    use EnumType1RepositoryTrait;

    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param $id
     *
     * @return EmptyVrmReason
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
