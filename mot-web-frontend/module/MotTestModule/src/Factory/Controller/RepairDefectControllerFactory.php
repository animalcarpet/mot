<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\Controller;

use Dvsa\Mot\Frontend\MotTestModule\Controller\RepairDefectController;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creating RepairDefectController instances.
 */
class RepairDefectControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return RepairDefectController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceLocatorInterface $mainServiceManager */
        $mainServiceManager = $serviceLocator->getServiceLocator();

        $defectsJourneyUrlGenerator = $mainServiceManager->get(DefectsJourneyUrlGenerator::class);

        return new RepairDefectController($defectsJourneyUrlGenerator);
    }
}
