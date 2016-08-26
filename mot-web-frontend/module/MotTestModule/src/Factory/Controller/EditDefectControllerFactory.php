<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\Controller;

use Dvsa\Mot\Frontend\MotTestModule\Controller\EditDefectController;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creating EditDefectController instances.
 */
class EditDefectControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return EditDefectController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceLocatorInterface $mainServiceManager */
        $mainServiceManager = $serviceLocator->getServiceLocator();

        /** @var DefectsJourneyContextProvider $defectsJourneyContextProvider */
        $defectsJourneyContextProvider = $mainServiceManager->get(DefectsJourneyContextProvider::class);

        /** @var DefectsJourneyUrlGenerator $defectsJourneyUrlGenerator */
        $defectsJourneyUrlGenerator = $mainServiceManager->get(DefectsJourneyUrlGenerator::class);

        return new EditDefectController($defectsJourneyContextProvider, $defectsJourneyUrlGenerator);
    }
}
