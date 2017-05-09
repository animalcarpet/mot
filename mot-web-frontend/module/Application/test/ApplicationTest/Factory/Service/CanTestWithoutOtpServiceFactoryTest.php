<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace ApplicationTest\Factory\Service;

use Application\Service\CanTestWithoutOtpService;
use Application\Factory\Service\CanTestWithoutOtpServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use Core\Service\MotFrontendIdentityProvider;

class CanTestWithoutOtpServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreates()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            CanTestWithoutOtpServiceFactory::class,
            CanTestWithoutOtpService::class,
            [
                'MotIdentityProvider' => MotFrontendIdentityProvider::class,
                'AuthorisationService' => MotAuthorisationServiceInterface::class,
                TwoFaFeatureToggle::class,
            ]
        );
    }
}
