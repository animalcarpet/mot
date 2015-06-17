<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\Title;

/**
 * Class TitleRepository
 * @package DvsaEntities\Repository
 * @codeCoverageIgnore
 */
class TitleRepository extends AbstractMutableRepository
{

    /**
     * @param $name
     *
     * @return Title
     */
    public function getByName($name)
    {
        return $this->findOneBy(['name' => $name]);
    }
}
