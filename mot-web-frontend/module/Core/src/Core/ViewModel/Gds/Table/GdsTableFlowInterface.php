<?php

namespace Core\ViewModel\Gds\Table;

interface GdsTableFlowInterface
{
    /**
     * @param string $htmlId
     *
     * @return GdsRow
     *
     * @internal param string $value
     * @internal param string $label
     */
    public function newRow($htmlId = null);

    /**
     * @return GdsRow[]
     */
    public function getRows();

    /**
     * @param int $index
     *
     * @return GdsRow
     */
    public function getRow($index);
}
