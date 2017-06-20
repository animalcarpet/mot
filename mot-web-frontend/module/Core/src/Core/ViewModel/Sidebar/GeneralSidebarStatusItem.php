<?php

namespace Core\ViewModel\Sidebar;

use Core\ViewModel\Badge\Badge;

class GeneralSidebarStatusItem
{
    private $key;
    private $value;
    private $badge;
    private $secondaryText;
    private $tertiaryText;
    private $htmlId;

    public function __construct($htmlId, $key, $value, Badge $badge, $secondaryText = null, $tertiaryText = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->badge = $badge;
        $this->secondaryText = $secondaryText;
        $this->tertiaryText = $tertiaryText;
        $this->htmlId = $htmlId;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getSecondaryText()
    {
        return $this->secondaryText;
    }

    public function getTertiaryText()
    {
        return $this->tertiaryText;
    }

    public function getHtmlId()
    {
        return $this->htmlId;
    }

    public function escapeKey()
    {
        return true;
    }

    public function escapeValue()
    {
        return true;
    }

    public function escapeSecondaryText()
    {
        return true;
    }

    public function getBadge()
    {
        return $this->badge;
    }

    public function getSecondary()
    {
        return $this->secondary;
    }
}
