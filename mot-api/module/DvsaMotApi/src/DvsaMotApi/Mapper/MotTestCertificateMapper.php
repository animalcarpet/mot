<?php

/**
 * Mot Test Certificate Mapper
 * Maps MOT Test data into the snap shot format for the Certificate
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace DvsaMotApi\Mapper;

use DataCatalogApi\Service\DataCatalogService;
use DvsaCommon\Date\DateUtils;
use DvsaMotApi\Service\MotTestDate;
use DvsaCommon\Utility\ArrayUtils;
use Exception;

/**
 * Mot Test Certificate Mapper
 * Maps MOT Test data into the snap shot format for the Certificate
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class MotTestCertificateMapper extends AbstractMotTestMapper
{
    const REP_VAR_EXPIRY_DATE = 'ExpiryDate';
    const REP_VAR_ADDITIONAL_INFO = 'AdditionalInformation';
    const REP_VAR_ODOMETER_HISTORY = 'OdometerHistory';


    public function __construct(DataCatalogService $dataCatalogService)
    {
        parent::__construct($dataCatalogService);
    }

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
            'Colour' => '',
            'CountryOfRegistration' => '',
            'IssuersName' => '',
            'TestClass' => '',
            self::REP_VAR_EXPIRY_DATE => '',
            'IssuedDate' => '',
            self::REP_VAR_TEST_STATION => '',
            self::REP_VAR_INSPECTION_AUTHORITY => '',
            'InspectionTelephone' => '',
            self::REP_VAR_ADVISORIES => '',
            self::REP_VAR_ODOMETER => '',
            self::REP_VAR_ODOMETER_HISTORY => '',
            self::REP_VAR_ADDITIONAL_INFO => '',
        ];

    /**
     * Map the input data to the certificate snapshot data format
     *
     * @return array
     */
    public function mapData()
    {
        $this->mapGenericMotTestData();

        $this->mapVehicleDetail();

        $this->mapOdometers();
        $this->mapAdvisories();

        $this->mapExpiryDate();

        $this->mapAdditionalInfo();

        return $this->getMappedData();
    }

    /**
     * Map Odometers
     */
    private function mapOdometers()
    {
        if (empty($this->data)) {
            return;
        }

        $readings = ArrayUtils::tryGet($this->data, 'OdometerReadings', []);
        if (empty($readings)) {
            return;
        }

        /** @var \DvsaCommon\Dto\Common\OdometerReadingDTO $value */
        $result = [];
        foreach ($readings as $value) {
            $result[] = $this->formatDate($value->getIssuedDate(), 'j n Y') . ': ' . $this->formatOdometer($value);
        }

        $this->setValue(self::REP_VAR_ODOMETER_HISTORY, join(PHP_EOL, $result));
    }

    private function mapExpiryDate()
    {
        if (empty($this->data)) {
            return;
        } else if(empty($this->data['expiryDate'])) {
            throw new Exception('Expiry date for passed test is mandatory, test number: ' . $this->data['motTestNumber']);
        }

        $date = DateUtils::toDateTime($this->data['expiryDate'], false);

        $expiryYear = (int)$date->format('y');

        $expiryDate = $this->formatDate($date) .
            ($this->isDualLanguage() ? PHP_EOL : ' ') .
            '(' . $this->convertYearToWords($expiryYear) . ')';

        $this->setValue(self::REP_VAR_EXPIRY_DATE, $expiryDate);
    }

    private function mapAdditionalInfo()
    {
        $expiryDate = ArrayUtils::tryGet($this->data, 'expiryDate', false);
        if (!$expiryDate) {
            return;
        }

        $this->setValue(
            self::REP_VAR_ADDITIONAL_INFO,
            'To preserve the anniversary of the expiry date, the earliest you can present your vehicle for test is '
            . $this->formatDate(MotTestDate::preservationDate(new \DateTime($expiryDate))) . '.'
        );
    }
}
