<?php
namespace DvsaCommon\ReasonForRejection\ElasticSearch\Factory;

use Aws\Credentials\CredentialProvider;
use DvsaCommon\Configuration\ApplicationEnv;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Constants\MotConfig\ElasticsearchConfigKeys;
use DvsaCommon\ReasonForRejection\ElasticSearch\ElasticsearchSettings;
use DvsaCommon\Constants\MotConfig\MotConfigKeys;
use DvsaCommon\ReasonForRejection\ElasticSearch\ReasonForRejectionElasticSearchClient;
use DvsaCommon\Utility\ArrayUtils;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Aws\ElasticsearchService\ElasticsearchPhpHandler;
use Elasticsearch\ClientBuilder;

class ReasonForRejectionElasticSearchClientFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $motConfig = $serviceLocator->get(MotConfig::class);
        $elasticSearchConfig = $motConfig->get(MotConfigKeys::ELASTICSEARCH);

        return self::createServiceWithArgs($elasticSearchConfig);
    }

    public static function createServiceWithArgs(array $elasticSearchConfig): ReasonForRejectionElasticSearchClient
    {
        $clientBuilder = ClientBuilder::create();

        $provider = null;
        $urlPrefix = "http";


        if (ApplicationEnv::isDevelopmentEnv() === false) {
            $urlPrefix = "https";
            $provider = CredentialProvider::instanceProfile();
            $memoizedProvider = CredentialProvider::memoize($provider);

            $handler = new ElasticsearchPhpHandler(
                ArrayUtils::get($elasticSearchConfig, ElasticsearchConfigKeys::ES_REGION),
                $memoizedProvider
            );

            $clientBuilder->setHandler($handler);
        }

        $esHostname = ArrayUtils::get($elasticSearchConfig, ElasticsearchConfigKeys::ES_HOSTNAME);
        if (empty($esHostname) === false) {
            $host = sprintf(
                "%s://%s:%s",
                $urlPrefix,
                ArrayUtils::get($elasticSearchConfig, ElasticsearchConfigKeys::ES_HOSTNAME),
                ArrayUtils::get($elasticSearchConfig, ElasticsearchConfigKeys::ES_HOSTNAME_PORT)
            );

            $clientBuilder->setHosts([$host]);
        }

        $client = $clientBuilder->build();

        return new ReasonForRejectionElasticSearchClient(
            $client,
            ArrayUtils::get($elasticSearchConfig, ElasticsearchConfigKeys::ES_INDEX_NAME),
            ElasticsearchSettings::ES_DOCUMENT_TYPE
        );
    }
}
