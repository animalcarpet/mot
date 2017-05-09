<?php

namespace DvsaElasticSearch\Model;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommonApi\Model\OutputFormat;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaMotApi\Model\OutputFormat\OutputFormatDataCsvMotTestLog;
use DvsaMotApi\Model\OutputFormat\OutputFormatDataTablesMotTestLog;

/**
 * Class ESDocMotTestLog.
 *
 * I manage the data for an MOT Test Log and can return it in various formats.
 */
class ESDocMotTestLog extends ESDocType
{
    /**
     * Return the internal state for JSON or other consumption.
     *
     * @param SearchResultDto $results
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function asJson($results)
    {
        $format = $results->getSearched()->getFormat();

        $outputFormat = null;
        if ($format == SearchParamConst::FORMAT_DATA_CSV) {
            $outputFormat = new OutputFormatDataCsvMotTestLog();
        } elseif ($format == SearchParamConst::FORMAT_DATA_TABLES) {
            $outputFormat = new OutputFormatDataTablesMotTestLog();
        }

        if ($outputFormat instanceof OutputFormat) {
            $outputFormat->setSourceType(
                $results->isElasticSearch()
                ? OutputFormat::SOURCE_TYPE_ES
                : OutputFormat::SOURCE_TYPE_NATIVE
            );

            return $outputFormat->extractItems($results->getData());
        }

        throw new BadRequestException(
            'Unknown search format: '.$results->getSearched()->getFormat(),
            BadRequestException::ERROR_CODE_INVALID_DATA
        );
    }
}
