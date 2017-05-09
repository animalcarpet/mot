<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaCommon\Utility\ArrayUtils;

class NonWorkingDayRepository extends EntityRepository
{
    /**
     * @param string $countryCode
     * @param int    $year
     *
     * @return \DateTime[]
     */
    public function findDaysByCountryAndYear($countryCode, $year)
    {
        $results = $this
            ->createQueryBuilder('nwd')
            ->select('nwd.day')
            ->innerJoin('nwd.country', 'nwdc')
            ->innerJoin('nwdc.country', 'c')
            ->where('c.code = :countryCode')
            ->andWhere('YEAR(nwd.day) = :year')
            ->setParameter('countryCode', $countryCode)
            ->setParameter('year', $year)
            ->getQuery()
            ->getArrayResult();

        return ArrayUtils::map($results, function ($row) {
            return $row['day'];
        });
    }
}
