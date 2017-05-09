<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\SiteStatus;

/**
 * Repository for {@link SiteStatus}.
 *
 * @method SiteStatus|null findOneByCode(string $code)
 * @codeCoverageIgnore
 */
class SiteStatusRepository extends AbstractMutableRepository
{
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param int $id
     *
     * @return SiteStatus
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $result = $this->find($id);
        if (empty($result)) {
            throw new NotFoundException('SiteStatus', $id);
        }

        return $result;
    }

    /**
     * @param string $code
     *
     * @return SiteStatus
     *
     * @throws NotFoundException
     */
    public function getByCode($code)
    {
        $result = $this->findOneByCode($code);
        if (is_null($result)) {
            throw new NotFoundException('SiteStatus', $code);
        }

        return $result;
    }
}
