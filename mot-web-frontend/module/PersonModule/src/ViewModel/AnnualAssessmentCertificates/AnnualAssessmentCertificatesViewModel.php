<?php

namespace Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates;

use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;

class AnnualAssessmentCertificatesViewModel
{
    private $template = 'annual-assessment-certificates/view';
    private $pageTitle = 'Annual assessment certificates';
    private $pageSubtitle;
    private $returnUrl;
    private $returnLinkText = 'Return to %s';
    private $addGroupALink;
    private $addGroupBLink;
    private $isGrantedToAddCertificates;

    /** @var AnnualAssessmentCertificatesGroupViewModel  */
    private $annualAssessmentCertificatesGroupAViewModel;

    /** @var AnnualAssessmentCertificatesGroupViewModel  */
    private $annualAssessmentCertificatesGroupBViewModel;

    /** @var bool */
    private $isUserViewingHisOwnProfile;

    public function __construct(
        $pageSubtitle,
        $returnUrl,
        AnnualAssessmentCertificatesGroupViewModel $annualAssessmentCertificatesGroupAViewModel,
        $addGroupALink,
        AnnualAssessmentCertificatesGroupViewModel $annualAssessmentCertificatesGroupBViewModel,
        $addGroupBLink,
        $isGrantedToAddCertificates,
        $isUserViewingHisOwnProfile
    )
    {
        $this->pageSubtitle = $pageSubtitle;
        $this->returnUrl = $returnUrl;
        $this->annualAssessmentCertificatesGroupAViewModel = $annualAssessmentCertificatesGroupAViewModel;
        $this->addGroupALink = $addGroupALink;
        $this->annualAssessmentCertificatesGroupBViewModel = $annualAssessmentCertificatesGroupBViewModel;
        $this->addGroupBLink = $addGroupBLink;
        $this->isGrantedToAddCertificates = $isGrantedToAddCertificates;
        $this->isUserViewingHisOwnProfile = $isUserViewingHisOwnProfile;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function getPageSubtitle()
    {
        return $this->pageSubtitle;
    }

    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    public function getReturnLink()
    {
        return $this->returnUrl;
    }

    public function getReturnLinkText()
    {
        return sprintf($this->returnLinkText, strtolower($this->pageSubtitle));
    }

    public function getAnnualAssessmentCertificatesGroupAViewModel()
    {
        return $this->annualAssessmentCertificatesGroupAViewModel;
    }

    public function getAnnualAssessmentCertificatesGroupBViewModel()
    {
        return $this->annualAssessmentCertificatesGroupBViewModel;
    }

    public function getAddGroupALink()
    {
        return $this->addGroupALink;
    }

    public function getAddGroupBLink()
    {
        return $this->addGroupBLink;
    }

    public function isAddLinkVisible()
    {
        return $this->isGrantedToAddCertificates;
    }

    public function getQualificationDetailsLink()
    {
        return ContextProvider::YOUR_PROFILE_PARENT_ROUTE . "/qualification-details";
    }

    public function isUserViewingHisOwnProfile()
    {
        return $this->isUserViewingHisOwnProfile;
    }
}