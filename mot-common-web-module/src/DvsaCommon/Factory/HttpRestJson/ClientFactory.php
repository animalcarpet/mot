<?php

namespace DvsaCommon\Factory\HttpRestJson;

use Doctrine\Common\Cache\Cache as DoctrineCache;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\HttpRestJson\CachingClient;
use DvsaCommon\HttpRestJson\CachingClient\Cache;
use DvsaCommon\HttpRestJson\CachingClient\CacheContextFactory\MotTestCacheContextFactory;
use DvsaCommon\HttpRestJson\CachingClient\CacheContextFactory\PersonCacheContextFactory;
use DvsaCommon\HttpRestJson\CachingClient\CacheContextFactory\SiteNameCacheContextFactory;
use DvsaCommon\HttpRestJson\CachingClient\CacheContextFactory\OrganisationNameCacheContextFactory;

use DvsaCommon\HttpRestJson\CachingClient\ChainedCacheContextFactory;
use DvsaCommon\HttpRestJson\ZendClient;
use Zend\Http\Client as HttpClient;
use Zend\Http\Client\Adapter\Curl;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ClientFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        /** @var MotConfig $motConfig */
        $motConfig = $serviceLocator->get(MotConfig::class);

        $logger = isset($config['logApiCalls']) ? $serviceLocator->get('Logger') : null;

        $client = new ZendClient(
            $this->createHttpClient(),
            $this->getApiUrl($config),
            $serviceLocator->get('tokenService')->getToken(),
            $logger,
            $this->getRequestUuid($config),
            $motConfig->withDefault(ZendClient::DEFAULT_API_TIMEOUT)->get('apiTimeout')
        );

        if ($this->isCacheEnabled($config)) {
            $client = new CachingClient(
                $client,
                new Cache($serviceLocator->get(DoctrineCache::class)),
                new ChainedCacheContextFactory($this->getCacheContextFactories($serviceLocator))
            );

        }

        return $client;
    }

    /**
     * @param array $config
     *
     * @return string
     */
    private function getApiUrl(array $config)
    {
        if (!isset($config['apiUrl'])) {
            throw new \RuntimeException('API url was not configured');
        }

        return $config['apiUrl'];
    }

    /**
     * @param array $config
     *
     * @return string
     */
    private function getRequestUuid(array $config)
    {
        if (isset($config['DvsaLogger']) && isset($config['DvsaLogger']['RequestUUID'])) {
            return $config['DvsaLogger']['RequestUUID'];
        }

        return uniqid();
    }

    /**
     * @param array $config
     *
     * @return array
     */
    private function getCacheContextFactories(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        return [
            new MotTestCacheContextFactory($this->getMotTestCacheLifeTime($config)),
            new PersonCacheContextFactory($serviceLocator->get('tokenService'), $this->getPersonCacheLifeTime($config)),
            new SiteNameCacheContextFactory($this->getSiteNameCacheLifeTime($config)),
            new OrganisationNameCacheContextFactory($this->getOrganisationNameCacheLifeTime($config))
        ];
    }

    /**
     * @param array $config
     *
     * @return bool
     */
    private function isCacheEnabled(array $config)
    {
        return isset($config['rest_client']['cache']['enabled']) ? (bool)$config['rest_client']['cache']['enabled'] : false;
    }

    /**
     * @param array $config
     *
     * @return int
     */
    private function getMotTestCacheLifeTime($config)
    {
        return isset($config['rest_client']['cache']['mot-test']['lifetime'])
            ? $config['rest_client']['cache']['mot-test']['lifetime']
            : MotTestCacheContextFactory::DEFAULT_LIFE_TIME;
    }

    /**
     * @param array $config
     *
     * @return int
     */
    private function getPersonCacheLifeTime($config)
    {
        return isset($config['rest_client']['cache']['person']['lifetime'])
            ? $config['rest_client']['cache']['person']['lifetime']
            : PersonCacheContextFactory::DEFAULT_LIFE_TIME;
    }

    private function getSiteNameCacheLifeTime($config)
    {
        return isset($config['rest_client']['cache']['site-name']['lifetime'])
            ? $config['rest_client']['cache']['site-name']['lifetime']
            : SiteNameCacheContextFactory::DEFAULT_LIFE_TIME;
    }

    private function getOrganisationNameCacheLifeTime($config)
    {
        return isset($config['rest_client']['cache']['organisation-name']['lifetime'])
            ? $config['rest_client']['cache']['organisation-name']['lifetime']
            : OrganisationNameCacheContextFactory::DEFAULT_LIFE_TIME;
    }

    /**
     * @return HttpClient
     */
    private function createHttpClient()
    {
        $httpClient = new HttpClient();
        $httpClient->setAdapter(Curl::class);

        return $httpClient;
    }
}