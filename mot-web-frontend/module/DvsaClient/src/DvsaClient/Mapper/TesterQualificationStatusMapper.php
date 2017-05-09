<?php

namespace DvsaClient\Mapper;

use DvsaCommon\UrlBuilder\PersonUrlBuilder;

/**
 * Class TesterQualificationStatusMapper.
 */
class TesterQualificationStatusMapper extends Mapper
{
    public function getTesterQualificationStatus($testerId)
    {
        $url = PersonUrlBuilder::motTesting($testerId)->toString();

        return $this->client->get($url)['data'];
    }
}
