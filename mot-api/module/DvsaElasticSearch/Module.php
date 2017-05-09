<?php

namespace DvsaElasticSearch;

use DvsaElasticSearch\Service\ElasticSearchService;
use DvsaEntities\Repository\SiteRepository;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class.
 */
class Module implements AutoloaderProviderInterface, ServiceProviderInterface
{
    public function getAutoloaderConfig()
    {
    }

    /**
     * Note: we only register the configuration service at this point! We manually register
     * the connection and search services once the bootstrap event is fired for this module.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                'ElasticSearchService' => function (ServiceLocatorInterface $sm) {
                    return new ElasticSearchService(
                        $sm->get('DvsaAuthorisationService'),
                        $sm->get(SiteRepository::class),
                        $sm->get('Feature\FeatureToggles')
                    );
                },
            ],
        ];
    }
}
