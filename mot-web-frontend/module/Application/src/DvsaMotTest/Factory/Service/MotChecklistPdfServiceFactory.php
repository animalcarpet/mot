<?php

namespace DvsaMotTest\Factory\Service;

use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\Pdf\Templating\ZendPdf\ZendPdfTemplate;
use DvsaMotTest\Presenter\MotChecklistPdfPresenter;
use DvsaMotTest\Service\MotChecklistPdfService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MotChecklistPdfServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotConfig $config */
        $config = $serviceLocator->get(MotConfig::class);

        return new MotChecklistPdfService(
            $serviceLocator->get(Client::class),
            $serviceLocator->get('MotIdentityProvider'),
            $serviceLocator->get(ZendPdfTemplate::class),
            $serviceLocator->get(MotChecklistPdfPresenter::class),
            $serviceLocator->get(MotTestService::class),
            $config->get('pdf', MotChecklistPdfService::MOT_CHECKLIST_CONFIG_KEY_MAIN),
            $serviceLocator->get(VehicleService::class)
        );
    }
}
