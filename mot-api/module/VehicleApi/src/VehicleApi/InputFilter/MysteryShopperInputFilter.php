<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace VehicleApi\InputFilter;

use VehicleApi\Validator\CampaignDateValidator;
use Zend\I18n\Validator\Alnum;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Date;
use Zend\Validator\NotEmpty;

/**
 * Class MysteryShopperInputFilter.
 */
class MysteryShopperInputFilter extends InputFilter
{
    const FIELD_VEHICLE_ID = 'vehicle_id';
    const FIELD_SITE_NUMBER = 'site_number';
    const FIELD_START_DATE = 'start_date';
    const FIELD_END_DATE = 'end_date';
    const FIELD_CAMPAIGN_DATES = 'campaign_dates';
    const FIELD_BOOKED_DATE_RANGES = 'booked_date_ranges';
    const FIELD_TEST_DATE = 'test_date';
    const FIELD_EXPIRY_DATE = 'expiry_date';

    /**
     * To indicate if we are validating during an update (edit) request or post (creation) will requires all the
     * expected fields, to allow supplying only those field which need to be updated.
     *
     * @var bool
     */
    private $isRequired = true;

    /**
     * To allow supplying only those fields which need to be updated.
     */
    public function setToEditMode()
    {
        $this->isRequired = false;
    }

    /**
     * To initiate all the validators.
     */
    public function init()
    {
        $this->initValidatorsForVehicleId($this->isRequired);
        $this->initValidatorsForSiteNumber($this->isRequired);
        $this->initValidatorsForDates(self::FIELD_START_DATE, $this->isRequired);
        $this->initValidatorsForDates(self::FIELD_END_DATE, $this->isRequired);
        $this->initValidatorsForDates(self::FIELD_TEST_DATE, $this->isRequired);
        $this->initValidatorsForDates(self::FIELD_EXPIRY_DATE, false);
        $this->initCampaignDateValidator($this->isRequired);
    }

    private function initValidatorsForSiteNumber($isRequired = true)
    {
        $input = [
            'name' => self::FIELD_SITE_NUMBER,
            'required' => $isRequired,
            'validators' => [
                [
                    'name' => Alnum::class,
                ],
            ],
        ];

        $this->add($input);
    }

    private function initValidatorsForVehicleId($isRequired = true)
    {
        $input = [
            'name' => self::FIELD_VEHICLE_ID,
            'required' => $isRequired,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                ],
            ],
        ];

        $this->add($input);
    }

    private function initValidatorsForDates($fieldName, $isRequired = true)
    {
        $input = [
            'name' => $fieldName,
            'required' => $isRequired,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                ],
                [
                    'name' => Date::class,
                    'options' => [
                        'format' => 'Y-m-d H:i:s',
                    ],
                ],
            ],
        ];

        $this->add($input);
    }

    private function initCampaignDateValidator($isRequired = true)
    {
        $input = [
            'name' => self::FIELD_CAMPAIGN_DATES,
            'required' => $isRequired,
            'validators' => [
                [
                    'name' => CampaignDateValidator::class,
                    'options' => [
                        CampaignDateValidator::KEY_START => self::FIELD_START_DATE,
                        CampaignDateValidator::KEY_END => self::FIELD_END_DATE,
                    ],
                ],
            ],
        ];

        $this->add($input);
    }
}
