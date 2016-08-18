<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestSurveyResult;

interface MotTestSurveyResultRepository
{
    /**
     * @param int|string $motTestId
     * @return MotTestSurveyResult
     */
    public function findByMotTestId($motTestId);

    /**
     * @param int|string $userId
     * @return string
     * @throws NotFoundException
     */
    public function getLastUserSurveyDate($userId);

    /**
     * @return MotTest
     */
    public function getLastUserSurveyTest();

}
