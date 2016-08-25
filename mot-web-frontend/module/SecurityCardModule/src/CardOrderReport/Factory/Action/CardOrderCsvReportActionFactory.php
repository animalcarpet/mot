<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Factory\Action;


use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Action\CardOrderCsvReportAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Action\CsvBuilder;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Date\DateTimeHolder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CardOrderCsvReportActionFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var TwoFaFeatureToggle $featureToggle */
        $featureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);
        /** @var MotFrontendAuthorisationServiceInterface $authService */
        $authService = $serviceLocator->get("AuthorisationService");
        /** @var $authorisationService AuthorisationService */
        $authorisationServiceClient = $serviceLocator->get(AuthorisationService::class);

        /** @var DateTimeHolder $dateTimeHolder */
        $dateTimeHolder = $serviceLocator->get(DateTimeHolder::class);

        return new CardOrderCsvReportAction(
            'php://temp',
            $authService,
            $authorisationServiceClient,
            $featureToggle,
            $dateTimeHolder
        );
    }
}
