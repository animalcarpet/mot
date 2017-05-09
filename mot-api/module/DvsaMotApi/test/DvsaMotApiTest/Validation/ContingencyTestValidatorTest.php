<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaMotApiTest\Validation;

use DvsaEntities\Entity\EmergencyLog;
use DvsaMotApi\Service\EmergencyService;
use DvsaMotApi\Validation\ContingencyTestValidator;
use Exception;
use PHPUnit_Framework_TestCase;
use SiteApi\Service\SiteService;
use stdClass;
use DateTime;

class ContingencyTestValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EmergencyService
     */
    private $emergencyService;

    /**
     * @var SiteService
     */
    private $siteService;

    public function setUp()
    {
        $this->emergencyService = $this
            ->getMockBuilder(EmergencyService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->siteService = $this
            ->getMockBuilder(SiteService::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider dataProviderTestParams
     */
    public function testSiteWithValidSite($params)
    {
        $this
            ->siteService
            ->expects($this->once())
            ->method('getSite')
            ->willReturn(new stdClass());

        $validator = new ContingencyTestValidator($this->emergencyService, $this->siteService, $params['isInfinityContingencyOn']);

        $result = $validator->validate(['siteId' => '123']);
        $messages = $result->getFlattenedMessages();

        $this->assertArrayNotHasKey('site', $messages);
    }

    /**
     * @dataProvider dataProviderTestParams
     */
    public function testWithInvalidSite($params)
    {
        $this
            ->siteService
            ->expects($this->once())
            ->method('getSite')
            ->will($this->throwException(new Exception()));

        $validator = new ContingencyTestValidator($this->emergencyService, $this->siteService, $params['isInfinityContingencyOn']);

        $result = $validator->validate(['siteId' => '123']);
        $messages = $result->getFlattenedMessages();
        $this->assertArrayHasKey(ContingencyTestValidator::FIELDSET_SITE, $messages);
        $this->assertNotEmpty($messages[ContingencyTestValidator::FIELDSET_SITE]);
        $this->assertEquals(ContingencyTestValidator::MESSAGE_MUST_BE_VALID_SITE,
            $messages[ContingencyTestValidator::FIELDSET_SITE]);
        $this->assertFalse($result->isValid());
    }

    /**
     * @dataProvider dataProviderTestParams
     */
    public function testWithValidContingencyCode($params)
    {
        $this
            ->emergencyService
            ->expects($this->once())
            ->method('getEmergencyLog')
            ->willReturn(new EmergencyLog());

        $validator = new ContingencyTestValidator($this->emergencyService, $this->siteService, $params['isInfinityContingencyOn']);

        $result = $validator->validate(['contingencyCode' => '12345A']);
        $messages = $result->getFlattenedMessages();

        $this->assertArrayNotHasKey(ContingencyTestValidator::FIELDSET_CONTINGENCY_CODE, $messages);
    }

    /**
     * @dataProvider dataProviderTestParams
     */
    public function testWithInvalidContingencyCode($params)
    {
        $this
            ->emergencyService
            ->expects($this->once())
            ->method('getEmergencyLog')
            ->willReturn(null);

        $validator = new ContingencyTestValidator($this->emergencyService, $this->siteService, $params['isInfinityContingencyOn']);

        $result = $validator->validate(['contingencyCode' => '12345A']);
        $messages = $result->getFlattenedMessages();
        $this->assertArrayHasKey(ContingencyTestValidator::FIELDSET_CONTINGENCY_CODE, $messages);
        $this->assertNotEmpty($messages[ContingencyTestValidator::FIELDSET_CONTINGENCY_CODE]);
        $this->assertEquals(ContingencyTestValidator::MESSAGE_MUST_BE_VALID_CONTINGENCY_CODE, $messages['contingencyCode']);
        $this->assertFalse($result->isValid());
    }

    /**
     * @dataProvider dataProviderTestParams
     */
    public function testInfinityContingency($params)
    {
        $validator = new ContingencyTestValidator($this->emergencyService, $this->siteService, $params['isInfinityContingencyOn']);

        $dateTime = new DateTime('-3 months -1 minute');
        $result = $validator->validate([
            'siteId' => '123',
            'contingencyCode' => '12345A',
            'reasonCode' => 'SO',
            'performedAtHour' => $dateTime->format('g'),
            'performedAtMinute' => $dateTime->format('i'),
            'performedAtAmPm' => $dateTime->format('a'),
            'performedAtYear' => $dateTime->format('Y'),
            'performedAtMonth' => $dateTime->format('m'),
            'performedAtDay' => $dateTime->format('d'), ]);
        $messages = $result->getFlattenedMessages();
        if ($params['isInfinityContingencyOn']) {
            $this->assertArrayNotHasKey(ContingencyTestValidator::FIELDSET_DATE, $messages);
        } else {
            $this->assertEquals(ContingencyTestValidator::MESSAGE_MUST_BE_LESS_THAN_3_MONTHS, $messages['date']);
            $this->assertFalse($result->isValid());
        }
    }

    public function dataProviderTestParams()
    {
        return [
            // 3 months timeframe in CommonContingency is OFF
            [
                'params' => [
                    'isInfinityContingencyOn' => true,
                ],
            ],
            // 3 months timeframe in CommonContingency is ON
            [
                'params' => [
                    'isInfinityContingencyOn' => false,
                ],
            ],
        ];
    }
}
