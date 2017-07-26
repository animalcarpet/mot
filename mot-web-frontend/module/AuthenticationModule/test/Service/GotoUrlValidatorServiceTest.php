<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Service;

use Dvsa\Mot\Frontend\AuthenticationModule\Service\GotoUrlValidatorService;

class GotoUrlValidatorServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $validDomains = [];

    /**
     * @var GotoUrlValidatorService
     */
    protected $gotoUrlValidatorService;

    public function setUp()
    {
        $this->validDomains = ['mot.gov.uk'];
        $this->gotoUrlValidatorService = new GotoUrlValidatorService($this->validDomains);
    }

    /**
     * @dataProvider getValidUrls
     */
    public function testIsValid($url)
    {
        $this->assertTrue($this->gotoUrlValidatorService->isValid($url));
    }

    /**
     * @dataProvider getInvalidUrls
     */
    public function testIsInvalid($url)
    {
        $this->assertFalse($this->gotoUrlValidatorService->isValid($url));
    }

    public function getValidUrls()
    {
        return [
            ['http://mysite.mot.gov.uk'],
            ['http://mot.gov.uk'],
            ['http://mot-web-frontend.mot.gov.uk']
        ];
    }

    public function getInvalidUrls()
    {
        return [
            ['http://invalid1.mot.com'],
            ['http://invalid2.mot-web-frontend.gov'],
            ['http://mot.gov.uk/login'],
            ['http://mot.gov.uk/logout']
        ];
    }
}
