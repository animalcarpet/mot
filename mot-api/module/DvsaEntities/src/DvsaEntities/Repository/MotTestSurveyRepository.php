<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestSurvey;

interface MotTestSurveyRepository
{
    /**
     * @param string $token
     * @return MotTestSurvey
     */
    public function findByToken($token);

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
