<?php

namespace DvsaElasticSearch\Model;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Date\DateUtils;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaMotApi\Model\OutputFormat\OutputFormatDataTablesVehicle;

/**
 * Class ESDocVehicle.
 *
 * I manage the data for a Vehicle and can return it in various formats.
 */
class ESDocVehicle extends ESDocType
{
    /**
     * Return the internal state for ES consumption.
     *
     * @param \DvsaEntities\Entity\MotTest $entity
     *
     * @return array
     */
    public function asEsData($entity)
    {
        $updatedDate = $entity->getLastUpdatedOn() !== null ?
            DateUtils::toIsoString($entity->getLastUpdatedOn()) :
            null;

        return [
            'id' => $entity->getId(),
            'vin' => $entity->getVin(),
            'registration' => $entity->getRegistration(),
            'make' => $entity->getModel()->getMake()->getName(),
            'model' => $entity->getModelName(),
            'displayDate' => $updatedDate,
            'updatedDate_display' => $entity->getLastUpdatedOn() !== null ?
                    $entity->getLastUpdatedOn()->format('d M Y') :
                    null,
            'updatedDate_timestamp' => $entity->getLastUpdatedOn() !== null ?
                    strtotime($entity->getLastUpdatedOn()->format('d M Y h:i')) :
                    null,
        ];
    }

    /**
     * Return the internal state for JSON or other consumption.
     *
     * @param $results
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function asJson($results)
    {
        if ($results['format'] == SearchParamConst::FORMAT_DATA_TABLES) {
            return (new OutputFormatDataTablesVehicle())->extractItems($results['hits']['hits']);
        }

        throw new BadRequestException(
            'Unknown search format: '.$results['format'],
            BadRequestException::ERROR_CODE_INVALID_DATA
        );
    }
}
