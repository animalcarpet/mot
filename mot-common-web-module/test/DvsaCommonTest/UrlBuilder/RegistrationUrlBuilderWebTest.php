<?php

namespace ApplicationTest\UrlBuilder;

use DvsaCommon\UrlBuilder\RegistrationUrlBuilderWeb;
use DvsaCommonTest\TestUtils\XMock;

class RegistrationUrlBuilderWebTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RegigrationUrlBuilderWeb
     */
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
     * @dataProvider dpChildRoutes
     */
    public function testDirectChildRoutes($functionName, $childRoute)
    {
        /**
         * Here I use the object in the class to make the call to the function defined
         * in the dataProvider using call_user_func
         */
        $url = call_user_func([$this->obj, $functionName]);
        $actual = $url->toString();
        // Concatenate the child route to the main route
        $expected = RegistrationUrlBuilderWeb::MAIN.$childRoute;
        $this->assertSame($expected, $actual);
    }

    public function dpChildRoutes()
    {
        return [
            ['createStep', RegistrationUrlBuilderWeb::CREATE],
            ['contactDetailsStep', RegistrationUrlBuilderWeb::CONTACT_DETAILS],
            ['passwordStep', RegistrationUrlBuilderWeb::PASSWORD],
            ['detailsStep', RegistrationUrlBuilderWeb::DETAILS],
            ['summaryStep', RegistrationUrlBuilderWeb::SUMMARY],
            ['securityQuestionStepOne', RegistrationUrlBuilderWeb::SECURITY_QUESTION_ONE],
            ['securityQuestionStepTwo', RegistrationUrlBuilderWeb::SECURITY_QUESTION_TWO]
        ];
    }
}