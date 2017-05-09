<?php

namespace Dvsa\Mot\Frontend\PersonModule\Factory\Controller;

use Application\Data\ApiPersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Controller\ChangeDateOfBirthController;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\MapperFactory;
use DvsaCommon\Validator\DateOfBirthValidator;
use UserAdmin\Service\HelpdeskAccountAdminService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChangeDateOfBirthControllerFactory implements FactoryInterface
{
    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        /** @var PersonProfileGuardBuilder $personProfileGuard */
        $personProfileGuard = $serviceLocator->get(PersonProfileGuardBuilder::class);
        /** @var HelpdeskAccountAdminService $helpdeskAccountAdminService */
        $helpdeskAccountAdminService = $serviceLocator->get(HelpdeskAccountAdminService::class);
        /** @var PersonProfileUrlGenerator $personProfileUrlGenerator */
        $personProfileUrlGenerator = $serviceLocator->get(PersonProfileUrlGenerator::class);
        /** @var ContextProvider $contextProvider */
        $contextProvider = $serviceLocator->get(ContextProvider::class);
        /** @var ApiPersonalDetails $apiPersonalDetails */
        $apiPersonalDetails = $serviceLocator->get(ApiPersonalDetails::class);
        /** @var MapperFactory $mapperFactory */
        $mapperFactory = $serviceLocator->get(MapperFactory::class);
        /** @var DateOfBirthValidator $dateOfBirthValidator */
        $dateOfBirthValidator = new DateOfBirthValidator();

        return new ChangeDateOfBirthController(
            $personProfileGuard,
            $helpdeskAccountAdminService,
            $personProfileUrlGenerator,
            $contextProvider,
            $apiPersonalDetails,
            $mapperFactory,
            $dateOfBirthValidator
        );
    }
}
