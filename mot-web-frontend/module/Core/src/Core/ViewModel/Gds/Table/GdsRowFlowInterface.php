<?php

namespace Core\ViewModel\Gds\Table;

interface GdsRowFlowInterface extends GdsTableFlowInterface
{
    public function getHtmlId();
    public function isHtmlClassSet();
    public function getHtmlClass();

    /**
     * @param $content
     * @param bool $escape
     *
     * @return GdsRowLabel
     */
    public function setLabel($content, $escape = true);

    /**
     * @return GdsRowLabel
     */
    public function getLabel();

    /**
     * @param $content
     * @param bool $escape
     *
     * @return GdsRowValue
     */
    public function setValue($content, $escape = true);

    /**
     * @return GdsRowValue
     */
    public function getValue();

    /**
     * @param $content
     * @param bool $escape
     *
     * @return GdsRowValueMetaData
     */
    public function setValueMetaData($content, $escape = true);

    /**
     * @return GdsRowValueMetaData
     */
    public function getValueMetaData();

    /**
     * @param $text
     * @param $url
     * @param string $tooltip
     *
     * @return GdsTableActionLink
     */
    public function addActionLink($text, $url, $tooltip = ' ');

    /**
     * @return GdsTableActionLink
     */
    public function getActionLink();

    /**
     * @return bool
     */
    public function hasActionLink();
}
