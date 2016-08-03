<?php

namespace Report\Table;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Utility\ArrayUtils;
use Zend\Stdlib\AbstractOptions;
use Zend\View\Renderer\PhpRenderer;

/**
 * Contains parameters of table Column and related functionality (draw, sorting and other)
 *
 * @package Report\Table
 */
class ColumnOptions extends AbstractOptions
{
    const SORT_CSS_ASC = 'sort-asc';
    const SORT_CSS_DESC = 'sort-desc';

    /**
     * @var string
     */
    private $field;
    /**
     * @var string
     */
    private $title;
    /**
     * @var bool
     */
    private $sortable = false;
    /**
     * @var string
     */
    private $sortBy = false;
    /**
     * @var callable
     */
    private $formatter;
    /**
     * @var string
     */
    private $thClass = 'tabular';
    /**
     * @var string
     */
    private $tdClass = 'tabular';
    /**
     * @var ColumnOptions[]
     */
    private $sub;
    /**
     * @var  Table
     */
    private $table;
    /**
     * @var bool
     */
    private $escapeHtml = true;

    /**
     * @return boolean
     */
    public function isEscapeHtml()
    {
        return $this->escapeHtml;
    }

    /**
     * @param boolean $escapeHtml
     *
     * @return $this
     */
    public function setEscapeHtml($escapeHtml)
    {
        $this->escapeHtml = $escapeHtml;

        return $this;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * @param boolean $sortable
     *
     * @return $this
     */
    public function setSortable($sortable)
    {
        $this->sortable = (boolean) $sortable;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param string $sortBy
     *
     * @return $this
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
        return $this;
    }

    /**
     * @return callable
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * @param callable $formatter
     *
     * @return $this
     */
    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @return string
     */
    public function getThClass()
    {
        return $this->thClass;
    }

    /**
     * @param string $thClass
     *
     * @return $this
     */
    public function setThClass($thClass)
    {
        $this->thClass = $thClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getTdClass()
    {
        return $this->tdClass;
    }

    /**
     * @param string $tdClass
     *
     * @return $this
     */
    public function setTdClass($tdClass)
    {
        $this->tdClass = $tdClass;

        return $this;
    }

    /**
     * @return ColumnOptions[]
     */
    public function getSub()
    {
        return $this->sub;
    }

    /**
     * @param ColumnOptions[] $sub
     *
     * @return $this
     */
    public function setSub(array $sub)
    {
        $subCols = [];

        foreach ($sub as $item) {
            $subCols[] = new ColumnOptions($item);
        }

        $this->sub = $subCols;

        return $this;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return $this
     */
    public function setTable(Table $table)
    {
        $this->table = $table;

        if (!empty($this->getSub())) {
            foreach ($this->getSub() as $subCol) {
                $subCol->setTable($table);
            }
        }

        return $this;
    }

    /**
     * Get sort class based on query parameters
     *
     * @return string
     */
    public function getSortCssClass()
    {
        $searchParams = $this->table->getSearchParams();

        $sortBy = $searchParams->getSortBy();
        if (!$sortBy || $sortBy !== $this->getSortBy()) {
            return '';
        }

        return (
            $searchParams->getSortDirection() === SearchParamConst::SORT_DIRECTION_ASC
            ? self::SORT_CSS_ASC
            : self::SORT_CSS_DESC
        );
    }

    /**
     * Get Url for sorting based on query parameters
     *
     * @param PhpRenderer $renderer
     *
     * @return string
     */
    public function getUrl(PhpRenderer $renderer)
    {
        $searchParams = clone $this->table->getSearchParams();

        if (
            $searchParams->getSortBy() === $this->getSortBy()
            && $searchParams->getSortDirection() === SearchParamConst::SORT_DIRECTION_ASC
        ) {
            $sortDirection = SearchParamConst::SORT_DIRECTION_DESC;
        } else {
            $sortDirection = SearchParamConst::SORT_DIRECTION_ASC;
        }

        $params = $searchParams
            ->setSortBy($this->getSortBy())
            ->setSortDirection($sortDirection)
            ->toQueryParams()->toArray();

        return $renderer->url(null, [], ['query' => $params], true);
    }

    /**
     * Render cell content based on provided formatter,
     * which can be either a closure or class implementing FormatterInterface
     *
     * @param array       $row
     * @param PhpRenderer $view
     *
     * @return string
     */
    public function renderCellContent($row, PhpRenderer $view = null)
    {
        //  --  if have few field column --
        if (!empty($this->getSub())) {
            $result = [];

            foreach ($this->getSub() as $col) {
                $result[] = $col->renderCellContent($row, $view);
            }

            return join('', $result);
        }

        //  --  if have single field column --
        $value = ArrayUtils::tryGet($row, $this->getField());
        if ($value === null) {
            return '';
        }

        $formatterClass = $this->getFormatter();
        if (!empty($formatterClass) && class_exists($formatterClass)) {
            $method = '\\' . $formatterClass . '::format';
            if (is_callable($method)) {
                return call_user_func($method, $row, $this, $view);
            }
        }

        return ($this->isEscapeHtml()) ? $view->escapeHtml($row[$this->getField()]) : $row[$this->getField()];
    }
}
