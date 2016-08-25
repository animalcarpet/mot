<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModule\Factory\Security;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dashboard\Service\TradeRolesAssociationsService;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuard;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creating PersonProfileGuard instances.
 */
class PersonProfileGuardBuilderFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PersonProfileGuard
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotFrontendAuthorisationServiceInterface $authorisationService */
        $authorisationService = $serviceLocator->get('AuthorisationService');

        /** @var MotIdentityProviderInterface $identityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        /** @var TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper */
        $testerGroupAuthorisationMapper = $serviceLocator->get(TesterGroupAuthorisationMapper::class);

        /** @var TradeRolesAssociationsService $tradeRolesAssociationsService */
        $tradeRolesAssociationsService = $serviceLocator->get(TradeRolesAssociationsService::class);

        $twoFaFeatureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);

        return new PersonProfileGuardBuilder($authorisationService, $identityProvider, $testerGroupAuthorisationMapper,
            $tradeRolesAssociationsService, $twoFaFeatureToggle);
    }
}
