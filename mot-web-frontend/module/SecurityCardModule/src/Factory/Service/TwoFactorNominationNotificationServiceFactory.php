<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Factory\Service;

use Application\Data\ApiPersonalDetails;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\TwoFactorNominationNotificationService;
use DvsaClient\MapperFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TwoFactorNominationNotificationServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SecurityCardService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $personalDetails ApiPersonalDetails */
        $personalDetails = $serviceLocator->get(ApiPersonalDetails::class);

        /** @var MapperFactory $mapperFactory */
        $mapperFactory = $serviceLocator->get(MapperFactory::class);

        return new TwoFactorNominationNotificationService(
            $personalDetails,
            $mapperFactory->OrganisationPosition,
            $mapperFactory->SitePosition
        );
    }
}
