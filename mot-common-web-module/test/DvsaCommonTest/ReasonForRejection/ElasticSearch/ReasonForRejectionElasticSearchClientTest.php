<?php

namespace DvsaCommonTest\ReasonForRejection\ElasticSearch;

use DvsaCommon\Enum\RfrDeficiencyCategoryCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\ReasonForRejection\ElasticSearch\ReasonForRejectionElasticSearchClient;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionResponseInterface;
use DvsaCommonTest\TestUtils\XMock;
use Elasticsearch\Client;

class ReasonForRejectionElasticSearchClientTest extends \PHPUnit_Framework_TestCase
{
    const INDEX_NAME = 'mot_search';
    const DOCUMENT_TYPE = 'doc_type';

    public function test_search_returnsResponse()
    {
        /** @var Client | \PHPUnit_Framework_MockObject_MockObject $client */
        $client = XMock::of(Client::class);
        $client->method("search")->willReturn($this->createClientResponse());
        $client->method("count")->willReturn(["count" => 1]);

        $rfrClient = new ReasonForRejectionElasticSearchClient($client, self::INDEX_NAME, self::DOCUMENT_TYPE);
        $response = $rfrClient->search("wheel", VehicleClassCode::CLASS_1, "t", 1);

        $this->assertInstanceOf(SearchReasonForRejectionResponseInterface::class, $response);
    }

    /**
     * @dataProvider inspectionManualReferenceAndRfrId
     */
    public function test_search_not_use_fuzzy_search($searchTerm)
    {
        /** @var Client | \PHPUnit_Framework_MockObject_MockObject $client */
        $client = XMock::of(Client::class);
        $client->expects($searchSpy = $this->any())->method("search")->willReturn($this->createClientResponse());
        $client->expects($countSpy = $this->any())->method("count")->willReturn(["count" => 1]);

        $rfrClient = new ReasonForRejectionElasticSearchClient($client, self::INDEX_NAME, self::DOCUMENT_TYPE);
        $rfrClient->search($searchTerm, VehicleClassCode::CLASS_1, "t", 1);

        $params = $searchSpy->getInvocations()[0]->parameters[0];
        $multiMatchQuery = $params["body"]["query"]["bool"]["should"][0]["multi_match"];

        $this->assertFalse(array_key_exists("fuzziness", $multiMatchQuery));
    }

    public function inspectionManualReferenceAndRfrId()
    {
        return [
            ["1"],
            ["1."],
            ["1.4"],
            ["1.4.A"],
            ["1.4.A brake"],
            ["83"],
            ["834"],
            ["8344"],
        ];
    }

    /**
     * @dataProvider searchTerm
     */
    public function test_search_use_fuzzy_search($searchTerm)
    {
        /** @var Client | \PHPUnit_Framework_MockObject_MockObject $client */
        $client = XMock::of(Client::class);
        $client->expects($searchSpy = $this->any())->method("search")->willReturn($this->createClientResponse());
        $client->expects($countSpy = $this->any())->method("count")->willReturn(["count" => 1]);

        $rfrClient = new ReasonForRejectionElasticSearchClient($client, self::INDEX_NAME, self::DOCUMENT_TYPE);
        $rfrClient->search($searchTerm, VehicleClassCode::CLASS_1, "t", 1);

        $params = $searchSpy->getInvocations()[0]->parameters[0];
        $multiMatchQuery = $params["body"]["query"]["bool"]["should"][0]["multi_match"];

        $this->assertTrue(array_key_exists("fuzziness", $multiMatchQuery));
    }

    public function searchTerm()
    {
        return [
            ["a2"],
            ["a2."],
            ["a2.2"],
            ["brake.a"],
            ["brake 2"],
            ["xxx yyy"],
        ];
    }

    private function createClientResponse()
    {
        return [
          "hits" => [
              "hits" => [
                  [
                    "_source" => [
                        "canBeDangerous" => true,
                        "manual" => "3",
                        "description" => "Engine mounting missing",
                        "endDate" => null,
                        "inspectionManualDescription" => "An engine mounting missing or significantly deteriorated resulting in excessive movement",
                        "isPrsFail" => true,
                        "vehicleClasses" => [],
                        "isAdvisory" => false,
                        "rfrId" => "913",
                        "sectionTestItemSelector" => null,
                        "sectionTestItemSelectorName" => "Body, Structure and General Items > Engine mountings",
                        "advisoryText" => "Engine mounting",
                        "inspectionManualReference" => "6.1.D.1a",
                        "testItemSelectorId" => "524",
                        "deficiencyCategoryCode" => RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE
                    ]
                ]
              ]
          ]
        ];
    }
}
