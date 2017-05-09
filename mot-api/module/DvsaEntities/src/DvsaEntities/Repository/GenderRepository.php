<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\Gender;

/**
 * Class GenderRepository.
 *
 * @codeCoverageIgnore
 */
class GenderRepository extends AbstractMutableRepository
{
    /**
     * @param $name
     *
     * @return Gender
     */
    public function getByName($name)
    {
        return $this->findOneBy(['name' => $name]);
    }
}
