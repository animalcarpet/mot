<?php
namespace Dvsa\Mot\Behat\Support;

use DvsaCommon\Constants\MotConfig\ElasticsearchConfigKeys;
use DvsaCommon\Factory\Date\RfrCurrentDateFakerFactory;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionResponseInterface;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionInterface;
use DvsaCommon\ReasonForRejection\ElasticSearch\Factory\ReasonForRejectionElasticSearchClientFactory;

class SearchReasonForRejectionElasticSearchClient implements SearchReasonForRejectionInterface
{
    private $client;

    public function __construct(
        string $hostName,
        string $port,
        string $indexName,
        string $region,
        string $fakeDate = ""
    )
    {
        $args = [
            ElasticsearchConfigKeys::ES_HOSTNAME => $hostName,
            ElasticsearchConfigKeys::ES_HOSTNAME_PORT => $port,
            ElasticsearchConfigKeys::ES_INDEX_NAME => $indexName,
            ElasticsearchConfigKeys::ES_REGION => $region
        ];

        $factory = new RfrCurrentDateFakerFactory();

        $date = null;
        if ($fakeDate !== "") {
            $date = new \DateTime($fakeDate);
        }

        $this->client = ReasonForRejectionElasticSearchClientFactory::createServiceWithArgs($args, $factory->createServiceWithArgs($date));
    }

    public function search(string $searchTerm, string $vehicleClassCode, string $audience, int $page): SearchReasonForRejectionResponseInterface
    {
        return $this->client->search($searchTerm, $vehicleClassCode, $audience, $page);
    }
}
