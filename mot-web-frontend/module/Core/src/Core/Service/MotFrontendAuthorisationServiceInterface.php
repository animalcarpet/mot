<?php

namespace Core\Service;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;

/**
 * Extends the MotAuthorisationServiceInterface for extra methods that are used on the frontend.
 *
 * Note - ideally all these methods would be removed, but they were already in extensive use.
 */
interface MotFrontendAuthorisationServiceInterface extends MotAuthorisationServiceInterface
{
    /**
     * @deprecated use permissions, not roles
     */
    public function isVehicleExaminer();

    /**
     * @deprecated use permissions, not roles
     */
    public function isTester();

    /**
     * @deprecated use permissions, not roles
     */
    public function isDvsa();
}
