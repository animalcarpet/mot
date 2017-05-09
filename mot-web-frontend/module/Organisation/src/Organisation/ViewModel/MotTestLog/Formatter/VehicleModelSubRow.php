<?php

namespace Organisation\ViewModel\MotTestLog\Formatter;

use Report\Table\ColumnOptions;
use Report\Table\Formatter\FormatterInterface;
use Zend\View\Renderer\PhpRenderer;

/**
 * Class Vehicle Model SubRow.
 */
class VehicleModelSubRow implements FormatterInterface
{
    /**
     * @return string
     */
    public static function format(array $data, ColumnOptions $column, PhpRenderer $view)
    {
        return $view->partial(
            'mot-test-log/formatter/vehicle-model-sub-row', [
                'value' => $data[$column->getField()],
            ]
        );
    }
}
