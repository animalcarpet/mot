<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule;

use Dvsa\Mot\Frontend\MotTestModule\Factory\View\DefectsJourneyUrlGeneratorViewHelperFactory;
use Dvsa\Mot\Frontend\MotTestModule\Listener\SatisfactionSurveyListener;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * The MotTest Module.
 */
class Module implements ConfigProviderInterface, ControllerProviderInterface, ServiceProviderInterface
{
    const TOP_LEVEL_ROUTE = 'mot-test-defects';
    const ODOMETER_ROUTE = 'odometer';
    const DEFECT_CATEGORIES_ROUTE = 'mot-test-defects/categories';

    /**
     * @param \Zend\Mvc\MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        $this->registerSatisfactionSurveyListener($event->getApplication()->getServiceManager());
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = array_merge(
            include __DIR__ . '/../config/routes.config.php',
            include __DIR__ . '/../config/module.config.php'
        );

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerConfig()
    {
        return include __DIR__ . '/../config/controllers.config.php';
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/../config/services.config.php';
    }

    /**
     * {@inheritdoc}
     */
    public function getViewHelperConfig()
    {
        return [
            'factories' => [
                'defectJourneyUrl' => DefectsJourneyUrlGeneratorViewHelperFactory::class,
            ],
        ];
    }

    /**
     * @param ServiceLocatorInterface $serviceManager
     */
    private function registerSatisfactionSurveyListener(ServiceLocatorInterface $serviceManager)
    {
        /** @var SatisfactionSurveyListener $satisfactionSurveyListener */
        $satisfactionSurveyListener = $serviceManager->get(SatisfactionSurveyListener::class);
        $satisfactionSurveyListener->attach();
    }
}
