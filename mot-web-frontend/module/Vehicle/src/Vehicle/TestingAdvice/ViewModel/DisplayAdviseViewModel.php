<?php

namespace Vehicle\TestingAdvice\ViewModel;

use Dvsa\Mot\ApiClient\Resource\Item\VehicleTestingData\TestingAdvice;

class DisplayAdviseViewModel
{
    private $backLinkUrl;
    private $backLinkLabel;
    private $feedbackLink;
    private $testingAdvice;

    public function __construct(TestingAdvice $testingAdvice, $backLinkUrl, $backLinkLabel, $feedbackLink)
    {
        $this->testingAdvice = $testingAdvice;
        $this->backLinkUrl = $backLinkUrl;
        $this->backLinkLabel = $backLinkLabel;
        $this->feedbackLink = $feedbackLink;
    }

    public function getBackLinkUrl()
    {
        return $this->backLinkUrl;
    }

    public function getBackLinkLabel()
    {
        return $this->backLinkLabel;
    }

    public function getFeedbackLink()
    {
        return $this->feedbackLink;
    }

    public function getTestingAdvice()
    {
        return $this->testingAdvice;
    }
}
