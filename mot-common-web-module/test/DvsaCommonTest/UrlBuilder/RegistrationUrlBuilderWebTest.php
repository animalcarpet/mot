<?php

namespace ApplicationTest\UrlBuilder;

use DvsaCommon\UrlBuilder\RegistrationUrlBuilderWeb;

class RegistrationUrlBuilderWebTest extends \PHPUnit_Framework_TestCase
{
    /** @var RegistrationUrlBuilderWeb $obj */
    private $obj;

    public function setUp()
    {
        parent::setUp();
        $this->obj = new RegistrationUrlBuilderWeb;
    }

    public function testMainRoute()
    {
        $actual = $this->obj->register()->toString();
        $expected = RegistrationUrlBuilderWeb::MAIN;
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider childRoutesDataProvider
     *
     * @param string $functionName
     * @param string $childRoute
     */
    public function testDirectChildRoutes($functionName, $childRoute)
    {
        $url = call_user_func([$this->obj, $functionName]);
        $actual = $url->toString();
        // Concatenate the child route to the main route
        $expected = RegistrationUrlBuilderWeb::MAIN.$childRoute;
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public function childRoutesDataProvider()
    {
        return [
            ['createStep', RegistrationUrlBuilderWeb::CREATE],
            ['contactDetailsStep', RegistrationUrlBuilderWeb::CONTACT_DETAILS],
            ['passwordStep', RegistrationUrlBuilderWeb::PASSWORD],
            ['detailsStep', RegistrationUrlBuilderWeb::DETAILS],
            ['summaryStep', RegistrationUrlBuilderWeb::SUMMARY],
            ['securityQuestionsStep', RegistrationUrlBuilderWeb::SECURITY_QUESTIONS],
        ];
    }
}