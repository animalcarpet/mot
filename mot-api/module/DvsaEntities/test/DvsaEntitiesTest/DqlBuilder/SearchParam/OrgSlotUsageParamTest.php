<?php

namespace DvsaEntitiesTest\DqlBuilder\SearchParam;

use DvsaEntities\DqlBuilder\SearchParam\OrgSlotUsageParam;

/**
 * Class OrgSlotUsageParamTest.
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class OrgSlotUsageParamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrgSlotUsageParam
     */
    private $param;

    public function setUp()
    {
        $this->param = new OrgSlotUsageParam();
    }

    /**
     * @dataProvider providerOrganisationId
     */
    public function testOrganisationId($input, $output)
    {
        $this->param->setOrganisationId($input);
        $this->assertEquals($output, $this->param->getOrganisationId());
    }

    public function providerOrganisationId()
    {
        return array(
            array(1, 1),
            array(999, 999),
        );
    }

    /**
     * @dataProvider providerDateFrom
     */
    public function testDateFrom($input, $output)
    {
        $this->param->setDateFrom($input);
        $this->assertEquals($output, $this->param->getDateFrom());
    }

    public function providerDateFrom()
    {
        return array(
            array('2014-01-01', '2014-01-01'),
        );
    }

    /**
     * @dataProvider providerDateTo
     */
    public function testDateTo($input, $output)
    {
        $this->param->setDateTo($input);
        $this->assertEquals($output, $this->param->getDateTo());
    }

    public function providerDateTo()
    {
        return array(
            array('2014-01-01', '2014-01-01'),
        );
    }

    /**
     * @dataProvider providerSearchText
     */
    public function testSearchText($input, $output)
    {
        $this->param->setSearchText($input);
        $this->assertEquals($output, $this->param->getSearchText());
    }

    public function providerSearchText()
    {
        return array(
            array('test', 'test'),
            array('very_long_string_very_long_string_very_long_string_very_long_string_very_long_string_very_long_string_',
                'very_long_string_very_long_string_very_long_string_very_long_string_very_long_string_very_long_string_', ),
        );
    }

    public function testGetSortName()
    {
        $this->param->setSortColumnId('usage');
        $sortName = $this->param->getSortName();
        $this->assertEquals('usage', $sortName);
    }

    public function testGetSortNameWithDefault()
    {
        $this->param->setSortColumnId('not_existing_sort_col');
        $sortName = $this->param->getSortName();
        $this->assertEquals(OrgSlotUsageParam::DEFAULT_SORT_COL, $sortName);
    }
}
