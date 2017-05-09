<?php

namespace DvsaCommonApi\Model;

/**
 * Class OutputFormat.
 */
abstract class OutputFormat
{
    const SOURCE_TYPE_ES = 1;
    const SOURCE_TYPE_NATIVE = 2;
    const SOURCE_TYPE_ENTITY = 3;

    protected $sourceType;

    /**
     * Extract the required data from the passed items data array.
     *
     * @param $items
     *
     * @return array
     */
    public function extractItems($items)
    {
        $results = [];
        if (count($items)) {
            foreach ($items as $key => $item) {
                $this->extractItem($results, $key, $item);
            }
        }

        return $results;
    }

    /**
     * Responsible for extracting the current item into the required format
     * and adding to the passed results array.
     *
     * @param $results
     * @param $key
     * @param $item
     *
     * @return mixed
     */
    abstract public function extractItem(&$results, $key, $item);

    /**
     * @return int
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * Set type of date source.
     *
     * @param int $sourceType type ES (Elastic Search), NATIVE (db native query), ENTITY (db doctrine)
     *
     * @return $this
     */
    public function setSourceType($sourceType)
    {
        $this->sourceType = $sourceType;

        return $this;
    }
}
