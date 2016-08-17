<?php

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\View;

use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGeneratorViewHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DefectsJourneyUrlGeneratorViewHelperFactory  implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return DefectsJourneyUrlGeneratorViewHelper
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $parentLocator = $serviceLocator->getServiceLocator();

        /** @var DefectsJourneyUrlGenerator $urlGenerator */
        $urlGenerator = $parentLocator->get(DefectsJourneyUrlGenerator::class);
        /** @var DefectsJourneyContextProvider $contextProvider */
        $contextProvider = $parentLocator->get(DefectsJourneyContextProvider::class);

        return new DefectsJourneyUrlGeneratorViewHelper(
            $urlGenerator,
            $contextProvider
        );
    }
}