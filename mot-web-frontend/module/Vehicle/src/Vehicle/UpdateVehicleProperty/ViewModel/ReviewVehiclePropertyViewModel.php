<?php

namespace Vehicle\UpdateVehicleProperty\ViewModel;

use Core\ViewModel\Gds\Table\GdsTable;
use Core\ViewModel\Header\HeaderTertiaryList;

class ReviewVehiclePropertyViewModel
{
    /**
     * @var GdsTable
     */
    private $summary;

    /**
     * @var string
     */
    private $formActionUrl;

    /**
     * @var string
     */
    private $submitButtonText;

    /**
     * @var string
     */
    private $cancelUrl;

    /**
     * @var string
     */
    private $cancelLinkLabel;

    /**
     * @var HeaderTertiaryList
     */
    private $pageTertiaryTitle;

    /**
     * @return GdsTable
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param GdsTable $summary
     *
     * @return ReviewVehiclePropertyViewModel
     */
    public function setSummary(GdsTable $summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->formActionUrl;
    }

    /**
     * @param string $formActionUrl
     *
     * @return ReviewVehiclePropertyViewModel
     */
    public function setFormActionUrl($formActionUrl)
    {
        $this->formActionUrl = $formActionUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    /**
     * @param string $submitButtonText
     *
     * @return ReviewVehiclePropertyViewModel
     */
    public function setSubmitButtonText($submitButtonText)
    {
        $this->submitButtonText = $submitButtonText;

        return $this;
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    /**
     * @param string $cancelUrl
     *
     * @return ReviewVehiclePropertyViewModel
     */
    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getCancelLinkLabel()
    {
        return $this->cancelLinkLabel;
    }

    /**
     * @param string $cancelLinkLabel
     *
     * @return ReviewVehiclePropertyViewModel
     */
    public function setCancelLinkLabel($cancelLinkLabel)
    {
        $this->cancelLinkLabel = $cancelLinkLabel;

        return $this;
    }

    /**
     * @return HeaderTertiaryList
     */
    public function getPageTertiaryTitle()
    {
        return $this->pageTertiaryTitle;
    }

    /**
     * @param HeaderTertiaryList $pageTertiaryTitle
     *
     * @return ReviewVehiclePropertyViewModel
     */
    public function setPageTertiaryTitle($pageTertiaryTitle)
    {
        $this->pageTertiaryTitle = $pageTertiaryTitle;

        return $this;
    }
}
