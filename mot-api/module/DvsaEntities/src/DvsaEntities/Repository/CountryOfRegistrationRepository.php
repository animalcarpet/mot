<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\CountryOfRegistration;

/**
 * Class CountryOfRegistrationRepository.
 *
 * @method FuelType|null findAll()
 * @codeCoverageIgnore
 */
class CountryOfRegistrationRepository extends AbstractMutableRepository
{
    /**
     * @param $id
     *
     * @return CountryOfRegistration
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
     * @return CountryOfRegistration
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
