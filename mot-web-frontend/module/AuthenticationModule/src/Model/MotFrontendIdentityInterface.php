<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModule\Model;

use DvsaCommon\Auth\MotIdentityInterface;

/**
 * Interface MotFrontendIdentityInterface
 */
interface MotFrontendIdentityInterface extends MotIdentityInterface
{
    /**
     * @return string
     */
    public function getDisplayName();

    /**
     * @return \Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation
     */
    public function getCurrentVts();

    public function hasPasswordExpired();

    public function isAuthenticatedWithLostForgotten();
}
