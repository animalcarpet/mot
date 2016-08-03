<?php

namespace Report\Table\Formatter;

use Report\Table\ColumnOptions;
use Zend\View\Renderer\PhpRenderer;

/**
 * Class Subline
 *
 * @package Report\Table\Formatter
 */
class SubRow implements FormatterInterface
{
    /**
     * @return string
     */
    public static function format(array $data, ColumnOptions $column, PhpRenderer $view)
    {
        return $view->partial(
            'table/formatter/sub-row', [
                'value'  => $data[$column->getField()],
                'escape' => $column->isEscapeHtml(),
            ]
        );
    }
}
