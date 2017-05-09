<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace VehicleApiTest\Validator;

use VehicleApi\InputFilter\MysteryShopperInputFilter;
use VehicleApi\MysteryShopper\CampaignDates;
use VehicleApi\Validator\CampaignDateValidator;
use Zend\Validator\Date;

/**
 * To test ONLY the expected exceptions on CampaignDateValidator
 * as its unit test is taking place in MysteryShopperInputFilterTest
 * Class CampaignDatesTest.
 */
class CampaignDateValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var CampaignDateValidator */
    private $subject;

    public function setUp()
    {
        $this->subject = new CampaignDateValidator();
    }

    public function testExpectedValue()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            sprintf(CampaignDateValidator::ERR_MSG_VALUE_TYPE, CampaignDates::class)
        );
        $this->subject->isValid('string is unacceptable');
    }

    public function testExpectedContext()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            sprintf(
                CampaignDateValidator::ERR_MSG_NO_CONTEXT.CampaignDateValidator::ERR_MSG_MISSING_KEY_DESCRIPTION,
                CampaignDateValidator::KEY_BOOKED_DATE_RANGES
            )
        );
        $this->subject->isValid(new CampaignDates('a', 'b', 'c'));
    }

    public function testExpectedDateRangesAreDateTime()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            CampaignDateValidator::ERR_MSG_NOT_DATETIME
        );

        $context = [
            CampaignDateValidator::KEY_BOOKED_DATE_RANGES => [
                [
                    MysteryShopperInputFilter::FIELD_START_DATE => 'incorrect date',
                    MysteryShopperInputFilter::FIELD_END_DATE => 'incorrect date',
                ],
            ],
        ];

        $this->subject->setOptions(
            [
                CampaignDateValidator::KEY_START => MysteryShopperInputFilter::FIELD_START_DATE,
                CampaignDateValidator::KEY_END => MysteryShopperInputFilter::FIELD_END_DATE,
            ]
        );

        $this->subject->isValid(new CampaignDates('a', 'b', 'c'), $context);
    }
}
