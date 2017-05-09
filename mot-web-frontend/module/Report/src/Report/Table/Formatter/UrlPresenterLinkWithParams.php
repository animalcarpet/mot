<?php

namespace Report\Table\Formatter;

use InvalidArgumentException;
use Organisation\Presenter\UrlPresenterData;
use Report\Table\ColumnOptions;
use Zend\View\Renderer\PhpRenderer;

/**
 * Class UrlPresenterLinkWithParams formatter for Table.
 */
class UrlPresenterLinkWithParams implements FormatterInterface
{
    /**
     * @param array         $data
     * @param ColumnOptions $column
     * @param PhpRenderer   $view
     *
     * @return string|\Zend\View\Helper\Partial
     *
     * @throws InvalidArgumentException
     */
    public static function format(array $data, ColumnOptions $column, PhpRenderer $view)
    {
        $field = $data[$column->getField()];

        if (is_array($field)) {
            $fields = $field;
        } else {
            $fields = [$field];
        }

        $links = [];
        foreach ($fields as $urlPresenterData) {
            $links[] = static::getLink($view, $urlPresenterData);
        }

        return implode(' ', $links);
    }

    private static function getLink(PhpRenderer $view, UrlPresenterData $field)
    {
        return $view->partial(
            'table/formatter/link-with-params', [
                'root' => $field->getRoot(),
                'params' => $field->getParams(),
                'queryParams' => $field->getQueryParams(),
                'value' => $field->getValue(),
                'id' => $field->getId(),
            ]
        );
    }
}
