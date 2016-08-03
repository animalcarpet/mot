<?php

namespace VehicleApiTest\InputFilter;

use VehicleApi\InputFilter\MysteryShopperInputFilter;
use VehicleApi\MysteryShopper\CampaignDates;
use VehicleApi\Validator\CampaignDateValidator;
use Zend\Form\Element\DateTime;
use Zend\I18n\Validator\Alnum;
use Zend\Validator\Date;
use Zend\Validator\NotEmpty;

/**
 * This is to cover:
 *  - MysteryShopperInputFilter
 *  - CampaignDateValidator
 *
 * Class MysteryShopperInputFilterTest
 */
class MysteryShopperInputFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Dummy booked campaigns
     */
    const EXISTING_CAMPAIGN_1_START_IN  = '-17 days';
    const EXISTING_CAMPAIGN_1_END_IN    = '-10 days';
    const EXISTING_CAMPAIGN_2_START_IN  = '+10 days';
    const EXISTING_CAMPAIGN_2_END_IN    = '+17 days';
    const EXISTING_CAMPAIGN_3_START_IN  = '+30 days';
    const EXISTING_CAMPAIGN_3_END_IN    = '+37 days';

    const NEW_CAMPAIGN_START_IN_PAST   = '-1 days';
    const NEW_CAMPAIGN_VALID_START_IN   = '+20 days';
    const NEW_CAMPAIGN_VALID_END_IN     = '+27 days';

    /**
     * @var MysteryShopperInputFilter
     */
    private $subject;

    public function setUp()
    {
        $this->subject = new MysteryShopperInputFilter();
        $this->subject->init();
    }

    /**
     * @param array $data
     * @param boolean $isValid
     * @param array $expectedMessages
     *
     * @dataProvider getValueContextAndExpectedResult
     */
    public function testValidators($data, $isValid, $expectedMessages)
    {
        $data[MysteryShopperInputFilter::FIELD_CAMPAIGN_DATES] = $this->constructValue($data);

        $this->subject->setData($data);

        $context = $data;
        $context[MysteryShopperInputFilter::FIELD_BOOKED_DATE_RANGES] = $this->getDummyBookedDateRanges();

        $result = $this->subject->isValid($context);
        $message = $this->subject->getMessages();

        $this->assertSame($isValid, $result);

        if (false === $isValid) {
            $this->assertEquals($expectedMessages, $message);
        }
    }

    public function getValueContextAndExpectedResult()
    {
        return [
            [
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => '1',
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => 'VTS1234',
                    MysteryShopperInputFilter::FIELD_START_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_START_IN, true),
                    MysteryShopperInputFilter::FIELD_END_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_END_IN, true),
                    MysteryShopperInputFilter::FIELD_TEST_DATE =>
                        $this->constructDateFromNow('-1 year', true),
                    MysteryShopperInputFilter::FIELD_EXPIRY_DATE =>
                        $this->constructDateFromNow('-1 week', true),
                ],
                true,
                '',
            ],
            [
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => '1',
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => 'VTS1234',
                    MysteryShopperInputFilter::FIELD_START_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_START_IN, true),
                    MysteryShopperInputFilter::FIELD_END_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_END_IN, true),
                    MysteryShopperInputFilter::FIELD_TEST_DATE =>
                        $this->constructDateFromNow('-1 year', true),
                ],
                true,
                '',
            ],
            [
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => '',
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => 'VTS1234',
                    MysteryShopperInputFilter::FIELD_START_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_START_IN, true),
                    MysteryShopperInputFilter::FIELD_END_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_END_IN, true),
                    MysteryShopperInputFilter::FIELD_TEST_DATE =>
                        $this->constructDateFromNow('-1 year', true),
                    MysteryShopperInputFilter::FIELD_EXPIRY_DATE =>
                        $this->constructDateFromNow('-1 week', true),
                ],
                false,
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => [
                        NotEmpty::IS_EMPTY => 'Value is required and can\'t be empty',
                    ],
                ],
            ],
            [
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => '1',
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => '',
                    MysteryShopperInputFilter::FIELD_START_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_START_IN, true),
                    MysteryShopperInputFilter::FIELD_END_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_END_IN, true),
                    MysteryShopperInputFilter::FIELD_TEST_DATE =>
                        $this->constructDateFromNow('-1 year', true),
                    MysteryShopperInputFilter::FIELD_EXPIRY_DATE =>
                        $this->constructDateFromNow('-1 week', true),
                ],
                false,
                [
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => [
                        NotEmpty::IS_EMPTY => 'Value is required and can\'t be empty',
                    ],
                ],
            ],
            [
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => '1',
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => 'VTS-123',
                    MysteryShopperInputFilter::FIELD_START_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_START_IN, true),
                    MysteryShopperInputFilter::FIELD_END_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_END_IN, true),
                    MysteryShopperInputFilter::FIELD_TEST_DATE =>
                        $this->constructDateFromNow('-1 year', true),
                    MysteryShopperInputFilter::FIELD_EXPIRY_DATE =>
                        $this->constructDateFromNow('-1 week', true),
                ],
                false,
                [
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => [
                        Alnum::NOT_ALNUM => 'The input contains characters which are non alphabetic and no digits',
                    ],
                ],
            ],
            [
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => 1,
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => 'VTS1234',
                    MysteryShopperInputFilter::FIELD_START_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_START_IN, true),
                    MysteryShopperInputFilter::FIELD_END_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_END_IN, true),
                    MysteryShopperInputFilter::FIELD_TEST_DATE => '',
                    MysteryShopperInputFilter::FIELD_EXPIRY_DATE => 'asd',
                ],
                false,
                [
                    MysteryShopperInputFilter::FIELD_TEST_DATE => [
                        NotEmpty::IS_EMPTY => 'Value is required and can\'t be empty',
                        Date::INVALID_DATE => 'The input does not appear to be a valid date',
                    ],
                    MysteryShopperInputFilter::FIELD_EXPIRY_DATE => [
                        Date::INVALID_DATE => 'The input does not appear to be a valid date',
                    ],
                    MysteryShopperInputFilter::FIELD_CAMPAIGN_DATES => [
                        CampaignDateValidator::LAST_TEST_IN_FUTURE =>
                            $this->getMessageFromValidatorTemplate(CampaignDateValidator::LAST_TEST_IN_FUTURE),
                    ],
                ],
            ],
            [
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => 1,
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => 'VTS1234',
                    MysteryShopperInputFilter::FIELD_START_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_START_IN, true),
                    MysteryShopperInputFilter::FIELD_END_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_END_IN, true),
                    MysteryShopperInputFilter::FIELD_TEST_DATE => $this->constructDateFromNow('+1 days', true),
                    MysteryShopperInputFilter::FIELD_EXPIRY_DATE => $this->constructDateFromNow('+1 days', true),
                ],
                false,
                [
                    MysteryShopperInputFilter::FIELD_CAMPAIGN_DATES => [
                        CampaignDateValidator::LAST_TEST_IN_FUTURE =>
                            $this->getMessageFromValidatorTemplate(CampaignDateValidator::LAST_TEST_IN_FUTURE),
                    ],
                ],
            ],
            [
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => '1',
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => 'VTS1234',
                    MysteryShopperInputFilter::FIELD_START_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_START_IN_PAST, true),
                    MysteryShopperInputFilter::FIELD_END_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_START_IN, true),
                    MysteryShopperInputFilter::FIELD_TEST_DATE =>
                        $this->constructDateFromNow('-1 year', true),
                    MysteryShopperInputFilter::FIELD_EXPIRY_DATE =>
                        $this->constructDateFromNow('-1 week', true),
                ],
                false,
                [
                    MysteryShopperInputFilter::FIELD_CAMPAIGN_DATES => [
                        CampaignDateValidator::IS_IN_THE_PAST =>
                            $this->getMessageFromValidatorTemplate(CampaignDateValidator::IS_IN_THE_PAST),
                    ],
                ],
            ],
            [
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => '1',
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => 'VTS1234',
                    MysteryShopperInputFilter::FIELD_START_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_END_IN, true),
                    MysteryShopperInputFilter::FIELD_END_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_START_IN, true),
                    MysteryShopperInputFilter::FIELD_TEST_DATE =>
                        $this->constructDateFromNow('-1 year', true),
                    MysteryShopperInputFilter::FIELD_EXPIRY_DATE =>
                        $this->constructDateFromNow('-1 week', true),
                ],
                false,
                [
                    MysteryShopperInputFilter::FIELD_CAMPAIGN_DATES => [
                        CampaignDateValidator::END_BEFORE_START =>
                            $this->getMessageFromValidatorTemplate(CampaignDateValidator::END_BEFORE_START),
                    ],
                ],
            ],
            [
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => 1,
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => 'VTS1234',
                    MysteryShopperInputFilter::FIELD_START_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_START_IN, true),
                    MysteryShopperInputFilter::FIELD_END_DATE =>
                        $this->constructDateFromNow('+32 days', true),
                    MysteryShopperInputFilter::FIELD_TEST_DATE =>
                        $this->constructDateFromNow('-1 year', true),
                    MysteryShopperInputFilter::FIELD_EXPIRY_DATE =>
                        $this->constructDateFromNow('-1 week', true),
                ],
                false,
                [
                    MysteryShopperInputFilter::FIELD_CAMPAIGN_DATES => [
                        CampaignDateValidator::COLLIDING_END_DATE =>
                            $this->getMessageFromValidatorTemplate(CampaignDateValidator::COLLIDING_END_DATE),
                    ]
                ],
            ],
            [
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => 1,
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => 'VTS1234',
                    MysteryShopperInputFilter::FIELD_START_DATE =>
                        $this->constructDateFromNow('+12 days', true),
                    MysteryShopperInputFilter::FIELD_END_DATE =>
                        $this->constructDateFromNow(self::NEW_CAMPAIGN_VALID_END_IN, true),
                    MysteryShopperInputFilter::FIELD_TEST_DATE =>
                        $this->constructDateFromNow('-1 year', true),
                    MysteryShopperInputFilter::FIELD_EXPIRY_DATE =>
                        $this->constructDateFromNow('-1 week', true),
                ],
                false,
                [
                    MysteryShopperInputFilter::FIELD_CAMPAIGN_DATES => [
                        CampaignDateValidator::COLLIDING_START_DATE =>
                            $this->getMessageFromValidatorTemplate(CampaignDateValidator::COLLIDING_START_DATE),
                    ]
                ],
            ],
            [
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => 1,
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => 'VTS1234',
                    MysteryShopperInputFilter::FIELD_START_DATE =>
                        $this->constructDateFromNow('+11 days', true),
                    MysteryShopperInputFilter::FIELD_END_DATE =>
                        $this->constructDateFromNow('+16 days', true),
                    MysteryShopperInputFilter::FIELD_TEST_DATE =>
                        $this->constructDateFromNow('-1 year', true),
                    MysteryShopperInputFilter::FIELD_EXPIRY_DATE =>
                        $this->constructDateFromNow('-1 week', true),
                ],
                false,
                [
                    MysteryShopperInputFilter::FIELD_CAMPAIGN_DATES => [
                        CampaignDateValidator::COLLIDING_START_DATE =>
                            $this->getMessageFromValidatorTemplate(CampaignDateValidator::COLLIDING_START_DATE),
                        CampaignDateValidator::COLLIDING_END_DATE =>
                            $this->getMessageFromValidatorTemplate(CampaignDateValidator::COLLIDING_END_DATE),
                    ]
                ],
            ],
            [
                [
                    MysteryShopperInputFilter::FIELD_VEHICLE_ID => 1,
                    MysteryShopperInputFilter::FIELD_SITE_NUMBER => 'VTS1234',
                    MysteryShopperInputFilter::FIELD_START_DATE =>
                        $this->constructDateFromNow('+9 days', true),
                    MysteryShopperInputFilter::FIELD_END_DATE =>
                        $this->constructDateFromNow('+18 days', true),
                    MysteryShopperInputFilter::FIELD_TEST_DATE =>
                        $this->constructDateFromNow('-1 year', true),
                    MysteryShopperInputFilter::FIELD_EXPIRY_DATE =>
                        $this->constructDateFromNow('-1 week', true),
                ],
                false,
                [
                    MysteryShopperInputFilter::FIELD_CAMPAIGN_DATES => [
                        CampaignDateValidator::COVERING_ANOTHER_CAMPAIGN =>
                            $this->getMessageFromValidatorTemplate(CampaignDateValidator::COVERING_ANOTHER_CAMPAIGN),
                    ]
                ],
            ],
        ];
    }

    private function constructValue($data)
    {
        return new CampaignDates(
            $data[MysteryShopperInputFilter::FIELD_START_DATE],
            $data[MysteryShopperInputFilter::FIELD_END_DATE],
            $data[MysteryShopperInputFilter::FIELD_TEST_DATE]
        );
    }

    /**
     * @see self::EXISTING_CAMPAIGN_*_*_IN
     * @return array
     */
    private function getDummyBookedDateRanges()
    {

        $dummyBookedDateRanges = [];
        for ($i = 1 ; $i <= 3 ; $i++) {

            $startsIn = constant('self::EXISTING_CAMPAIGN_'. $i .'_START_IN');
            $endsIn = constant('self::EXISTING_CAMPAIGN_'. $i .'_END_IN');

            $dummyBookedDateRanges[] = [
                MysteryShopperInputFilter::FIELD_START_DATE => $this->constructDateFromNow($startsIn),
                MysteryShopperInputFilter::FIELD_END_DATE => $this->constructDateFromNow($endsIn),
            ];
        }

        return $dummyBookedDateRanges;
    }

    /**
     * @param string $messageKey
     * @return string
     */
    private function getMessageFromValidatorTemplate($messageKey)
    {
        $dummyValidator = new CampaignDateValidator();
        $templates = $dummyValidator->getMessageTemplates();

        if (false === array_key_exists($messageKey, $templates)) {
            throw new \OutOfBoundsException(sprintf('couldn\'t fine a message "%s" template.', $messageKey));
        }

        return $templates[$messageKey];
    }

    /**
     * @param string $when diff from now e.g. "-5 days" or "+2 month"
     * @param boolean $toString to convert the datetime to string
     * @return \DateTime
     */
    private function constructDateFromNow($when, $toString = false)
    {
        $return = (new \DateTime())->setTimestamp(strtotime(sprintf('%s', $when)));

        if (true === $toString) {
            $return = $return->format('Y-m-d H:i:s');
        }
        return $return;
    }
}
