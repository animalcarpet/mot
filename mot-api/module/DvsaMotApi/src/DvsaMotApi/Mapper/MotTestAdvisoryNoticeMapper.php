<?php

/**
 * Mot Test Advisory Notice Mapper
 * Maps MOT Test data into the snap shot format for the advisory notice
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace DvsaMotApi\Mapper;

use DataCatalogApi\Service\DataCatalogService;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Mot Test Advisory Notice Mapper
 * Maps MOT Test data into the snap shot format for the advisory notice
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class MotTestAdvisoryNoticeMapper extends AbstractMotTestMapper
{
    /**
     * Holds the template map
     *
     * @var array
     */
    protected $mapTemplate
        = [
            'TestNumber' => '',
            'VRM' => '',
            'VIN' => '',
            'Make' => '',
            'Model' => '',
            'IssuersName' => '',
            'IssuedDate' => '',
            self::REP_VAR_TEST_STATION => '',
            self::REP_VAR_INSPECTION_AUTHORITY => '',
            'InspectionTelephone' => '',
            self::REP_VAR_ADVISORIES => '',
            'FailureInformation' => '',
            'Measurements' => '',
            self::REP_VAR_ODOMETER => '',
            'CountryOfRegistration' => '',
            'Colour' => '',
            'TestClass' => '',
            self::REP_VAR_FAILURES => '',
            self::REP_VAR_RFC => '',
            self::REP_VAR_RFC_COMMENT => '',
        ];


    public function __construct(DataCatalogService $dataCatalogService)
    {
        parent::__construct($dataCatalogService);
    }

    /**
     * Map the input data to the snapshot data format
     *
     * @return array
     */
    public function mapData()
    {
        $this->mapGenericMotTestData();

        $this->mapVehicleDetail();

        $this->mapFailures('FAIL');

        $testType = ArrayUtils::tryGet($this->data, 'testType');
        if ($testType instanceof MotTestTypeDto && MotTestType::isVeAdvisory($testType->getCode()) ) {
            $this->mapFailures('PRS');
        }

        $this->mapAdvisories();

        $this->mapReasonForCancel();

        return $this->getMappedData();
    }
}
