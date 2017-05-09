<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Person;

/**
 * Class UserRepository.
 *
 * @codeCoverageIgnore
 */
class UserRepository extends AbstractMutableRepository
{
    /**
     * @param $id
     *
     * @return Person
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getById($id)
    {
        $user = $this->find($id);
        if ($user === null) {
            throw new NotFoundException('User not found');
        }

        return $user;
    }
}
