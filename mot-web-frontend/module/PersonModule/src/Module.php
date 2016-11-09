<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModule;

use Dvsa\Mot\Frontend\PersonModule\Factory\View\PersonProfileUrlGeneratorViewHelperFactory;
use DvsaCommon\Constants\FeatureToggle;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;

/**
 * The Profile Module.
 */
class Module implements
    ConfigProviderInterface,
    ControllerProviderInterface,
    ServiceProviderInterface
{
    /**
     * @param ModuleManager $moduleManager
     */
    public function init(ModuleManager $moduleManager)
    {
        $eventManager = $moduleManager->getEventManager();

        $eventManager->attach(ModuleEvent::EVENT_MERGE_CONFIG, [$this, 'onMergeConfig']);
    }

    /**
     * @param ModuleEvent $e
     */
    public function onMergeConfig(ModuleEvent $e)
    {
        $configListener = $e->getConfigListener();
        $config         = $configListener->getMergedConfig(false);

        $configListener->setMergedConfig($config);
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
                'personProfileUrl' => PersonProfileUrlGeneratorViewHelperFactory::class,
            ],
        ];
    }
}
