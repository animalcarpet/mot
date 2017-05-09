<?php

namespace Report\Table;

use DvsaCommon\Dto\Search\SearchParamsDto;
use Zend\Paginator\Adapter\NullFill as NullAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Renderer\PhpRenderer;

/**
 * Contains parameters of Table, functionality for setup table and columns parameters, draw table and footer.
 */
class Table
{
    /**
     * @var array
     */
    private $data;
    /**
     * @var int
     */
    private $rowsTotalCount;
    /**
     * @var ColumnOptions[]
     */
    private $columns;
    /**
     * @var TableOptions
     */
    private $tableOptions;
    /**
     * @var SearchParamsDto
     */
    private $searchParams;

    public function __construct()
    {
        $this->setTableOptions(new TableOptions());
    }

    public function getRowsTotalCount()
    {
        return $this->rowsTotalCount;
    }

    /**
     * @return $this
     */
    public function setRowsTotalCount($rowsTotalCount)
    {
        $this->rowsTotalCount = (int) $rowsTotalCount;

        return $this;
    }

    public function getSearchParams()
    {
        return $this->searchParams;
    }

    /**
     * @return $this
     */
    public function setSearchParams(SearchParamsDto $searchParams)
    {
        $this->searchParams = $searchParams;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return $this
     */
    public function setData($rows)
    {
        $this->data = $rows;

        return $this;
    }

    /**
     * @return ColumnOptions[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return $this
     */
    public function setColumns($columns)
    {
        foreach ($columns as $column) {
            $this->columns[] = (new ColumnOptions($column))
                ->setTable($this);
        }

        return $this;
    }

    /**
     * @return TableOptions
     */
    public function getTableOptions()
    {
        return $this->tableOptions;
    }

    /**
     * @return $this
     */
    public function setTableOptions(TableOptions $tableOptions)
    {
        $this->tableOptions = $tableOptions;

        return $this;
    }

    /**
     * Render table from view script.
     *
     * @param PhpRenderer $phpRenderer
     *
     * @return string|\Zend\View\Helper\Partial
     */
    public function renderTable(PhpRenderer $phpRenderer)
    {
        return $phpRenderer->partial(
            $this->tableOptions->getTableViewScript(), [
                'table' => $this,
            ]
        );
    }

    /**
     * Render pagination stuff.
     *
     * @param PhpRenderer $phpRenderer
     *
     * @return string
     *
     * @throws \Exception
     */
    public function renderFooter(PhpRenderer $phpRenderer)
    {
        $adapter = new NullAdapter($this->getRowsTotalCount());

        $searchParams = $this->getSearchParams();

        $paginator = new Paginator($adapter);
        $paginator
            ->setItemCountPerPage($searchParams->getRowsCount())
            ->setCurrentPageNumber($searchParams->getPageNr());

        return $phpRenderer->partial(
            $this->tableOptions->getFooterViewScript(), [
                'paginator' => $paginator,
                'searchParams' => $searchParams,
                'tableOptions' => $this->tableOptions,
            ]
        );
    }
}
