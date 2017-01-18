<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service\Validator\Odometer;

use Api\Check\CheckMessage;
use Api\Check\CheckResult;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Dto\Common\OdometerReadingDto;
use DvsaEntities\Repository\ConfigurationRepository;

/**
 * Class OdometerReadingDeltaAnomalyChecker.
 */
class OdometerReadingDeltaAnomalyChecker
{
    const CURRENT_LOWER_THAN_PREVIOUS = 'This is lower than the last test';
    const VALUE_SIGNIFICANTLY_HIGHER = 'This is significantly higher than the last test';
    const CURRENT_EQ_PREVIOUS = 'This is the same as the last test';
    const CONFIG_PARAM_ODOMETER_DELTA_SIGNIFICANTLY_HIGH = 'odometerDeltaSignificantValue';

    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;

    /**
     * @param ConfigurationRepository $configurationRepository
     */
    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * @param $currentReading
     * @param $previousReading
     *
     * @return CheckResult
     */
    public function check(OdometerReadingDto $currentReading, OdometerReadingDto $previousReading)
    {
        $result = CheckResult::ok();

        if ($currentReading->getResultType() === OdometerReadingResultType::OK) {
            $delta = $this->calculateDeltaSinceLastReading($currentReading, $previousReading);
            if (!is_null($delta)) {
                if ($delta === 0) {
                    $result->add(CheckMessage::withWarn()->text(self::CURRENT_EQ_PREVIOUS));
                } elseif ($delta < 0) {
                    $result->add(CheckMessage::withWarn()->text(self::CURRENT_LOWER_THAN_PREVIOUS));
                } elseif ($this->isMuchHigherThanLastOne($delta)) {
                    $result->add(
                        CheckMessage::withWarn()->text(self::VALUE_SIGNIFICANTLY_HIGHER)
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Checks if the delta is much higher than a limit (configuration value).
     *
     * @param $delta
     *
     * @return bool
     */
    private function isMuchHigherThanLastOne($delta)
    {
        $limit = (int)$this->configurationRepository->getValue(self::CONFIG_PARAM_ODOMETER_DELTA_SIGNIFICANTLY_HIGH);

        return $delta >= $limit;
    }

    /**
     * @param OdometerReadingDto $currentReading
     * @param OdometerReadingDto $previousReading
     *
     * @return int|null
     */
    private function calculateDeltaSinceLastReading(OdometerReadingDto $currentReading, OdometerReadingDto $previousReading)
    {
        if ($currentReading->getValue() == 0 || $previousReading->getValue() == 0) {
            return;
        }

        if ($currentReading->getResultType() === OdometerReadingResultType::OK
            && $previousReading->getResultType() === OdometerReadingResultType::OK
        ) {
            return intval(($this->normaliseReadingToKilometres($currentReading)
                    - $this->normaliseReadingToKilometres($previousReading)) / 1.6);
        }

        return;
    }

    /**
     * @param OdometerReadingDto $reading
     *
     * @return int
     */
    private function normaliseReadingToKilometres(OdometerReadingDto $reading)
    {
        if ($reading->getUnit() === OdometerUnit::MILES) {
            return $reading->getValue() * 1.6;
        }

        return $reading->getValue();
    }
}
