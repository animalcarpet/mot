<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaEntities\Entity\NonWorkingDayCountry;
use DvsaCommonApi\Service\Exception\NotFoundException;

/**
 * Repository for {@link NonWorkingDayLookup}.
 *
 * @codeCoverageIgnore
 */
class NonWorkingDayCountryRepository extends EntityRepository
{
    /**
     * @param string $code
     *
     * @return NonWorkingDayCountry
     *
     * @throws NotFoundException
     */
    public function getOneByCode($code)
    {
        $nonWorkingDayCountry = $this->createQueryBuilder('nwdc')
            ->innerJoin('nwdc.country', 'c')
            ->where('c.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();

        if (is_null($nonWorkingDayCountry)) {
            throw new NotFoundException($this->getEntityName(), $code);
        }

        return $nonWorkingDayCountry;
    }
}
