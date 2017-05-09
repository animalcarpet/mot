<?php

namespace Report\Table\Formatter;

use Report\Table\ColumnOptions;
use Zend\View\Renderer\PhpRenderer;

/**
 * Data formatter.
 *
 * Interface FormatterInterface
 */
interface FormatterInterface
{
    /**
     * Format an cell.
     *
     * @param array         $data
     * @param ColumnOptions $column
     * @param PhpRenderer   $view
     *
     * @return mixed
     */
    public static function format(array $data, ColumnOptions $column, PhpRenderer $view);
}
