<?php

namespace OrganisationTest\Form;

use DvsaCommonTest\TestUtils\TestCaseTrait;
use Organisation\Form\AeUnlinkSiteForm;
use Zend\Stdlib\Parameters;

class AeUnlinkSiteFormTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    /**
     * @var AeUnlinkSiteForm
     */
    private $form;

    public function setUp()
    {
        $this->form = new AeUnlinkSiteForm();
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
        $this->assertInstanceOf(AeUnlinkSiteForm::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? 'is' : 'get').$method;
        $this->assertEquals($expect, $this->form->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            ['status', 'status'],
            ['statuses', ['unit_Status1', 'unit_Status2']],
        ];
    }

    public function testFromPost()
    {
        $expect = 'unit_Status';
        $postData = [
            AeUnlinkSiteForm::FIELD_STATUS => $expect,
        ];

        $this->form->fromPost(new Parameters($postData));

        $this->assertEquals($expect, $this->form->getStatus());
    }
}
