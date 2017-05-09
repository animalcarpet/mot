<?php

namespace Core;

use Core\Service\LazyMotFrontendAuthorisationService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use Dvsa\Mot\Frontend\GoogleAnalyticsModule\TagManager\DataLayer;
use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;

/**
 * Core Module.
 */
class Module implements
BootstrapListenerInterface,
ConfigProviderInterface,
DependencyIndicatorInterface,
    ServiceProviderInterface
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return require __DIR__.'/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return require __DIR__.'/config/services.config.php';
    }

    /**
     * Expected to return an array of modules on which the current one depends on.
     *
     * @return array
     */
    public function getModuleDependencies()
    {
        return ['Dvsa\Mot\Frontend\GoogleAnalyticsModule'];
    }

    /**
     * Listen to the bootstrap event.
     *
     * @param EventInterface|MvcEvent $e
     *
     * @return array|void
     */
    public function onBootstrap(EventInterface $e)
    {
        if ($e->getRequest() instanceof ConsoleRequest) {
            return;
        }

        $this->appendUserIdAndRolesToGoogleTagManager($e->getApplication()->getServiceManager());
    }

    /**
     * @param ServiceManager $serviceManager
     */
    private function appendUserIdAndRolesToGoogleTagManager(ServiceManager $serviceManager)
    {
        /** @var MotFrontendIdentityInterface $identity */
        $identity = $serviceManager->get('MotIdentityProvider')->getIdentity();
        if (!$identity) {
            return;
        }

        $hashedUserId = hash('sha1', $identity->getUserId().'.'.$identity->getUsername());
        $dataLayer = $serviceManager->get(DataLayer::class);
        $dataLayer->add(['userId' => $hashedUserId]);

        /** @var LazyMotFrontendAuthorisationService $authorisationService */
        $authorisationService = $serviceManager->get('AuthorisationService');
        $dataLayer->add(['roles' => $authorisationService->getAllRoles()]);
    }
}
