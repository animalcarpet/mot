<?php

namespace Report\Table\Formatter;

use Zend\View\Renderer\PhpRenderer;
use Report\Table\ColumnOptions;

/**
 * Class Bold.
 */
class Bold implements FormatterInterface
{
    /**
     * @param array                           $data
     * @param ColumnOptions                   $params
     * @param \Zend\View\Renderer\PhpRenderer $view
     *
     * @return string
     */
    public static function format(array $data, ColumnOptions $column, PhpRenderer $view)
    {
        return '<b>'.$view->escapeHtml($data[$column->getField()]).'</b>';
    }
}
