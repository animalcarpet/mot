<?php

namespace Application;

use Application\Data\ApiCurrentMotTest;
use Application\Data\ApiPersonalDetails;
use Application\Data\ApiUserSiteCount;
use Application\Factory\ApplicationSessionFactory;
use Application\Factory\ApplicationWideCacheFactory;
use Application\Factory\AuthAdapterFactory;
use Application\Factory\ContingencySessionManagerFactory;
use Application\Factory\Data\ApiCurrentMotTestFactory;
use Application\Factory\Data\ApiPersonalDetailsFactory;
use Application\Factory\Data\ApiUserSiteCountFactory;
use Application\Factory\Data\BrakeTestResultsResourceFactory;
use Application\Factory\Data\TesterInProgressTestNumberResourceFactory;
use Application\Factory\FileTemplateFactory;
use Application\Factory\LoggedInUserManagerFactory;
use Application\Factory\MotSessionFactory;
use Application\Factory\Service\CatalogServiceFactory;
use Application\Factory\ZendAuthenticationServiceFactory;
use Application\Listener\ChangeTempPasswordListener;
use Application\Listener\ClaimAccountListener;
use Application\Listener\ExpiredPasswordListener;
use Application\Listener\Factory\ChangeTempPasswordListenerFactory;
use Application\Listener\Factory\ClaimAccountListenerFactory;
use Application\Listener\Factory\ExpiredPasswordListenerFactory;
use Application\Listener\WebListenerEventsPriorities;
use Application\Navigation\Breadcrumbs\BreadcrumbsBuilder;
use Application\Navigation\Breadcrumbs\Factory\BreadcrumbsBuilderFactory;
use Application\Navigation\Breadcrumbs\Handler\Factory\OrganisationNameBySiteResolverFactory;
use Application\Navigation\Breadcrumbs\Handler\Factory\SimpleResolverFactory;
use Application\Navigation\Breadcrumbs\Handler\Factory\SiteNameResolverFactory;
use Application\Navigation\Breadcrumbs\Handler\OrganisationNameBySiteResolver;
use Application\Navigation\Breadcrumbs\Handler\SimpleResolver;
use Application\Navigation\Breadcrumbs\Handler\SiteNameResolver;
use Application\Service\CatalogService;
use Application\Service\ContingencySessionManager;
use Application\View\HelperFactory\AuthorisationHelperFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Listener\RegisterCardHardStopListener;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Listener\CardPinValidationListener;
use DvsaApplicationLogger\Log\FilteredStackTrace;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaFeature\Exception\FeatureNotAvailableException;
use DvsaMotTest\Data\BrakeTestResultsResource;
use DvsaMotTest\Data\TesterInProgressTestNumberResource;
use DvsaMotTest\Factory\BrakeTestConfigurationContainerFactory;
use DvsaMotTest\Factory\LocationSelectContainerFactory;
use DvsaMotTest\Factory\Model\VehicleSearchResultFactory;
use DvsaMotTest\Factory\Service\AuthorisedClassesServiceFactory;
use DvsaMotTest\Factory\Service\BrakeTestConfigurationServiceFactory;
use DvsaMotTest\Factory\Service\CertificatePrintingServiceFactory;
use DvsaMotTest\Factory\Service\MotTestCertificatesServiceFactory;
use DvsaMotTest\Factory\Service\VehicleSearchServiceFactory;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass1And2Mapper;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass3AndAboveMapper;
use DvsaMotTest\Model\BrakeTestConfigurationClass1And2Helper;
use DvsaMotTest\Model\BrakeTestConfigurationClass3AndAboveHelper;
use DvsaMotTest\Model\VehicleSearchResult;
use DvsaMotTest\Service\AuthorisedClassesService;
use DvsaMotTest\Service\BrakeTestConfigurationService;
use DvsaMotTest\Service\CertificatePrintingService;
use DvsaMotTest\Service\VehicleSearchService;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventInterface;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response;
use Zend\Log\Logger;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\View\Http\RouteNotFoundStrategy;

/**
 * Class Module.
 */
class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface,
    BootstrapListenerInterface
{
    const APPLICATION_SESSION = 'applicationSession';

    /**
     * {@inheritdoc}
     */
    public function onBootstrap(EventInterface $e)
    {
        if (!($e instanceof MvcEvent) || !($e->getRequest() instanceof HttpRequest)) {
            return;
        }

        $eventManager = $e->getApplication()->getEventManager();

        $claimAccountListener = $e->getApplication()->getServiceManager()->get(ClaimAccountListener::class);
        $eventManager->attach(
            MvcEvent::EVENT_DISPATCH,
            $claimAccountListener,
            WebListenerEventsPriorities::DISPATCH_CLAIM_ACCOUNT
        );

        $changeTempPasswordListener = $e->getApplication()->getServiceManager()->get(ChangeTempPasswordListener::class);
        $eventManager->attach(
            MvcEvent::EVENT_DISPATCH,
            $changeTempPasswordListener,
            WebListenerEventsPriorities::DISPATCH_CHANGE_TEMP_PASSWORD
        );

        if ($this->isPasswordExpiryEnabled($e)) {
            $resetPasswordListener = $e->getApplication()->getServiceManager()->get(ExpiredPasswordListener::class);
            $eventManager->attach(
                MvcEvent::EVENT_DISPATCH,
                $resetPasswordListener,
                WebListenerEventsPriorities::DISPATCH_RESET_EXPIRED_PASSWORD
            );
        }

        $cardPinValidation = $e->getApplication()->getServiceManager()->get(CardPinValidationListener::class);
        $eventManager->attach(
            MvcEvent::EVENT_DISPATCH,
            $cardPinValidation,
            WebListenerEventsPriorities::DISPATCH_CARD_VALIDATION
        );

        $registerCardHardStopListener = $e->getApplication()->getServiceManager()->get(RegisterCardHardStopListener::class);
        $eventManager->attach(
            MvcEvent::EVENT_DISPATCH,
            $registerCardHardStopListener,
            WebListenerEventsPriorities::DISPATCH_REGISTER_CARD_HARD_STOP
        );

        $eventManager->attach(
            MvcEvent::EVENT_RENDER,
            function ($e) {
                $viewModel = $e->getApplication()->getMvcEvent()->getViewModel();
                $viewModel->flashMessenger = new FlashMessenger();
            }
        );

        $eventManager->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            [$this, 'handleError'],
            WebListenerEventsPriorities::DISPATCH_ERROR_HANDLE_ERROR
        );
        $eventManager->attach(
            MvcEvent::EVENT_RENDER_ERROR,
            [$this, 'handleError'],
            WebListenerEventsPriorities::DISPATCH_ERROR_HANDLE_ERROR
        );
    }

    private function isPasswordExpiryEnabled(EventInterface $e)
    {
        /** @var MotConfig $config */
        $config = $e->getApplication()->getServiceManager()->get(MotConfig::class);

        return $config->get('feature_toggle', 'openam.password.expiry.enabled');
    }

    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
    }

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                self::APPLICATION_SESSION => ApplicationSessionFactory::class,
                'CatalogService' => CatalogServiceFactory::class,
                CatalogService::class => CatalogServiceFactory::class,
                'AuthAdapter' => AuthAdapterFactory::class,
                'ZendAuthenticationService' => ZendAuthenticationServiceFactory::class,
                'ApplicationWideCache' => ApplicationWideCacheFactory::class,
                'MotSession' => MotSessionFactory::class,
                'FileTemplate' => FileTemplateFactory::class,
                'LoggedInUserManager' => LoggedInUserManagerFactory::class,
                ApiPersonalDetails::class => ApiPersonalDetailsFactory::class,
                ApiCurrentMotTest::class => ApiCurrentMotTestFactory::class,
                ApiUserSiteCount::class => ApiUserSiteCountFactory::class,
                'Logger' => \DvsaApplicationLogger\Factory\LoggerFactory::class,
                TesterInProgressTestNumberResource::class => TesterInProgressTestNumberResourceFactory::class,
                BrakeTestResultsResource::class => BrakeTestResultsResourceFactory::class,
                ContingencySessionManager::class => ContingencySessionManagerFactory::class,
                ClaimAccountListener::class => ClaimAccountListenerFactory::class,
                ExpiredPasswordListener::class => ExpiredPasswordListenerFactory::class,
                ChangeTempPasswordListener::class => ChangeTempPasswordListenerFactory::class,
                'BrakeTestConfigurationContainerHelper' => BrakeTestConfigurationContainerFactory::class,
                'LocationSelectContainerHelper' => LocationSelectContainerFactory::class,
                AuthorisedClassesService::class => AuthorisedClassesServiceFactory::class,
                VehicleSearchResult::class => VehicleSearchResultFactory::class,
                VehicleSearchService::class => VehicleSearchServiceFactory::class,
                'MotTestCertificatesService' => MotTestCertificatesServiceFactory::class,
                CertificatePrintingService::class => CertificatePrintingServiceFactory::class,
                BreadcrumbsBuilder::class => BreadcrumbsBuilderFactory::class,
                SimpleResolver::class => SimpleResolverFactory::class,
                SiteNameResolver::class => SiteNameResolverFactory::class,
                OrganisationNameBySiteResolver::class => OrganisationNameBySiteResolverFactory::class,
                'AuthorisationHelper' => AuthorisationHelperFactory::class,
                OrganisationNameBySiteResolver::class => OrganisationNameBySiteResolverFactory::class,
                BrakeTestConfigurationService::class => BrakeTestConfigurationServiceFactory::class,
            ],
            'aliases' => [
                AuthenticationService::class => 'ZendAuthenticationService',
            ],
            'invokables' => [
                BrakeTestConfigurationClass1And2Helper::class => BrakeTestConfigurationClass1And2Helper::class,
                BrakeTestConfigurationClass3AndAboveHelper::class => BrakeTestConfigurationClass3AndAboveHelper::class,
                BrakeTestConfigurationClass1And2Mapper::class => BrakeTestConfigurationClass1And2Mapper::class,
                BrakeTestConfigurationClass3AndAboveMapper::class => BrakeTestConfigurationClass3AndAboveMapper::class,
            ],
        ];
    }

    public function handleError(MvcEvent $e)
    {
        $this->verifyIsError($e);

        $error = $e->getError();

        $exception = $e->getParam('exception');

        $serviceManager = $e->getApplication()->getServiceManager();

        /** @var $routeNotFoundStrategy RouteNotFoundStrategy */
        $routeNotFoundStrategy = $serviceManager->get('HttpRouteNotFoundStrategy');

        $eid = false;

        /** @var $logger \Zend\Log\Logger */
        $logger = $serviceManager->get('Application/Logger');
        if ($exception) {
            $eid = uniqid();
            $logger->err('error id: '.$eid);
            $logger->err('error: '.$error);
            $logger->err('class: '.get_class($exception));
            $logger->err('file: '.$exception->getFile());
            $logger->err('line: '.$exception->getLine());
            $logger->err('message: '.$exception->getMessage());
            $logger->err('stacktrace: '.(new FilteredStackTrace())->getTraceAsString($exception));
        }

        $config = $serviceManager->get('config');

        $viewModel = $e->getResult();
        $viewModel->setVariables(['showErrorsInFrontEnd' => $config['showErrorsInFrontEnd'], 'errorId' => $eid]);

        if ($exception instanceof FeatureNotAvailableException) {
            $e->getResponse()->setStatusCode(Response::STATUS_CODE_404);
            $routeNotFoundStrategy->prepareNotFoundViewModel($e);
        }

        if ($exception instanceof GeneralRestException) {
            $e->getResponse()->setStatusCode($exception->getCode());
            if ($exception instanceof NotFoundException) {
                $routeNotFoundStrategy->prepareNotFoundViewModel($e);
            }
        } elseif ($exception instanceof UnauthorisedException) {
            $viewModel->setTemplate('error/permission-error.phtml');
        }

        return $viewModel;
    }

    /**
     * @param MvcEvent $e
     *
     * @throws \LogicException
     */
    private function verifyIsError(MvcEvent $e)
    {
        $error = $e->getError();
        if (!$error) {
            throw new \LogicException('This listener is only meant to be called on errors');
        }
    }
}
