<?php

namespace ReportTest\Table;

use DvsaCommon\Dto\Search\SearchParamsDto;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\TestCaseViewTrait;
use DvsaCommonTest\TestUtils\XMock;
use Report\Table\ColumnOptions;
use Report\Table\Table;
use Report\Table\TableOptions;
use Zend\View\Helper\Url;

class TableTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseViewTrait;

    /**
     * @var Table
     */
    private $table;

    public function setUp()
    {
        $this->table = new Table();
    }

    public function tearDown()
    {
        unset($this->table);
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
        $result = $this->table->{'set'.$method}($value);
        $this->assertInstanceOf(Table::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? 'is' : 'get').$method;
        $this->assertEquals($expect, $this->table->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            [
                'property' => 'rowsTotalCount',
                'value' => 9999,
            ],
            ['data', ['row1Data', 'row2Data']],
            ['searchParams', new SearchParamsDto()],
            ['tableOptions', new TableOptions()],
        ];
    }

    public function testGetSetColumns()
    {
        $columns = [
            ['field' => 'testSubField1'],
            ['field' => 'testSubField2'],
        ];

        $expect = [
            (new ColumnOptions($columns[0]))->setTable($this->table),
            (new ColumnOptions($columns[1]))->setTable($this->table),
        ];

        //  logical block: set value and check set method
        $result = $this->table->setColumns($columns);
        $this->assertInstanceOf(Table::class, $result);

        //  logical block: check get method
        $this->assertEquals($expect, $this->table->getColumns());
    }

    public function testRenderTable()
    {
        //  logical block: prepare
        $renderer = $this->getPhpRenderer(
            [
                'table/table' => __DIR__.'/../../../view/table/default.phtml',
            ]
        );

        $title = 'testField1Title';
        $value = 'testField1Value';

        $columns = [
            [
                'field' => 'testField1',
                'title' => $title,
            ],
        ];
        $rowsData = [
            [
                'testField1' => $value,
            ],
        ];

        $this->table
            ->setSearchParams(
                (new SearchParamsDto())
            )
            ->setColumns($columns)
            ->setData($rowsData);

        //  logical block: call
        $actual = $this->table->renderTable($renderer);

        //  logical block: check
        $doc = new \DOMDocument();
        $doc->loadHTML($actual);
        $xpath = new \DOMXPath($doc);

        $entries = $xpath->query('//tr');
        $this->assertEquals($title, trim($entries->item(0)->nodeValue));
        $this->assertEquals($value, trim($entries->item(1)->nodeValue));

        $this->assertStringStartsWith(
            '<table class="result-table" id="dataTable">',
            trim($actual)
        );
    }

    public function testRenderFooter()
    {
        //  logical block: prepare
        $renderer = $this->getPhpRenderer(
            [
                'table/footer' => __DIR__.'/../../../view/table/footer.phtml',
                'table/paginator' => __DIR__.'/../../../view/table/paginator.phtml',
            ]
        );

        $viewHelperManager = Bootstrap::getServiceManager()->get('ViewHelperManager');
        $viewHelperManager->setService('url', XMock::of(Url::class));

        $renderer->setHelperPluginManager($viewHelperManager);

        $this->table
            ->setRowsTotalCount(99)
            ->setSearchParams(
                (new SearchParamsDto())->setPageNr(2)
            );

        //  logical block: call
        $actual = $this->table->renderFooter($renderer);

        //  logical block: check
        $doc = new \DOMDocument();
        $doc->loadHTML($actual);
        $xpath = new \DOMXPath($doc);

        //  check if items by page selector exists
        $this->assertEquals(1, $xpath->query('//div[contains(@class, "page-results-control")]')->length);

        //  check if paginator element, next and prev links exists
        $this->assertEquals(1, $xpath->query('//ul[@class="data-paging"]')->length);
        $this->assertEquals(1, $xpath->query('//li[@class="data-paging__next"]')->length);
        $this->assertEquals(1, $xpath->query('//li[@class="data-paging__prev"]')->length);
    }
}
