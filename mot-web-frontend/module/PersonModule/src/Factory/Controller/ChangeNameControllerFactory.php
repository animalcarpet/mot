<?php

namespace Dvsa\Mot\Frontend\PersonModule\Factory\Controller;

use Application\Data\ApiPersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Controller\ChangeNameController;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\MapperFactory;
use UserAdmin\Service\HelpdeskAccountAdminService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class ChangeNameControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {

        /**
         * @var ServiceManager
         */
        $serviceLocator = $controllerManager->getServiceLocator();

        /**
         * @var PersonProfileGuardBuilder
         */
        $personProfileGuardBuilder = $serviceLocator->get(PersonProfileGuardBuilder::class);

        /**
         * @var HelpDeskAccountAdminService
         */
        $helpDeskAccountAdminService = $serviceLocator->get(HelpDeskAccountAdminService::class);

        /**
         * @var PersonProfileUrlGenerator
         */
        $personProfileUrlBuilder = $serviceLocator->get(PersonProfileUrlGenerator::class);

        /**
         * @var ContextProvider
         */
        $contextProvider = $serviceLocator->get(ContextProvider::class);

        /**
         * @var ApiPersonalDetails
         */
        $personalDetailsService = $serviceLocator->get(ApiPersonalDetails::class);

        /**
         * @var MapperFactory
         */
        $mapperFactory = $serviceLocator->get(MapperFactory::class);

        return new ChangeNameController(
            $personProfileGuardBuilder,
            $helpDeskAccountAdminService,
            $personProfileUrlBuilder,
            $contextProvider,
            $personalDetailsService,
            $mapperFactory
        );
    }
}
