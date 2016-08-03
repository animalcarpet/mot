<?php

/**
 * Mot Test Failure Mapper
 * Maps MOT Test data into the snap shot format for the failure document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace DvsaMotApi\Mapper;

use DataCatalogApi\Service\DataCatalogService;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Mot Test Failure Mapper
 * Maps MOT Test data into the snap shot format for the failure document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class MotTestFailureMapper extends AbstractMotTestMapper
{
    /**
     * Holds the template map
     *
     * @var array
     */
    protected $mapTemplate
        = [
            'TestNumber' => '',
            'CountryOfRegistration' => '',
            'Make' => '',
            'Model' => '',
            'Colour' => '',
            self::REP_VAR_ODOMETER => '',
            'VRM' => '',
            'TestClass' => '',
            'VIN' => '',
            'IssuersName' => '',
            self::REP_VAR_INSPECTION_AUTHORITY => '',
            'InspectionTelephone' => '',
            self::REP_VAR_TEST_STATION => '',
            'IssuedDate' => '',
            self::REP_VAR_ADVISORIES => '',
            'FirstUseDate' => '',
            self::REP_VAR_FAILURES => '',
            self::REP_VAR_RFC => '',
            self::REP_VAR_RFC_COMMENT => '',
        ];

    public function __construct(DataCatalogService $dataCatalogService)
    {
        parent::__construct($dataCatalogService);
    }

    public function mapData()
    {
        $this->mapGenericMotTestData();

        $this->mapVehicleDetail();

        /** @var \DvsaCommon\Dto\Vehicle\VehicleDto $vehicle */
        $vehicle = ArrayUtils::tryGet($this->data, 'vehicle');
        $this->setValue('FirstUseDate', $this->formatDate($vehicle->getFirstUsedDate()));

        $this->mapFailures('FAIL');
        $this->mapFailures('PRS');
        $this->mapNoticeInformationOnRejections();
        $this->mapAdvisories();

        $this->mapReasonForCancel();

        return $this->getMappedData();
    }
}
