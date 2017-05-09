<?php

namespace DvsaEntitiesTest\DqlBuilder\SearchParam;

use DvsaEntities\DqlBuilder\SearchParam\TransactionSearchParam;

/**
 * Class OrgSlotUsageParamTest.
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class TransactionSearchParamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TransactionSearchParam
     */
    private $param;

    public function setUp()
    {
        $this->param = new TransactionSearchParam();
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
     * @dataProvider providerStatus
     */
    public function testStatus($input, $output)
    {
        $this->param->setStatus($input);
        $this->assertEquals($output, $this->param->getStatus());
    }

    public function providerStatus()
    {
        return array(
            array('ok', 'ok'),
            array(true, true),
        );
    }

    public function testGetSortName()
    {
        $this->param->setSortColumnId('completedOn');
        $sortName = $this->param->getSortName();
        $this->assertEquals('completedOn', $sortName);
    }

    public function testGetSortNameWithDefault()
    {
        $this->param->setSortColumnId('not_existing_sort_col');
        $sortName = $this->param->getSortName();
        $this->assertEquals(TransactionSearchParam::DEFAULT_SORT_COL, $sortName);
    }

    public function testProcess()
    {
        $this->param->setOrganisationId(1);
        $return = $this->param->process();
        $this->assertEquals($this->param, $return);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testProcessWithNoOrganisation()
    {
        $this->param->process();
    }

    public function testToArray()
    {
        $data = $this->param->toArray();
        $this->assertArrayHasKey('organisationId', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('dateFrom', $data);
        $this->assertArrayHasKey('dateTo', $data);
    }
}
