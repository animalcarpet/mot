<?php

namespace Dashboard\ViewModel;

class ReplacementDuplicateCertificateViewModel
{
    /** @var bool $hasTestInProgress */
    private $hasTestInProgress;

    /** @var bool $canViewReplacementDuplicateCertificateLink */
    private $canViewReplacementDuplicateCertificateLink;

    /**
     * ReplacementDuplicateCertificateViewModel constructor.
     *
     * @param bool $hasTestInProgress
     * @param bool $canViewReplacementDuplicateCertificateLink
     */
    public function __construct($hasTestInProgress, $canViewReplacementDuplicateCertificateLink)
    {
        $this->hasTestInProgress = $hasTestInProgress;
        $this->canViewReplacementDuplicateCertificateLink = $canViewReplacementDuplicateCertificateLink;
    }

    /**
     * @return bool
     */
    public function hasTestInProgress()
    {
        return $this->hasTestInProgress;
    }

    /**
     * @return bool
     */
    public function canViewReplacementDuplicateCertificateLink()
    {
        return $this->canViewReplacementDuplicateCertificateLink && !$this->hasTestInProgress();
    }
}
