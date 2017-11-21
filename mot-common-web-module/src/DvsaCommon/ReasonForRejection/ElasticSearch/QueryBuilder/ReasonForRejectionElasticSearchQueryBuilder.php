<?php
namespace DvsaCommon\ReasonForRejection\ElasticSearch\QueryBuilder;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\RfrCurrentDateFaker;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionInterface;
use Zend\Validator\Regex;

class ReasonForRejectionElasticSearchQueryBuilder
{
    private $index;
    private $documentType;
    private $rfrCurrentDateFaker;

    public function __construct(string $index, string $documentType, RfrCurrentDateFaker $rfrCurrentDateFaker)
    {
        $this->index = $index;
        $this->documentType = $documentType;
        $this->rfrCurrentDateFaker = $rfrCurrentDateFaker;
    }

    public function buildCountQuery(string $searchTerm, string $vehicleClassCode, string $audience): array
    {
        return $this->createParams($searchTerm, $vehicleClassCode, $audience);
    }

    public function buildSearchQuery(string $searchTerm, string $vehicleClassCode, string $audience, int $page): array
    {
        $params = $this->createParams($searchTerm, $vehicleClassCode, $audience);
        $params["body"]["from"] = SearchReasonForRejectionInterface::ITEMS_PER_PAGE * ($page - 1);
        $params["body"]["size"] = SearchReasonForRejectionInterface::ITEMS_PER_PAGE;

        return $params;
    }

    private function createParams(string $searchTerm, string $vehicleClassCode, string $audience): array
    {
        return [
            "index" => $this->index,
            "type" => $this->documentType,
            "body" => [
                "query" => $this->createQuery($searchTerm, $vehicleClassCode, $audience)
            ]
        ];
    }

    private function createQuery(string $searchTerm, string $vehicleClassCode, string $audience): array
    {
        return [
            "bool" => [
                "minimum_should_match" => 1,
                "should" => [
                    $this->createMultiMatchQuery($searchTerm),
                    $this->createSimpleQueryStringQuery($searchTerm),
                ],
                "filter" => [
                    "bool" => [
                        "must" => [
                            $this->createAudienceFilter($audience),
                            $this->createDateFilter(),
                            $this->createVehicleClassesFilter($vehicleClassCode)
                        ]
                    ]
                ]
            ]
        ];
    }

    private function createMultiMatchQuery(string $searchTerm): array
    {
        $query = [
            "multi_match" => [
                "query" => $searchTerm,
                "fields" => ["description", "testItemSelectorName"],
                "operator" => "AND"
            ]
        ];

        if ((new Regex('/^\d+(\.\d+)?/'))->isValid($searchTerm) === false) {
            $query["multi_match"]["fuzziness"] = "AUTO";
        }

        return $query;
    }

    private function createSimpleQueryStringQuery(string $searchTerm): array
    {
        return [
            "bool" => [
                "should" => [
                    ["match" => ["rfrId" => $searchTerm]],
                    ["match" => ["inspectionManualReference" => ["query" => $searchTerm, "operator" => "AND"]]],
                    [
                        "simple_query_string" => [
                            "query" => $searchTerm . "*",
                            "fields" => ["inspectionManualReference"],
                            "analyze_wildcard" => true,
                            "default_operator" => "AND"
                        ]
                    ]
                ]
            ]
        ];
    }

    private function createAudienceFilter(string $audience): array
    {
        return [
            "bool" => [
                "should" => [
                    ["term" => ["audience" => "b"]],
                    ["term" => ["audience" => $audience]]
                ]
            ]
        ];
    }

    private function createDateFilter(): array
    {
        $today = $this->rfrCurrentDateFaker->getCurrentDateTime()->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY);

        return [
            "bool" => [
                "should" => [
                    ["range" =>["endDate" => ["gt" => $today, "format" => "yyyy-MM-dd"]]],
                    ["range" =>["startDate" => ["lte" => $today, "format" => "yyyy-MM-dd"]]],
                    [
                        "bool" => [
                            "must_not" => ["exists" => ["field" => "endDate"]]
                        ]
                    ],
                    [
                        "bool" => [
                            "must_not" => ["exists" => ["field" => "startDate"]]
                        ]
                    ]
                ]
            ]
        ];
    }

    private function createVehicleClassesFilter(string $vehicleClassCode): array
    {
        return ["term" => ["vehicleClasses" => $vehicleClassCode]];
    }
}
