<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\OdometerReading;

/**
 * Repository handling operations on OdometerReading class
 */
class OdometerReadingRepository extends AbstractMutableRepository
{

    /**
     * Finds an odometer reading for a given MOT test
     *
     * @param $motTestNumber
     *
     * @return null|OdometerReading
     */
    public function findReadingForTest($motTestNumber)
    {
        $query = "SELECT o FROM " . OdometerReading::class . " o" .
            " JOIN " . MotTest::class . " mt " .
            " WHERE mt.odometerReading = o
                AND mt.number = :motTestNumber";

        $resultList = $this->getEntityManager()->createQuery($query)
            ->setParameter("motTestNumber", $motTestNumber)
            ->getResult();

        return $resultList ? $resultList[0] : null;
    }

    /**
     * Saves odometer reading into database taking care of having only one instance per MOT test
     *
     * @param OdometerReading $reading
     */
    public function persist($reading)
    {
        $this->getEntityManager()->persist($reading);
    }

    /**
     * Searches for a previous test odometer reading looking from a given mot test's perspective.
     * The test must be a normal test either PASSED or FAILED.
     *
     * @param $motTestNumber
     *
     * @return OdometerReading
     */
    public function findPreviousReading($motTestNumber)
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(OdometerReading::class, 'o');

        $query = $this->getEntityManager()->createNativeQuery(
            "
            SELECT
              o.id, o.value, o.unit, o.result_type
            FROM
              odometer_reading o
          WHERE o.id = (
            SELECT
              mt1.odometer_reading_id
            FROM
              mot_test mt1
            JOIN mot_test_type tt1 ON (mt1.mot_test_type_id = tt1.id)
            JOIN mot_test mt2
            WHERE
              mt1.vehicle_id = mt2.vehicle_id
              AND mt2.number = :motTestNumber
              AND mt1.number <> mt2.number
              AND (tt1.code = :testTypeNormal
                OR tt1.code = :testTypeRetest)
              AND mt1.started_date < mt2.started_date
            ORDER BY
              mt1.issued_date DESC LIMIT 1
          )
          ",
            $rsm
        )
            ->setParameter("motTestNumber", $motTestNumber)
            ->setParameter("status", MotTestStatusName::PASSED)
            ->setParameter("testTypeNormal", MotTestTypeCode::NORMAL_TEST)
            ->setParameter("testTypeRetest", MotTestTypeCode::RE_TEST);

        $resultArray = $query->getResult();

        return empty($resultArray) ? null : $resultArray[0];
    }
}
