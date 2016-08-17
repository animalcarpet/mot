<?php

namespace TestSupport\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Connection;
use DvsaEntities\Entity\MotTest;

class MotService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Update the mot_test entry with the associated $id with $data
     *
     * @param int $id
     * @param array $data
     */
    public function update($id, $data)
    {
        //update vehicle with new data
        $this->entityManager->getConnection()->update(
            'mot_test', $data, ['id' => $id]
        );
    }

    /**
     * Updates the latest mot_test entry associated with $vehicleId with $data
     * @param $vehicleId
     * @param array $data
     */
    public function updateLatest($vehicleId, $data)
    {
        $motTest = $this->getLatestTest($vehicleId);

        // update the test
        $this->update($motTest['id'], $data);
    }

    /**
     * Get the latest mot_test entry's id
     *
     * @param $vehicleId
     * @return int
     */
    public function getLatestTest($vehicleId)
    {
        // order by id since this should retrieve us the most recent record
        // instead of adding last_updated_on and last_created_on columns to MotTest Entity
        $sql = $this->entityManager->createQueryBuilder()
            ->select('mt.id')
            ->from(MotTest::class, 'mt')
            ->where('mt.vehicle = ?1')
            ->orderBy('mt.id', 'DESC')
            ->setParameter(1, $vehicleId);

        return $sql->getQuery()->getSingleResult();
    }

    public function changeDate($motNumber, \DateTime $startedDate, \DateTime $completedDate)
    {
        $this->entityManager->getConnection()->update(
            'mot_test',
            [
                'started_date' => $startedDate->format("Y-m-d H:i:s"),
                'completed_date' => $completedDate->format("Y-m-d H:i:s")
            ],
            ['number' => $motNumber]
        );
    }

    public function removeAllTests()
    {
        $this->entityManager->getConnection()->prepare('DELETE FROM brake_test_result_class_1_2')->execute();
        $this->entityManager->getConnection()->prepare('DELETE FROM brake_test_result_class_3_and_above')->execute();
        $this->entityManager->getConnection()->prepare('DELETE FROM enforcement_mot_test_differences')->execute();
        $this->entityManager->getConnection()->prepare('DELETE FROM enforcement_mot_demo_test')->execute();
        $this->entityManager->getConnection()->prepare('DELETE FROM enforcement_mot_test_result_witnesses')->execute();
        $this->entityManager->getConnection()->prepare('DELETE FROM enforcement_mot_test_result')->execute();
        $this->entityManager->getConnection()->prepare('DELETE FROM mot_test_rfr_map')->execute();
        $this->entityManager->getConnection()->prepare('UPDATE mot_test SET prs_mot_test_id = NULL, mot_test_id_original = NULL')->execute();
        $this->entityManager->getConnection()->prepare('DELETE FROM certificate_replacement')->execute();
        $this->entityManager->getConnection()->prepare('DELETE FROM replacement_certificate_draft')->execute();
        $this->entityManager->getConnection()->prepare('DELETE FROM mot_test_event')->execute();
        $this->entityManager->getConnection()->prepare('DELETE FROM mot_test_recent_certificate')->execute();
        $this->entityManager->getConnection()->prepare('DELETE FROM mot_test_survey')->execute();
        $this->entityManager->getConnection()->prepare('DELETE FROM mot_test')->execute();
    }
}
