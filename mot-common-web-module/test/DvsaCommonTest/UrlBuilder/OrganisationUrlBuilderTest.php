<?php

namespace DvsaCommonTest\UrlBuilder;

use DvsaCommon\UrlBuilder\OrganisationUrlBuilder;
use PHPUnit_Framework_TestCase;

/**
 * Class OrganisationPositionUrlBuilderTest
 *
 * @package DvsaCommonTest\UrlBuilder
 */
class OrganisationUrlBuilderTest extends PHPUnit_Framework_TestCase
{
    const ORG_ID = 1111;

    public function test()
    {
        $base = 'organisation/' . self::ORG_ID;
        $this->checkUrl(OrganisationUrlBuilder::organisationById(self::ORG_ID), $base);

        $positionId = 9999;
        $this->checkUrl(
            OrganisationUrlBuilder::position(self::ORG_ID, $positionId),
            $base . '/position/' . $positionId
        );

        $urlBuilder = OrganisationUrlBuilder::organisationById(self::ORG_ID);
        $this->checkUrl($urlBuilder->usage(), $base . OrganisationUrlBuilder::USAGE);

        $urlBuilder = OrganisationUrlBuilder::organisationById(self::ORG_ID);
        $this->checkUrl($urlBuilder->usage()->periodData(), $base . OrganisationUrlBuilder::USAGE.OrganisationUrlBuilder::USAGE_PERIOD_DATA);

        $urlBuilder = OrganisationUrlBuilder::organisationById(self::ORG_ID);
        $this->checkUrl($urlBuilder->createEvent(), $base . OrganisationUrlBuilder::EVENT);
    }

    private function checkUrl(OrganisationUrlBuilder $urlBuilder, $expectUrl)
    {
        $this->assertEquals($expectUrl, $urlBuilder->toString());
        $this->assertInstanceOf(OrganisationUrlBuilder::class, $urlBuilder);
    }
}
