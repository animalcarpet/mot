<?php

namespace SiteApi\Service\Validator;

use DvsaCommon\Date\Time;
use DvsaCommonApi\Error\Message as ErrorMessage;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\BadRequestExceptionWithMultipleErrors;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonApi\Service\Validator\AbstractValidator;

/**
 * Class SiteTestingDailyScheduleValidator.
 *
 * Validates the site weekly opening times schedule provided in data array format and throws errors for any invalid
 * scenarios encountered.
 */
class SiteTestingDailyScheduleValidator extends AbstractValidator
{
    private $fieldErrors;

    private $days = [
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday',
        7 => 'sunday',
    ];

    public function validateOpeningHours($data)
    {
        if (false === is_array($data) || false === array_key_exists('weeklySchedule', $data)) {
            throw new BadRequestException(
                'A valid site opening times schedule has not been provided',
                BadRequestException::ERROR_CODE_INVALID_DATA
            );
        }

        $data = $data['weeklySchedule'];

        $this->fieldErrors = [];

        $this->validateStructure($data);

        foreach ($data as $dailyScheduleData) {
            $weekday = $dailyScheduleData['weekday'];

            if (($weekday < 1) || ($weekday > 7)) {
                throw new BadRequestException(
                    'Weekday must be numeric from 1 to 7 ',
                    BadRequestException::ERROR_CODE_INVALID_DATA
                );
            }

            if (false === $dailyScheduleData['isClosed']) {
                $this->validateTime($dailyScheduleData['openTime'], 'openTime', $weekday);
                $this->validateTime($dailyScheduleData['closeTime'], 'closeTime', $weekday);
            }
        }

        if ($this->fieldErrors) {
            throw new BadRequestExceptionWithMultipleErrors([], $this->fieldErrors);
        }
    }

    private function validateTime($time, $timeField, $weekday)
    {
        if (!empty($time) && !Time::isValidIso8601($time)) {
            $this->fieldErrors[] = new ErrorMessage(
                'Invalid time format provided',
                BadRequestException::ERROR_CODE_INVALID_DATA,
                [$this->days[$weekday].ucfirst($timeField) => null]
            );
        }

        if (Time::isValidIso8601($time)) {
            $time = Time::fromIso8601($time);

            if ($time->getMinute() !== '30') {
                if ($time->getMinute() !== '00') {
                    $this->fieldErrors[] = new ErrorMessage(
                        'Invalid time format provided: minutes must be in increments of 30',
                        BadRequestException::ERROR_CODE_INVALID_DATA,
                        [$this->days[$weekday].ucfirst($timeField) => null]
                    );
                }
            }
        }
    }

    private function validateStructure($data)
    {
        foreach ($data as $dailyScheduleData) {
            if (empty($dailyScheduleData['weekday'])) {
                throw new RequiredFieldException(['weekday']);
            }

            if (false === isset($dailyScheduleData['isClosed'])) {
                throw new RequiredFieldException(['isClosed']);
            }

            $weekday = $dailyScheduleData['weekday'];

            if (true === $dailyScheduleData['isClosed']) {
                if ($dailyScheduleData['openTime']) {
                    $this->fieldErrors[] = new ErrorMessage(
                        'Opening time can not be provided when the site is indicated as closed.',
                        BadRequestException::ERROR_CODE_INVALID_DATA,
                        [$this->days[$weekday].'OpenTime' => null]
                    );
                }

                if ($dailyScheduleData['closeTime']) {
                    $this->fieldErrors[] = new ErrorMessage(
                        'Closing time can not be provided when the site is indicated as closed.',
                        BadRequestException::ERROR_CODE_INVALID_DATA,
                        [$this->days[$weekday].'CloseTime' => null]
                    );
                }
            } elseif (false === $dailyScheduleData['isClosed']) {
                if (empty($dailyScheduleData['openTime'])) {
                    $this->fieldErrors[] = new ErrorMessage(
                        'Opening time must be provided when the site is indicated as open.',
                        BadRequestException::ERROR_CODE_INVALID_DATA,
                        [$this->days[$weekday].'OpenTime' => null]
                    );
                }

                if (empty($dailyScheduleData['closeTime'])) {
                    $this->fieldErrors[] = new ErrorMessage(
                        'Closing time must be provided when the site is indicated as open.',
                        BadRequestException::ERROR_CODE_INVALID_DATA,
                        [$this->days[$weekday].'CloseTime' => null]
                    );
                }
            } else {
                $this->fieldErrors[] = new ErrorMessage(
                    'invalid isClosed format provided',
                    BadRequestException::ERROR_CODE_INVALID_DATA,
                    [$this->days[$weekday].'IsClosed' => null]
                );
            }
        }
    }
}
