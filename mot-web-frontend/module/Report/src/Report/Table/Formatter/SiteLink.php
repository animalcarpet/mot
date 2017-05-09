<?php

namespace Report\Table\Formatter;

use DvsaCommon\UrlBuilder\SiteUrlBuilderWeb;
use Report\Table\ColumnOptions;
use Zend\View\Renderer\PhpRenderer;

/**
 * Class SiteLink formatter for Table.
 */
class SiteLink implements FormatterInterface
{
    /**
     * @return string|\Zend\View\Helper\Partial
     */
    public static function format(array $data, ColumnOptions $column, PhpRenderer $view)
    {
        $field = $column->getField();

        return $view->partial(
            'table/formatter/mot-test-link', [
                'url' => SiteUrlBuilderWeb::of($data['id']),
                'value' => $data[$field],
            ]
        );
    }
}
