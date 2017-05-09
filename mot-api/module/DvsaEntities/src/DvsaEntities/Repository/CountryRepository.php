<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Country;

/**
 * Class CountryRepository.
 *
 * @codeCoverageIgnore
 */
class CountryRepository extends AbstractMutableRepository
{
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param $id
     *
     * @return Country
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $result = $this->find($id);
        if (empty($result)) {
            throw new NotFoundException('Country of registration', $id);
        }

        return $result;
    }

    /**
     * @param $code
     *
     * @return Country
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getByCode($code)
    {
        $result = $this->findOneBy(['code' => $code]);
        if (!$result) {
            throw new NotFoundException('Country of registration[by code]', $code);
        }

        return $result;
    }
}
