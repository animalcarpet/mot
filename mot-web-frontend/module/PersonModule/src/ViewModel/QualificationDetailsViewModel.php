<?php

namespace Dvsa\Mot\Frontend\PersonModule\ViewModel;

class QualificationDetailsViewModel
{
    private $template = 'qualification-details/view';
    private $pageTitle = 'MOT tester training certificates';
    private $returnLinkText = 'Return to %s';
    private $returnLink;
    private $pageSubtitle;
    private $qualificationDetailsGroupBViewModel;
    private $isGuidanceShown;

    public function __construct(
        $returnLink,
        $pageSubtitle,
        QualificationDetailsGroupViewModel $qualificationDetailsGroupAViewModel,
        QualificationDetailsGroupViewModel $qualificationDetailsGroupBViewModel,
        $isGuidanceShown
    ) {
        $this->returnLink = $returnLink;
        $this->pageSubtitle = $pageSubtitle;
        $this->qualificationDetailsGroupAViewModel = $qualificationDetailsGroupAViewModel;
        $this->qualificationDetailsGroupBViewModel = $qualificationDetailsGroupBViewModel;
        $this->isGuidanceShown = $isGuidanceShown;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function getQualificationDetailsGroupAViewModel()
    {
        return $this->qualificationDetailsGroupAViewModel;
    }

    public function getQualificationDetailsGroupBViewModel()
    {
        return $this->qualificationDetailsGroupBViewModel;
    }

    public function getPageSubtitle()
    {
        return $this->pageSubtitle;
    }

    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    public function getReturnLinkText()
    {
        return sprintf($this->returnLinkText, strtolower($this->pageSubtitle));
    }

    public function getReturnLink()
    {
        return $this->returnLink;
    }

    public function canUserSeeGuidance()
    {
        return $this->isGuidanceShown;
    }
}
