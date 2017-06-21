<?php

namespace DvsaMotApi\Mapper;

use DataCatalogApi\Service\DataCatalogService;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Common\ColourDto;
use DvsaCommon\Dto\Common\OdometerReadingDto;
use DvsaCommon\Dto\Common\ReasonForCancelDto;
use DvsaCommon\Dto\Common\ReasonForRefusalDto;
use DvsaCommon\Dto\Vehicle\CountryDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Utility\AddressUtils;
use DvsaCommon\Utility\ArrayUtils;
use DvsaDocument\Mapper\AbstractMapper;
use DvsaEntities\Entity\Person;

/**
 * Abstract Mot Test Mapper.
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractMotTestMapper extends AbstractMapper
{
    const GETTER_METHOD_VIN = 'getReasonsForEmptyVin';
    const GETTER_METHOD_VRM = 'getReasonsForEmptyVRM';

    const REP_VAR_FAILURES = 'FailureInformation';
    const REP_VAR_ADVISORIES = 'AdvisoryInformation';
    const REP_VAR_TEST_STATION = 'TestStation';
    const REP_VAR_INSPECTION_AUTHORITY = 'InspectionAuthority';
    const REP_VAR_RFC = 'ReasonForCancel';
    const REP_VAR_RFC_COMMENT = 'ReasonForCancelComment';
    const REP_VAR_ODOMETER = 'Odometer';

    const MOT_TEST_ABORTED = 'ABORTED';
    const MOT_TEST_ABANDONED = 'ABANDONED';

    /** String for an unreadable odometer entry */
    const TEXT_NOT_READABLE = 'Unreadable';
    const TEXT_NOT_READABLE_CY = 'Dim yn ddarllenadwy';

    /** String for when no odometer was present */
    const TEXT_NO_ODOMETER = 'No odometer';
    const TEXT_NO_ODOMETER_CY = 'Dim odomedr';

    /** String for when no reading was recorded, e.g. an aborted or abandoned test */
    const TEXT_NOT_RECORDED = 'Not recorded';
    const TEXT_NOT_RECORDED_CY = 'Heb gofnodi';

    /** @var array Holds the template map */
    protected $mapTemplate = [];

    /** Does this certificate need to be dual language? */
    protected $isDualLanguage = false;

    /** @var bool Is this certificate for a retest? */
    protected $isNormalTest = false;

    /** @var int $rfrNr */
    protected $rfrNr = 1;

    /** @var DataCatalogService $dataCatalogService */
    protected $dataCatalogService;

    /**
     * AbstractMotTestMapper constructor.
     *
     * @param DataCatalogService $dataCatalogService
     */
    protected function __construct(DataCatalogService $dataCatalogService)
    {
        $this->dataCatalogService = $dataCatalogService;
    }

    /**
     * Get a key's value.
     *
     * @param string $key
     *
     * @return string value
     */
    protected function getValue($key)
    {
        $mappedData = $this->getMappedData();
        if (isset($mappedData[$key])) {
            return $mappedData[$key];
        }

        return null;
    }

    /**
     * Map generic data.
     */
    protected function mapGenericMotTestData()
    {
        $this->setValue('TestNumber', ArrayUtils::tryGet($this->getData(), 'motTestNumber'));

        $this->mapAddress();
        $this->mapOdometer();

        $this->setValue('IssuedDate', $this->formatDate($this->getData()['issuedDate'], 'j M Y'));
        $this->setValue('IssuersName', Person::getShortName($this->getData()['tester']));
    }

    protected function mapVehicleDetail()
    {
        /** @var CountryDto $draftCountryOfRegistration */
        $draftCountryOfRegistration = $this->getData()['countryOfRegistration'];

        /** @var VehicleClassDto $draftVehicleClass */
        $draftVehicleClass = !empty($this->getData()['vehicleClass']) ? $this->getData()['vehicleClass']->getCode() : null;
        $draftVin = ArrayUtils::tryGet($this->getData(), 'vin');
        $draftVrm = ArrayUtils::tryGet($this->getData(), 'registration');
        $draftReasonForEmptyVin = ArrayUtils::tryGet($this->getData(), 'emptyVinReason');
        $draftReasonForEmptyVrm = ArrayUtils::tryGet($this->getData(), 'emptyVrmReason');
        $draftMake = $this->getData()['make'];
        $draftModel = $this->getData()['model'];

        $this->setValue('VRM', strtoupper($this->mapVrm($draftVrm, $draftReasonForEmptyVrm)));
        $this->setValue('VIN', strtoupper($this->mapVin($draftVin, $draftReasonForEmptyVin)));
        $this->setValue('Make', $draftMake);
        $this->setValue('Model', $draftModel);
        $this->setValue('CountryOfRegistration', $draftCountryOfRegistration->getName(), 'CountryRegistration');
        $this->setValue('TestClass', $draftVehicleClass);

        $this->mapColour();
    }

    /**
     * @param null|string $draftVin
     * @param null|string $draftReasonForEmptyVin
     *
     * @return string
     */
    private function mapVin($draftVin = null, $draftReasonForEmptyVin = null)
    {
        if (is_null($draftVin)) {
            return $this->getEmptyVinReasonName($draftReasonForEmptyVin);
        }

        return $draftVin;
    }

    /**
     * @param null|string $draftVrm
     * @param null|string $draftReasonForEmptyVrm
     *
     * @return string
     */
    private function mapVrm($draftVrm = null, $draftReasonForEmptyVrm = null)
    {
        if (is_null($draftVrm)) {
            return $this->getEmptyVrmReasonName($draftReasonForEmptyVrm);
        }

        return $draftVrm;
    }

    /**
     * @param string $draftReasonForEmptyVin
     *
     * @return string
     */
    private function getEmptyVinReasonName($draftReasonForEmptyVin)
    {
        return $this->getEmptyVinOrVrmReasonNameByCode($draftReasonForEmptyVin, self::GETTER_METHOD_VIN);
    }

    /**
     * @param string $draftReasonForEmptyVrm
     *
     * @return string
     */
    private function getEmptyVrmReasonName($draftReasonForEmptyVrm)
    {
        return $this->getEmptyVinOrVrmReasonNameByCode($draftReasonForEmptyVrm, self::GETTER_METHOD_VRM);
    }

    /**
     * @param string $draftReasonForEmptyVinOrVrmCode
     * @param string $VinOrVrm
     *
     * @return string
     */
    private function getEmptyVinOrVrmReasonNameByCode($draftReasonForEmptyVinOrVrmCode, $VinOrVrm)
    {
        $reasonData = ArrayUtils::firstOrNull(
            $this->dataCatalogService->$VinOrVrm(),
            function ($reason) use ($draftReasonForEmptyVinOrVrmCode) {
                return $reason['code'] === $draftReasonForEmptyVinOrVrmCode;
            }
        );

        return $reasonData['name'];
    }

    protected function mapAdvisories()
    {
        $this->setValue(self::REP_VAR_ADVISORIES, $this->getRfr('ADVISORY'));
    }

    protected function mapFailures($key)
    {
        $failures = $this->getRfr($key);
        if ($failures === null) {
            return;
        }

        $result = $this->getValue(self::REP_VAR_FAILURES);

        $this->setValue(
            self::REP_VAR_FAILURES,
            $result
            .(!empty($result) ? PHP_EOL.($this->isDualLanguage() ? PHP_EOL : '') : '')
            .$failures
        );
    }

    private function mapColour()
    {
        $primaryColour = ArrayUtils::tryGet($this->getData(), 'primaryColour');
        $secondaryColour = ArrayUtils::tryGet($this->getData(), 'secondaryColour');

        $colours = [$primaryColour->getName()];

        if (
            $secondaryColour instanceof ColourDto &&
            ColourCode::NOT_STATED != $secondaryColour->getCode()
        ) {
            $colours[] = $secondaryColour->getName();
        }

        $colour = implode(' and ', $colours);

        $this->setValue('Colour', $colour);
    }

    /**
     * Map address data.
     */
    protected function mapAddress()
    {
        //  --  for Demo test - write 'demo test' text instead of address   --
        if (ArrayUtils::tryGet($this->data, 'testType') == MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING) {
            $this->setValue(self::REP_VAR_TEST_STATION, 'Demo Test');
            $this->setValue(self::REP_VAR_INSPECTION_AUTHORITY, 'Demo Test');

            return;
        }

        $siteData = ArrayUtils::tryGet($this->data, 'vehicleTestingStation');
        if (empty($siteData)) {
            return;
        }

        // Set VTS
        $this->setValue('TestStation', (!empty($siteData['siteNumber']) ? $siteData['siteNumber'] : 'N/A'));

        // Set address
        $address = ArrayUtils::tryGet($siteData, 'address');
        if (!is_array($address)) {
            $additionalData = $this->getDataSource('Additional');
            $address = ArrayUtils::tryGet($additionalData, 'TestStationAddress', false);

            if (!$address && $val = ArrayUtils::tryGet($this->data, 'TestStationAddress', false)) {
                $address = $val;
            }
        }

        $primaryTelephone = isset($this->data['vehicleTestingStation']['primaryTelephone']) ?
            $this->data['vehicleTestingStation']['primaryTelephone']
            : '';

        $result = $this->data['vehicleTestingStation']['name']
            .PHP_EOL
            .AddressUtils::stringify($address, PHP_EOL)
            ."\t\t".$primaryTelephone
            .PHP_EOL;

        $this->setValue('InspectionAuthority', $result);
    }

    /**
     * Map the failures.
     *
     * @param string $type
     *
     * @return null|string
     */
    protected function getRfr($type)
    {
        if (empty($this->data)) {
            return null;
        }

        $rfr = ArrayUtils::tryGet($this->data, 'reasonsForRejection');

        $items = ArrayUtils::tryGet($rfr, $type, false);
        if (!$items) {
            return null;
        }

        $result = [];

        $lastElement = end($items);
        foreach ($items as $item) {
            $result[] = $this->formatRejection($item, $this->rfrNr);

            if ($this->isDualLanguage() && $item['name'] !== 'Manual Advisory') {
                $result[] = $this->formatRejection($item, $this->rfrNr, true);

                if ($item != $lastElement) {
                    $result[] = '';      //  used for splitting rows
                }
            }

            ++$this->rfrNr;
        }

        return implode(PHP_EOL, $result);
    }

    /**
     * Format advisory.
     *
     * @param array $rejection
     * @param int   $i
     * @param bool  $preferCy
     *
     * @return string
     */
    protected function formatRejection($rejection, $i, $preferCy = false)
    {
        $comment = trim(ArrayUtils::tryGet($rejection, 'comment', false));
        $manualRef = ArrayUtils::tryGet($rejection, 'inspectionManualReference');

        $parts = [
            $this->getBestFit($rejection, 'testItemSelectorDescription', $preferCy),
            $this->getBestFit($rejection, 'failureText', $preferCy),
            ArrayUtils::tryGet($rejection, 'locationLateral'),
            ArrayUtils::tryGet($rejection, 'locationLongitudinal'),
            ArrayUtils::tryGet($rejection, 'locationVertical'),
            ($comment ? '('.$comment.')' : null),
            ($manualRef ? '['.$manualRef.']' : null),
        ];

        if (!empty($rejection['failureDangerous'])) {
            // @NOTE Welsh translation *not* verified
            $parts[] = $preferCy ? '* PERYGLUS *' : '* DANGEROUS *';
        }

        return str_pad((string) $i, 3, '0', STR_PAD_LEFT)
        .' '.implode(' ', array_filter($parts));
    }

    /**
     * Get the best available translation for a key.
     *
     * @param array  $rejection
     * @param string $key
     * @param bool   $preferCy
     *
     * @return string
     */
    protected function getBestFit($rejection, $key, $preferCy)
    {
        $textCy = trim(ArrayUtils::tryGet($rejection, $key.'Cy'));
        if ($preferCy && $textCy !== '') {
            return $textCy;
        }

        return ArrayUtils::tryGet($rejection, $key);
    }

    /**
     * Format the odometer reading.
     *
     * @param OdometerReadingDto $odometerReading
     *
     * @return string
     */
    protected function formatOdometer($value, $unit, $resultType)
    {
        switch ($resultType) {
            case OdometerReadingResultType::OK:
                $result = sprintf('%d %s', $value, $unit);
                break;
            case OdometerReadingResultType::NOT_READABLE:
                $result = sprintf('%s%s', self::TEXT_NOT_READABLE,
                    $this->isDualLanguage() ? '/'.self::TEXT_NOT_READABLE_CY : '');
                break;
            case OdometerReadingResultType::NO_ODOMETER:
                $result = sprintf('%s%s', self::TEXT_NO_ODOMETER,
                    $this->isDualLanguage() ? '/'.self::TEXT_NO_ODOMETER_CY : '');
                break;
            default:
                $result = sprintf('%s%s', self::TEXT_NOT_RECORDED,
                    $this->isDualLanguage() ? '/'.self::TEXT_NOT_RECORDED_CY : '');
        }

        return $result;
    }

    /**
     * Map odometer.
     *
     * @param array $data
     */
    protected function mapOdometer()
    {
        $value = ArrayUtils::tryGet($this->getData(), 'odometerValue');
        $unit = ArrayUtils::tryGet($this->getData(), 'odometerUnit');
        $resultType = ArrayUtils::tryGet($this->getData(), 'odometerResultType');

        $this->setValue(self::REP_VAR_ODOMETER, $this->formatOdometer($value, $unit, $resultType));
    }

    /**
     * Convert year to words.
     *
     * @param int $year
     *
     * @return string
     */
    protected function convertYearToWords($year)
    {
        $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        $result = $formatter->format($year);

        if ($this->isDualLanguage()) {
            $formatter = new \NumberFormatter('cy_GB', \NumberFormatter::SPELLOUT);

            $result .= ' / '.$formatter->format($year);
        }

        return strtoupper($result);
    }

    public function setDualLanguage($dualLanguage)
    {
        $this->isDualLanguage = $dualLanguage;
    }

    public function isDualLanguage()
    {
        return $this->isDualLanguage;
    }

    public function setNormalTest($normalTest)
    {
        $this->isNormalTest = $normalTest;
    }

    public function isNormalTest()
    {
        return $this->isNormalTest;
    }

    protected function mapReasonForCancel()
    {
        /** @var ReasonForRefusalDto|ReasonForCancelDto $rfc */
        $rfc = ArrayUtils::tryGet($this->data, 'reasonForCancel');

        if (!($rfc instanceof ReasonForRefusalDto) && !($rfc instanceof ReasonForCancelDto)) {
            return;
        }

        $reason = $rfc->getReason();
        if ($this->isDualLanguage()) {
            $reason .= ' / '.$rfc->getReasonCy();
        }

        $this->setValue(self::REP_VAR_RFC, $reason);

        $rftc = ArrayUtils::tryGet($this->data, 'reasonForTerminationComment', false);
        if ($rftc) {
            $this->setValue(self::REP_VAR_RFC_COMMENT, $rftc);
        }
    }

    protected function mapNoticeInformationOnRejections()
    {
        $rejections = $this->getValue(self::REP_VAR_FAILURES);
        $status = ArrayUtils::tryGet($this->data, 'status');

        if ($this->isNormalTest() && $rejections !== null
            && $status !== self::MOT_TEST_ABORTED && $status !== self::MOT_TEST_ABANDONED
        ) {
            $rejections .= PHP_EOL.PHP_EOL.
                'For retest procedures and details of free retest items please refer to the MOT fees and appeals '.
                'poster at the testing station or alternatively the details can be found at '.
                'www.gov.uk/getting-an-mot/retests'.PHP_EOL;

            if ($this->isDualLanguage()) {
                $rejections .= PHP_EOL.
                    'Ar gyfer rheolau ailbrofi ac manylion o eitemau ailbrofi am ddim, gwelwch y poster ffioedd ac '.
                    'apelau MOT yn y gorsaf brofi neu darganfyddwch y manylion ar www.gov.uk/getting-an-mot/retests'.
                    PHP_EOL;
            }

            $this->setValue(self::REP_VAR_FAILURES, $rejections);
        }
    }

    /**
     * Format date in PDF report and add welsh translation of month.
     *
     * @param int|string|\DateTime $date
     * @param array|string         $format
     *
     * @return string
     *
     * @throws \DvsaCommon\Date\Exception\IncorrectDateFormatException
     * @throws \DvsaCommon\Date\Exception\NonexistentDateTimeException
     */
    protected function formatDate($date, $format = self::FORMAT_DATE)
    {
        if (is_numeric($date)) {
            $date = new \DateTime('@'.(string) $date);
        } elseif (is_string($date)) {
            $date = DateUtils::toDateTime($date, false);
        }

        if (!($date instanceof \DateTime)) {
            return '';
        }

        if ($this->isDualLanguage()) {
            $formatCharsMap = ['M' => 'MMM', 'F' => 'MMMM'];

            $isTextMonth = preg_match_all('/[MF]{1}/', $format, $parts);

            if ($isTextMonth) {
                foreach ($parts[0] as $formatChar) {
                    $monthInWls = datefmt_format_object($date, $formatCharsMap[$formatChar], 'cy_GB');

                    $format = str_replace(
                        $formatChar,
                        $formatChar.'/'.preg_replace('/./', '\\\\\0', $monthInWls),
                        $format
                    );
                }
            }
        }

        return $date->format($format);
    }
}
