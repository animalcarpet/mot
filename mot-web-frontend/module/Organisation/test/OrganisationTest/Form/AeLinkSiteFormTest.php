<?php

namespace OrganisationTest\Form;

use DvsaCommonTest\TestUtils\TestCaseTrait;
use Organisation\Form\AeLinkSiteForm;
use Zend\Stdlib\Parameters;

class AeLinkSiteFormTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    /**
     * @var AeLinkSiteForm
     */
    private $form;

    public function setUp()
    {
        $this->form = new AeLinkSiteForm();
    }

    /**
     * @param string $property
     * @param mixed  $value
     * @param mixed  $expect
     *
     * @dataProvider dataProviderTestGetSet
     */
    public function testGetSet($property, $value, $expect = null)
    {
        $method = ucfirst($property);

        //  logical block: set value and check set method
        $result = $this->form->{'set'.$method}($value);
        $this->assertInstanceOf(AeLinkSiteForm::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? 'is' : 'get').$method;
        $this->assertEquals($expect, $this->form->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            ['siteNumber', 'unitTestSiteNr'],
            ['maxInputLength', 'unitTest_maxInputLength'],
            ['sites', ['unit_Site1', 'unit_Site2']],
        ];
    }

    public function testFromPost()
    {
        $expect = 'unit_SiteNr';
        $postData = [
            AeLinkSiteForm::FIELD_SITE_NR => $expect,
        ];

        $this->form->fromPost(new Parameters($postData));

        $this->assertEquals($expect, $this->form->getSiteNumber());
    }
}
