<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Domain\Survey;

use DvsaMotApi\Domain\Survey\SurveyToken;
use PHPUnit_Framework_TestCase;

class SurveyTokenTest extends PHPUnit_Framework_TestCase
{
    public function testTokenIsInitalised()
    {
        $surveyToken = new SurveyToken();
        $this->assertInternalType('string', $surveyToken->toString());
        $this->assertEquals($surveyToken->toString(), (string) $surveyToken);
    }

    /**
     * @param string $uuid
     * @param bool   $isValid
     *
     * @dataProvider tokensForValidationProvider
     */
    public function testTokenValidator($uuid, $isValid)
    {
        $this->assertEquals($isValid, SurveyToken::isValid($uuid));
    }

    /**
     * @return array
     */
    public function tokensForValidationProvider()
    {
        return [
            [null, false],
            ['123e4567-e89b-12d3-a456-426655440000', true],
            ['xxxxxxxx-xxxx-Mxxx-Nxxx-xxxxxxxxxxxx', false],
            ['validate this!', false],
        ];
    }

    public function testGeneratedTokenIsValid()
    {
        $surveyToken = new SurveyToken();
        $this->assertTrue(SurveyToken::isValid($surveyToken->toString()));
    }
}
