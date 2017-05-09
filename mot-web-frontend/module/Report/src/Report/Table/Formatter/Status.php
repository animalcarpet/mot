<?php

namespace Report\Table\Formatter;

use InvalidArgumentException;
use Organisation\Presenter\StatusPresenterData;
use Report\Table\ColumnOptions;
use Zend\View\Renderer\PhpRenderer;

/**
 * Class Subline.
 */
class Status implements FormatterInterface
{
    /**
     * @param array         $data
     * @param ColumnOptions $column
     * @param PhpRenderer   $view
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public static function format(array $data, ColumnOptions $column, PhpRenderer $view)
    {
        $value = $data[$column->getField()];
        if ($value instanceof StatusPresenterData) {
            return $view->partial(
                'table/formatter/status', [
                    'status' => $value->getStatus(),
                    'class' => $value->getSidebarBadgeCssClass(),
                ]
            );
        } else {
            throw new InvalidArgumentException('StatusPresenterData needed');
        }
    }
}
