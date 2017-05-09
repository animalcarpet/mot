<?php

namespace DvsaElasticSearch\Model;

/**
 * Class ESDocType.
 *
 * I am the base class for all document containers. As a family we are
 * responsible for actins as mediators/adapters between PHP internal form,
 * JSON return form and ElasticSearch form.
 */
class ESDocType
{
    /**
     * @codeCoverageIgnore because this is a base class and cannot be tested
     *
     * CTOR: whatever you need...!
     */
    public function __construct()
    {
    }

    /**
     * @codeCoverageIgnore because this is a base class and cannot be tested
     *
     * Return the internal state for ES consumption
     *
     * @param $entity
     *
     * @return array
     */
    public function asEsData($entity)
    {
        return [];
    }

    /**
     * @codeCoverageIgnore because this is a base class and cannot be tested
     *
     * Return the internal state for JSON or other consumption.
     *
     * @param $results
     *
     * @return array
     */
    public function asJson($results)
    {
        return [];
    }
}
