<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTestSurvey;

interface MotTestSurveyRepository
{
    /**
     * @param string $token
     *
     * @return MotTestSurvey
     */
    public function findOneByToken($token);

    /**
     * @param int|string $userId
     *
     * @throws NotFoundException
     *
     * @return string
     */
    public function getLastUserSurveyDate($userId);

    /**
     * Returns the MOT Test ID.
     *
     * @return int
     */
    public function getLastSurveyMotTestId();
}
