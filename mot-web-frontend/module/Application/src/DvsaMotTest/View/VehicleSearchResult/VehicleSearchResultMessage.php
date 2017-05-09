<?php

namespace DvsaMotTest\View\VehicleSearchResult;

class VehicleSearchResultMessage
{
    private $mainMessage;
    private $additionalMessage;

    public function __construct($mainMessage, $additionalMessage)
    {
        $this->mainMessage = $mainMessage;
        $this->additionalMessage = $additionalMessage;
    }

    public function getAdditionalMessage()
    {
        return $this->additionalMessage;
    }

    public function getMainMessage()
    {
        return $this->mainMessage;
    }

    public static function getEmpty()
    {
        return new self('', '');
    }
}
