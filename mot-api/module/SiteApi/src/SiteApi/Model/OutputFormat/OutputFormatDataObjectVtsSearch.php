<?php

namespace SiteApi\Model\OutputFormat;

use DvsaCommonApi\Model\OutputFormat;
use SiteApi\Service\ExtractSiteTrait;
use SiteApi\Service\Mapper\SiteBusinessRoleMapMapper;

/**
 * Class OutputFormatDataObjectVtsSearch.
 */
class OutputFormatDataObjectVtsSearch extends OutputFormat
{
    use ExtractSiteTrait;

    public function __construct($objectHydrator, SiteBusinessRoleMapMapper $positionMapper)
    {
        $this->objectHydrator = $objectHydrator;
        $this->positionMapper = $positionMapper;
    }

    /**
     * Responsible for extracting the current item into the required format.
     *
     * @param $results
     * @param $key
     * @param $item
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function extractItem(&$results, $key, $item)
    {
        $key = 123; // phpmd fudge
        $results[] = $this->extractVehicleTestingStation($item->getVehicleTestingStation());
    }
}
