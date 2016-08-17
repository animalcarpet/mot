<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\Controller;

use Dvsa\Mot\Frontend\MotTestModule\Controller\RemoveDefectController;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creating RemoveDefectController instances.
 */
class RemoveDefectControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return RemoveDefectController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceLocatorInterface $mainServiceManager */
        $mainServiceManager = $serviceLocator->getServiceLocator();

        /*
         * @var DefectsJourneyContextProvider
         */
        $defectsJourneyContextProvider = $mainServiceManager->get(DefectsJourneyContextProvider::class);

        /*
         * @var DefectsJourneyUrlGenerator
         */
        $defectsJourneyUrlGenerator = $mainServiceManager->get(DefectsJourneyUrlGenerator::class);

        return new RemoveDefectController($defectsJourneyContextProvider, $defectsJourneyUrlGenerator);
    }
}
