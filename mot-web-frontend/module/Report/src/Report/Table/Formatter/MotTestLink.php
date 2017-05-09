<?php

namespace Report\Table\Formatter;

use DvsaCommon\UrlBuilder\MotTestUrlBuilderWeb;
use Report\Table\ColumnOptions;
use Zend\View\Renderer\PhpRenderer;

/**
 * Class MotTestLink formatter for Table.
 */
class MotTestLink implements FormatterInterface
{
    /**
     * @return string|\Zend\View\Helper\Partial
     */
    public static function format(array $data, ColumnOptions $column, PhpRenderer $view)
    {
        $field = $column->getField();

        return $view->partial(
            'table/formatter/mot-test-link', [
                'url' => MotTestUrlBuilderWeb::motTest($data['motTestNumber']),
                'value' => $data[$field],
            ]
        );
    }
}
