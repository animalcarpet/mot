<?php

namespace ReportTest\Table;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Search\MotTestSearchParamsDto;
use DvsaCommon\Dto\Search\SearchParamsDto;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use Report\Table\ColumnOptions;
use Report\Table\Formatter as Formatter;
use Report\Table\Formatter\Bold;
use Report\Table\Table;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\Mvc\Router\RouteMatch;
use Zend\View\Helper\Url;
use Zend\View\Renderer\PhpRenderer;

class ColumnOptionsTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    /** @var PhpRenderer */
    private static $renderer;

    /** @var ColumnOptions */
    private $columnOptions;

    public function setUp()
    {
        $this->columnOptions = new ColumnOptions();
    }

    public function tearDown()
    {
        unset($this->columnOptions);
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
        $result = $this->columnOptions->{'set'.$method}($value);
        $this->assertInstanceOf(ColumnOptions::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? 'is' : 'get').$method;
        $this->assertEquals($expect, $this->columnOptions->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        $sub = [
            ['field' => 'testSubField1'],
            ['field' => 'testSubField2'],
        ];

        return [
            [
                'property' => 'field',
                'value' => 'testField',
            ],
            ['title', 'testTitle'],
            ['sortable', true],
            ['sortable', 'a', true],
            ['sortable', null, false],
            ['formatter', new Formatter\Bold()],
            ['sortBy', 'testSortBy'],
            ['thClass', 'testThClass'],
            ['tdClass', 'testTdClass'],
            ['escapeHtml', false, false],
            [
                'property' => 'sub',
                'value' => $sub,
                'expect' => [new ColumnOptions($sub[0]), new ColumnOptions($sub[1])],
            ],
        ];
    }

    public function testGetSetTable()
    {
        $table = new Table();

        //  logical block: prepare column option
        $sub = [
            ['field' => 'testSubField1'],
            ['field' => 'testSubField2'],
        ];
        $this->columnOptions->setSub($sub);

        //  logical block: set value and check set method
        $result = $this->columnOptions->setTable($table);
        $this->assertInstanceOf(ColumnOptions::class, $result);

        $this->assertEquals($table, $this->columnOptions->getTable());

        //  logical block: check
        foreach ($this->columnOptions->getSub() as $subCol) {
            $this->assertSame($table, $subCol->getTable());
        }
    }

    /**
     * @dataProvider dataProviderTestSort
     */
    public function testGetSortCssClass($sortByField, SearchParamsDto $searchParams, $expect)
    {
        //  logical block: prepare objects
        $table = new Table();
        $table->setSearchParams($searchParams);

        $this->columnOptions->setTable($table);
        $this->columnOptions->setSortBy($sortByField);

        //  locigal block: call and check
        $actual = $this->columnOptions->getSortCssClass();

        $this->assertSame($expect['css'], $actual);
    }

    /**
     * @dataProvider dataProviderTestSort
     */
    public function testGetUrl($sortByField, SearchParamsDto $searchParams, $expect)
    {
        //  logical block: prepare objects
        $table = new Table();
        $table->setSearchParams($searchParams);

        $this->columnOptions->setTable($table);
        $this->columnOptions->setSortBy($sortByField);

        //  locigal block: call
        $viewRenderer = $this->getViewRenderer();
        $actual = $this->columnOptions->getUrl($viewRenderer);

        //  locigal block: check
        $queryParams = [
            SearchParamConst::ROW_COUNT => 10,
            SearchParamConst::PAGE_NR => 1,
        ];

        $expectUrl = '/?'.http_build_query($queryParams + $expect['queryParams']);

        $this->assertSame($expectUrl, $actual);
    }

    public function dataProviderTestSort()
    {
        $searchParams = (new SearchParamsDto())
            ->setSortBy('testFieldA')
            ->setSortDirection(SearchParamConst::SORT_DIRECTION_ASC);

        return [
            [
                'sortBy' => 'testFieldA',
                'searchParams' => $this->cloneObj($searchParams)
                    ->setSortDirection(SearchParamConst::SORT_DIRECTION_DESC),
                'expect' => [
                    'css' => ColumnOptions::SORT_CSS_DESC,
                    'queryParams' => [
                        SearchParamConst::SORT_BY => 'testFieldA',
                        SearchParamConst::SORT_DIRECTION => SearchParamConst::SORT_DIRECTION_ASC,
                    ],
                ],
            ],
            [
                'sortBy' => 'testFieldA',
                'searchParams' => $searchParams,
                'expect' => [
                    'css' => ColumnOptions::SORT_CSS_ASC,
                    'queryParams' => [
                        SearchParamConst::SORT_BY => 'testFieldA',
                        SearchParamConst::SORT_DIRECTION => SearchParamConst::SORT_DIRECTION_DESC,
                    ],
                ],
            ],
            [
                'sortBy' => 'testFieldB',
                'searchParams' => $searchParams,
                'expect' => [
                    'css' => '',
                    'queryParams' => [
                        SearchParamConst::SORT_BY => 'testFieldB',
                        SearchParamConst::SORT_DIRECTION => SearchParamConst::SORT_DIRECTION_ASC,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestRenderCellContent
     */
    public function testRenderCellContent($row, ColumnOptions $column, $expect)
    {
        //  block: call
        $viewRenderer = $this->getViewRenderer();
        $actual = $column->renderCellContent($row, $viewRenderer);

        //  block: check
        $this->assertEquals($expect, $actual);
    }

    public function dataProviderTestRenderCellContent()
    {
        $row = [
            'fieldA' => 'lorem Ipsum',
            'fieldB' => 'La la la jaga jaga',
            'fieldC' => 'just test text',
        ];

        $table = new Table();
        $table->setSearchParams(new MotTestSearchParamsDto());

        return [
            //  without formatter & no value for column
            [
                'row' => $row,
                'column' => (new ColumnOptions())
                    ->setField('notExistField'),
                'expect' => '',
            ],
            //  without formatter & value is for column
            [
                'row' => $row,
                'column' => (new ColumnOptions())
                    ->setField('fieldA'),
                'expect' => $row['fieldA'],
            ],
            //  without formatter & 2 fields in column
            [
                'row' => $row,
                'column' => (new ColumnOptions())
                    ->setSub(
                        [
                            ['field' => 'fieldA'],
                            ['field' => 'fieldC'],
                        ]
                    ),
                'expect' => $row['fieldA'].$row['fieldC'],
            ],
            //  with formatter & value is for column
            [
                'row' => $row,
                'column' => (new ColumnOptions())
                    ->setField('fieldB')
                    ->setFormatter(Bold::class)
                    ->setTable($table),
                'expect' => '<b>'.$row['fieldB'].'</b>',
            ],
        ];
    }

    /**
     * @return SearchParamsDto
     */
    private function cloneObj($obj)
    {
        return clone $obj;
    }

    private function getViewRenderer()
    {
        if (self::$renderer) {
            return self::$renderer;
        }

        /** @var \Zend\ServiceManager\ServiceManager $serviceManager */
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        self::$renderer = new PhpRenderer([]);

        $mockUrl = new Url();
        $mockUrl->setRouter(TreeRouteStack::factory($serviceManager->get('Config')['router']));
        $mockUrl->setRouteMatch((new RouteMatch([]))->setMatchedRouteName('user-home'));

        $viewHelperManager = $serviceManager->get('ViewHelperManager');
        $viewHelperManager->setService('url', $mockUrl);

        return self::$renderer
            ->setHelperPluginManager($viewHelperManager);
    }
}
