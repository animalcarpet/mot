<?php
namespace DvsaCommon\ReasonForRejection\ElasticSearch;

use DvsaCommon\ReasonForRejection\ElasticSearch\QueryBuilder\ReasonForRejectionElasticSearchQueryBuilder;
use DvsaCommon\ReasonForRejection\ReasonForRejectionDtoMapper;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionInterface;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionResponseInterface;
use Elasticsearch\Client;

class ReasonForRejectionElasticSearchClient implements SearchReasonForRejectionInterface
{
    /** @var Client  */
    private $client;
    /** @var ReasonForRejectionElasticSearchQueryBuilder */
    private $reasonForRejectionElasticSearchQueryBuilder;

    public function __construct(Client $client, string $indexName, string $documentType)
    {
        $this->client = $client;
        $this->reasonForRejectionElasticSearchQueryBuilder = new ReasonForRejectionElasticSearchQueryBuilder($indexName, $documentType);
    }

    public function search(string $searchTerm, string $vehicleClassCode, string $audience, int $page): SearchReasonForRejectionResponseInterface
    {
        $searchTerm = str_replace(
            ['+', '<', '>', '&', '@', '(', ')', '~', '*', '"'],
            ' ',
            $searchTerm
        );

        $countResponse = $this->client->count($this->reasonForRejectionElasticSearchQueryBuilder->buildCountQuery($searchTerm, $vehicleClassCode, $audience));
        $count = (int) $countResponse["count"];

        $lastPage = (int) ceil($count/SearchReasonForRejectionInterface::ITEMS_PER_PAGE);

        if ($lastPage < 0) {
            $page = 1;
        } elseif ($lastPage < $page) {
            $page  = $lastPage;
        }

        $dtoArray = [];
        if ($count > 0) {
            $response = $this->client->search($this->reasonForRejectionElasticSearchQueryBuilder->buildSearchQuery($searchTerm, $vehicleClassCode, $audience, $page));
            $dtoArray = $this->mapToDtoArray($response, $vehicleClassCode);
        }

        return new ReasonForRejectionResponse($dtoArray, $count, SearchReasonForRejectionInterface::ITEMS_PER_PAGE, $page);
    }

    private function mapToDtoArray(array $response, string $vehicleClassCode): array
    {
        $rfrs =  $response["hits"]["hits"];

        $dtos = [];
        foreach ($rfrs as $rfr) {
            $source = $rfr["_source"];
            $source["vehicleClassCode"] = $vehicleClassCode;

            $dtos[] = ReasonForRejectionDtoMapper::mapSingle($source);
        }

        return $dtos;
    }
}
