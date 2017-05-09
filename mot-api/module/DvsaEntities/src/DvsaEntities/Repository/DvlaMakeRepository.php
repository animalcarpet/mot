<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\DvlaMake;

/**
 * Class DvlaMakeRepository.
 *
 * @codeCoverageIgnore
 */
class DvlaMakeRepository extends AbstractMutableRepository
{
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param int $id
     *
     * @return DvlaMake
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $result = $this->find($id);
        if (empty($result)) {
            throw new NotFoundException('DvlaMake', $id);
        }

        return $result;
    }

    /**
     * @param string $code
     *
     * @return DvlaMake
     *
     * @throws NotFoundException
     */
    public function getByCode($code)
    {
        $result = $this->findOneBy(['code' => $code]);
        if (is_null($result)) {
            throw new NotFoundException('DvlaMake', $code);
        }

        return $result;
    }
}
