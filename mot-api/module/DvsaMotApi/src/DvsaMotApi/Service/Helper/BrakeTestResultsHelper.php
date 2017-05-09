<?php

namespace DvsaMotApi\Service\Helper;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\BrakeTestResultClass12;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;

/**
 * Class BrakeTestResultsHelper.
 */
class BrakeTestResultsHelper
{
    /** @var EntityManager $entityManager */
    private $entityManager;

    /**
     * BrakeTestResultsHelper constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param MotTest $motTest
     */
    public function deleteAllBrakeTestResults(MotTest $motTest)
    {
        $this->deleteBrakeTestResultsForClass12($motTest);
        $this->deleteBrakeTestResultsForClass3AndAbove($motTest);
    }

    /**
     * @param MotTest $motTest
     */
    private function deleteBrakeTestResultsForClass12(MotTest $motTest)
    {
        $repository = BrakeTestResultClass12::class;
        $brakeTestResultsRepo = $this->entityManager->getRepository($repository);
        $brakeTestResults = $brakeTestResultsRepo->findBy(['motTest' => $motTest->getId()]);
        foreach ($brakeTestResults as $brakeTestResult) {
            $this->entityManager->remove($brakeTestResult);
        }
        $this->entityManager->flush();
    }

    /**
     * @param MotTest $motTest
     */
    private function deleteBrakeTestResultsForClass3AndAbove(MotTest $motTest)
    {
        $repository = BrakeTestResultClass3AndAbove::class;
        $brakeTestResultsRepo = $this->entityManager->getRepository($repository);
        $brakeTestResults = $brakeTestResultsRepo->findBy(['motTest' => $motTest->getId()]);
        foreach ($brakeTestResults as $brakeTestResult) {
            $this->entityManager->remove($brakeTestResult);
        }
        $this->entityManager->flush();
    }
}
