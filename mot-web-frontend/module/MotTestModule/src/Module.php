<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule;

use Dvsa\Mot\Frontend\MotTestModule\Factory\View\DefectsJourneyUrlGeneratorViewHelperFactory;
use Dvsa\Mot\Frontend\MotTestModule\Listener\SatisfactionSurveyListener;
use DvsaCommon\Constants\FeatureToggle;
use DvsaFeature\FeatureToggles;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
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
     * {@inheritdoc}
     */
    public function init(ModuleManager $moduleManager)
    {
        $events = $moduleManager->getEventManager();

        /*
         * Registering a listener at default priority (1) which will trigger after the ConfigListener merges config.
         *
         * NOTE: If a cached config is used by the ModuleManager, the EVENT_MERGE_CONFIG event will not be triggered.
         * However, typically that means that what is cached will be what was originally manipulated by your listener.
         */
        $events->attach(ModuleEvent::EVENT_MERGE_CONFIG, [$this, 'onMergeConfig']);
    }

    /**
     * {@inheritdoc}
     */
    public function onMergeConfig(ModuleEvent $e)
    {
        $configListener = $e->getConfigListener();
        $config = $configListener->getMergedConfig(false);

        /*
         * Check if the new Test Result Entry (BL-1024) screens are enabled as a feature toggle.
         *
         * This is a risky move as if the config root key provided by the FeatureToggles module changes we are in
         * trouble. Ideally we would load the module and use the "Dvsa\FeatureToggles::isEnabled()" method to check
         * if the feature is enabled but loading the module here is not an option. So be it.
         */
        if (isset($config['feature_toggle'][FeatureToggle::TEST_RESULT_ENTRY_IMPROVEMENTS])
            && true === $config['feature_toggle'][FeatureToggle::TEST_RESULT_ENTRY_IMPROVEMENTS]) {
            return;
        }

        // Remove the routes from the configuration.
        if (isset($config['router']['routes'][self::TOP_LEVEL_ROUTE])) {
            unset($config['router']['routes'][self::TOP_LEVEL_ROUTE]);
            unset($config['router']['routes'][self::ODOMETER_ROUTE]);
            unset($config['router']['routes'][self::DEFECT_CATEGORIES_ROUTE]);
        }

        // Pass the changed configuration back to the listener:
        $configListener->setMergedConfig($config);
    }

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
        /** @var FeatureToggles $featureToggles */
        $featureToggles = $serviceManager->get('Feature\FeatureToggles');
        if (true !== $featureToggles->isEnabled(FeatureToggle::SURVEY_PAGE)) {
            return;
        }

        /** @var SatisfactionSurveyListener $satisfactionSurveyListener */
        $satisfactionSurveyListener = $serviceManager->get(SatisfactionSurveyListener::class);
        $satisfactionSurveyListener->attach();
    }
}
