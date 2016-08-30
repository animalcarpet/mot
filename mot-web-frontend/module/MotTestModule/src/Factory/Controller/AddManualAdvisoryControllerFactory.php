<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\Controller;

use Dvsa\Mot\Frontend\MotTestModule\Controller\AddManualAdvisoryController;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creating AddManualAdvisoryController instances.
 */
class AddManualAdvisoryControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AddManualAdvisoryController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceLocatorInterface|ControllerManager $mainServiceManager */
        $mainServiceManager = $serviceLocator->getServiceLocator();

        /** @var DefectsJourneyUrlGenerator $defectsJourneyUrlGenerator */
        $defectsJourneyUrlGenerator = $mainServiceManager->get(DefectsJourneyUrlGenerator::class);
        /** @var DefectsJourneyContextProvider $defectsJourneyContextProvider */
        $defectsJourneyContextProvider = $mainServiceManager->get(DefectsJourneyContextProvider::class);

        return new AddManualAdvisoryController($defectsJourneyUrlGenerator, $defectsJourneyContextProvider);
    }
}
