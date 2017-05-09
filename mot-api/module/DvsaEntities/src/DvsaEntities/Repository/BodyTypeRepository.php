<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\BodyType;

/**
 * Class BodyTypeRepository.
 *
 * @method BodyType|null findOneByCode(string $code)
 * @codeCoverageIgnore
 */
class BodyTypeRepository extends AbstractMutableRepository
{
    use EnumType1RepositoryTrait;

    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param $id
     *
     * @return BodyType
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
