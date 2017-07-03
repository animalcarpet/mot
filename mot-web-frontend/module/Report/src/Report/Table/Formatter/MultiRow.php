<?php

namespace Report\Table\Formatter;

use Report\Table\ColumnOptions;
use Zend\View\Renderer\PhpRenderer;

/**
 * Class Multiline.
 */
class MultiRow implements FormatterInterface
{
    /**
     * @return string
     */
    public static function format(array $data, ColumnOptions $column, PhpRenderer $view)
    {
        return $view->partial(
            'table/formatter/multi-row', [
                'values' => $data[$column->getField()],
                'valuesCount' => count($data[$column->getField()]),
                'escape' => $column->isEscapeHtml(),
                'fieldClass' => $column->getFieldClass(),
            ]
        );
    }
}
