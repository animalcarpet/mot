<?php

namespace DvsaEntitiesTest\DqlBuilder\SearchParam;

use DvsaEntities\DqlBuilder\SearchParam\SiteSlotUsageParam;

/**
 * Class SiteSlotUsageParamTest.
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class SiteSlotUsageParamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SiteSlotUsageParam
     */
    private $param;

    public function setUp()
    {
        $this->param = new SiteSlotUsageParam();
    }

    /**
     * @dataProvider providerVtsId
     */
    public function testVtsId($input, $output)
    {
        $this->param->setVtsId($input);
        $this->assertEquals($output, $this->param->getVtsId());
    }

    public function providerVtsId()
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

    public function testGetSortName()
    {
        $this->param->setSortColumnId('date');
        $sortName = $this->param->getSortName();
        $this->assertEquals('date', $sortName);
    }

    public function testGetSortNameWithDefault()
    {
        $this->param->setSortColumnId('not_existing_sort_col');
        $sortName = $this->param->getSortName();
        $this->assertEquals(SiteSlotUsageParam::DEFAULT_SORT_COL, $sortName);
    }
}
