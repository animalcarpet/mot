<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Colour;

/**
 * Repository for {@link Colour}.
 *
 * @method Colour|null findOneByCode(string $code)
 * @codeCoverageIgnore
 */
class ColourRepository extends AbstractMutableRepository
{
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param int $id
     *
     * @return Colour
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $result = $this->find($id);
        if (empty($result)) {
            throw new NotFoundException('Colour', $id);
        }

        return $result;
    }

    /**
     * @param string $code
     *
     * @return Colour
     *
     * @throws NotFoundException
     */
    public function getByCode($code)
    {
        $result = $this->findOneByCode($code);
        if (is_null($result)) {
            throw new NotFoundException('Colour', $code);
        }

        return $result;
    }
}
