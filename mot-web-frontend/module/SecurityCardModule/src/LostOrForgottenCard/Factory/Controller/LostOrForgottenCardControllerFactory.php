<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Factory\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\AlreadyLoggedInTodayWithLostForgottenCardCookieService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\AlreadyOrderedCardCookieService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Specification\ContainsTwoSecurityQuestionDtoSpecification;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\MotIdentityProvider;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Configuration\MotConfig;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenService;

class LostOrForgottenCardControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return LostOrForgottenCardController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $serviceLocator->getServiceLocator();

        /** @var Request $request */
        $request = $serviceLocator->get('Request');

        /** @var Response $response */
        $response = $serviceLocator->get('Response');

        /** @var MotIdentityProviderInterface $identityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        /** @var TwoFaFeatureToggle $twoFaFeatureToggle */
        $twoFaFeatureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);

        /** @var LostOrForgottenService $securityQuestionService */
        $securityQuestionService = $serviceLocator->get(LostOrForgottenService::class);

        /** @var SecurityCardService $securityCardService */
        $securityCardService = $serviceLocator->get(SecurityCardService::class);

        /** @var AlreadyOrderedCardCookieService $alreadyOrderedCardCookieService */
        $alreadyOrderedCardCookieService = new AlreadyOrderedCardCookieService($identityProvider);

        /** @var AlreadyLoggedInTodayWithLostForgottenCardCookieService $cookieService */
        $cookieService = $serviceLocator->get(AlreadyLoggedInTodayWithLostForgottenCardCookieService::class);

        /** @var ContainsTwoSecurityQuestionDtoSpecification $specification */
        $specification = new ContainsTwoSecurityQuestionDtoSpecification();

        /** @var MotConfig $motConfig */
        $motConfig = $serviceLocator->get(MotConfig::class);

        /** @var array $helpDeskConfigInfo */
        $helpDeskConfigInfo = $motConfig->get('helpdesk');

        return new LostOrForgottenCardController(
            $request,
            $response,
            $identityProvider->getIdentity(),
            $twoFaFeatureToggle,
            $securityQuestionService,
            $securityCardService,
            $alreadyOrderedCardCookieService,
            $cookieService,
            $specification,
            $helpDeskConfigInfo
        );
    }
}
