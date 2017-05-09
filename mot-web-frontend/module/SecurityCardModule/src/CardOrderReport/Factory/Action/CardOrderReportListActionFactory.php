<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Factory\Action;

use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Action\CardOrderReportListAction;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Date\DateTimeHolder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CardOrderReportListActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $featureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);
        $authService = $serviceLocator->get('AuthorisationService');
        /** @var $authorisationService AuthorisationService */
        $authorisationService = $serviceLocator->get(AuthorisationService::class);

        /** @var DateTimeHolder $dateTimeHolder */
        $dateTimeHolder = $serviceLocator->get(DateTimeHolder::class);

        return new CardOrderReportListAction(
            $authService,
            $authorisationService,
            $featureToggle,
            $dateTimeHolder
        );
    }
}
