<?php

namespace IntegrationApi\OpenInterface\Repository;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\DvlaVehicle;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\DvlaMake;
use DvsaEntities\Entity\DvlaModel;

/**
 * Class OpenInterfaceMotTestRepository.
 */
class OpenInterfaceMotTestRepository
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns longest valid MOT Test being issued before or on provided day.
     *
     * @param string $vehicleId
     * @param string $before
     * @param string $status
     *
     * @return MotTest
     */
    public function findLatestMotTest($vehicleId, $before, $status)
    {
        $motTest = $this->entityManager
            ->getRepository(MotTest::class)
            ->getLatestMotTestByVehicleIdAndResult(
                $vehicleId,
                $status,
                $before
            );

        return $motTest;
    }

    /**
     * Returns longest valid MOT Test being issued before or on provided day.
     *
     * @param string $vrm
     * @param string $before
     * @param string $status
     * @param array  $excludeCodes
     *
     * @return MotTest
     */
    public function findLatestMotTestForVrm($vrm, $before, $status, $excludeCodes = [
        MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
        MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST,
    ])
    {
        return $this->entityManager
            ->getRepository(MotTest::class)
            ->findLatestMotTestByVrmAndResult(
                $vrm,
                $status,
                $before,
                $excludeCodes
            );
    }

    /**
     * @return DvlaVehicle
     */
    public function findVehicleByVrm($vrm)
    {
        $vehicle = $this->entityManager->getRepository(DvlaVehicle::class)->findOneByRegistration($vrm);

        return $vehicle;
    }

    /**
     * @return Colour
     */
    public function findColourByCode($code)
    {
        $colour = $this->entityManager->getRepository(Colour::class)->findOneByCode($code);

        return $colour;
    }

    /**
     * @return DvlaMake
     */
    public function findDvlaMakeByCode($code)
    {
        $dvlaMake = $this->entityManager->getRepository(DvlaMake::class)->findOneByCode($code);

        return $dvlaMake;
    }

    /**
     * @param $make_code
     * @param $model_code
     *
     * @return DvlaModel|null
     */
    public function findDvlaModelByMakeCodeModelCode($make_code, $model_code)
    {
        $dvlaModel = $this->entityManager->getRepository(DvlaModel::class)->findByMakeCodeModelCode($make_code, $model_code);

        return $dvlaModel;
    }
}
