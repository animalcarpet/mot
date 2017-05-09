<?php

namespace DvsaMotTest\Model;

class MotChecklistPdfField
{
    /**
     * @var int
     */
    protected $xCoordinate;

    /**
     * @var int
     */
    protected $yCoordinate;

    /**
     * @var int
     */
    protected $fontSize;

    /**
     * @var string Hex color
     */
    protected $fontColor;

    /**
     * @var string
     */
    protected $text;

    public function __construct($text, $x, $y, $fontSize = 9, $fontColor = '#000000')
    {
        $this->text = $text;
        $this->xCoordinate = $x;
        $this->yCoordinate = $y;
        $this->fontSize = $fontSize;
        $this->fontColor = $fontColor;
    }
    /**
     * @return int
     */
    public function getXCoordinate()
    {
        return $this->xCoordinate;
    }

    /**
     * @param int $xCoordinate
     *
     * @return MotChecklistPdfField
     */
    public function setXCoordinate($xCoordinate)
    {
        $this->xCoordinate = $xCoordinate;

        return $this;
    }

    /**
     * @return int
     */
    public function getYCoordinate()
    {
        return $this->yCoordinate;
    }

    /**
     * @param int $yCoordinate
     *
     * @return MotChecklistPdfField
     */
    public function setYCoordinate($yCoordinate)
    {
        $this->yCoordinate = $yCoordinate;

        return $this;
    }

    /**
     * @return int
     */
    public function getFontSize()
    {
        return $this->fontSize;
    }

    /**
     * @param int $fontSize
     *
     * @return MotChecklistPdfField
     */
    public function setFontSize($fontSize)
    {
        $this->fontSize = $fontSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getFontColor()
    {
        return $this->fontColor;
    }

    /**
     * @param string $fontColor
     *
     * @return MotChecklistPdfField
     */
    public function setFontColor($fontColor)
    {
        $this->fontColor = $fontColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return MotChecklistPdfField
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }
}
