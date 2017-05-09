<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModuleTest\Factory\Security;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dashboard\Service\TradeRolesAssociationsService;
use Dvsa\Mot\Frontend\PersonModule\Factory\Security\PersonProfileGuardBuilderFactory;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;

class PersonProfileGuardFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            PersonProfileGuardBuilderFactory::class,
            PersonProfileGuardBuilder::class,
            [
                'AuthorisationService' => MotFrontendAuthorisationServiceInterface::class,
                'MotIdentityProvider' => MotIdentityProviderInterface::class,
                TesterGroupAuthorisationMapper::class => TesterGroupAuthorisationMapper::class,
                TradeRolesAssociationsService::class => TradeRolesAssociationsService::class,
                TwoFaFeatureToggle::class,
            ]
        );
    }
}
