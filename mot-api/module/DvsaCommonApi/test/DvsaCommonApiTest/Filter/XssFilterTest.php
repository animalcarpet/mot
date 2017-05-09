<?php

namespace DvsaCommonApiTest\Filter;

use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommonApi\Filter\XssFilter;
use HTMLPurifier;

/**
 * Class XssFilterTest.
 */
class XssFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \DvsaCommonApi\Filter\XssFilter
     */
    protected $filter;

    public function setUp()
    {
        $htmlPurifier = new HTMLPurifier();
        $this->filter = new XssFilter($htmlPurifier);
    }

    public function testScriptTagsAreStripped()
    {
        $filter = $this->filter;
        $valuesExpected = [
            '<script>alert(document.cookie)</script>' => '',
            'Hello<script>alert(document.cookie)</script> World!' => 'Hello World!',
        ];

        // Test using filter()
        foreach ($valuesExpected as $input => $expect) {
            $this->assertEquals($expect, $filter->filter($input));
        }

        // Test using filterMultiple()
        $filteredValues = $filter->filter($valuesExpected);
        foreach (array_keys($valuesExpected) as $k) {
            $this->assertEquals($valuesExpected[$k], $filteredValues[$k]);
        }
    }

    public function testDto()
    {
        //  --  expected dto (clean)   --
        $addressDto = new AddressDto();
        $addressDto
            ->setAddressLine1('a1 &<IMG SRC=JaVaScRiPt:alert("XSS")>')
            ->setAddressLine2('a2 &#34; <IMG """>"> &amp; &lt;')
            ->setAddressLine3('a3 <IMG SRC=# onmouseover="alert(\'xxs\')">')
            ->setAddressLine4('a4 <IMG SRC= onmouseover="alert(\'xxs\')">')
            ->setTown('a5 <IMG SRC="jav&#x0D;ascript:alert(\'XSS\');">')
            ->setCountry('a6 <BODY onload!#$%&()*~+-_.,:;?@[/|\]^`=alert("XSS")>')
            ->setPostcode('a7 <');

        $expect = new OrganisationContactDto();
        $expect
            ->setAddress($addressDto)
            ->setPhones(
                [
                    (new PhoneDto())->setNumber('b1 Hello World!'),
                    (new PhoneDto())->setNumber('b2 & <> "'),
                    (new PhoneDto())->setNumber('b3 '),
                    (new PhoneDto())->setNumber('b4 &amp; &lt; &gt; &quot;  &#34;'),
                ]
            );

        //  --  given dto (dirty) with XSS stuff   --
        $addressDto = new AddressDto();
        $addressDto
            ->setAddressLine1('a1 &<IMG SRC=JaVaScRiPt:alert("XSS")>')
            ->setAddressLine2('a2 &#34; <IMG """><SCRIPT>alert("XSS")</SCRIPT>"> &amp; &lt;')
            ->setAddressLine3('a3 <IMG SRC=# onmouseover="alert(\'xxs\')">')
            ->setAddressLine4('a4 <IMG SRC= onmouseover="alert(\'xxs\')">')
            ->setTown('a5 <IMG SRC="jav&#x0D;ascript:alert(\'XSS\');">')
            ->setCountry('a6 <BODY onload!#$%&()*~+-_.,:;?@[/|\]^`=alert("XSS")>')
            ->setPostcode('a7 <<SCRIPT>alert("XSS");//<</SCRIPT>');

        $contactDto = new OrganisationContactDto();
        $contactDto
            ->setAddress($addressDto)
            ->setPhones(
                [
                    (new PhoneDto())->setNumber('b1 Hello<script>alert(document.cookie)</script> World!'),
                    (new PhoneDto())->setNumber('b2 & <> "<SCRIPT a=">\'>" SRC="http://ha.ckers.org/xss.js"></SCRIPT>'),
                    (new PhoneDto())->setNumber('b3 <SCRIPT a=`>` SRC="http://ha.ckers.org/xss.js"></SCRIPT>'),
                    (new PhoneDto())->setNumber('b4 &amp; &lt; &gt; &quot;  &#34;'),
                ]
            );

        $actual = $this->filter->filter($contactDto);

        $this->assertEquals($expect, $actual);
    }
}
