<?php

namespace DvsaCommonTest\ReasonForRejection\ElasticSearch;

use DvsaCommon\Dto\ReasonForRejection\ReasonForRejectionDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\ReasonForRejection\ElasticSearch\ReasonForRejectionResponse;

class ReasonForRejectionResponseTest extends \PHPUnit_Framework_TestCase
{
    public function test_throwsException_whenInjectIncorrectData()
    {
        $this->expectException(\InvalidArgumentException::class);

        new ReasonForRejectionResponse([new SiteDto()], 1, 2 , 1);
    }

    public function test_getData_returnsArrayOfDto()
    {
        $data = [new ReasonForRejectionDto()];

        $response = new ReasonForRejectionResponse($data, 0, 2, 1);

        $this->assertEquals($data, $response->getData());
    }
}
