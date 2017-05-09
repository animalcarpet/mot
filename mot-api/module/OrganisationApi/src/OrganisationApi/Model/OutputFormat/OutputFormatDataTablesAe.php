<?php

namespace OrganisationApi\Model\OutputFormat;

use DvsaCommonApi\Model\OutputFormat;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use OrganisationApi\Service\Mapper\AuthorisedExaminerListItemMapper;

/**
 * Class OutputFormatDataTablesAe.
 */
class OutputFormatDataTablesAe extends OutputFormat
{
    public function extractItems($items)
    {
        $mapper = new AuthorisedExaminerListItemMapper();

        return $mapper->manyToDto($items);
    }

    /**
     * Responsible for extracting the current item into the required format.
     *
     * @param array                              $results
     * @param mixed                              $key
     * @param AuthorisationForAuthorisedExaminer $item
     *
     * @return array|mixed
     */
    public function extractItem(&$results, $key, $item)
    {
    }
}
