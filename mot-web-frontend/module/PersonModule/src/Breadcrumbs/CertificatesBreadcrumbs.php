<?php
namespace Dvsa\Mot\Frontend\PersonModule\Breadcrumbs;

use Zend\Mvc\Controller\AbstractActionController;

class CertificatesBreadcrumbs extends PersonProfileBreadcrumbs
{
    const ROUTE_QUALIFICATION_DETAILS = '/qualification-details';
    const QUALIFICATION_DETAILS_BREADCRUMB = 'MOT tester training certificates';
    const ROUTE_ANNUAL_ASSESSMENT_CERTIFICATE = '/annual-assessment-certificates';
    const ANNUAL_ASSESSMENT_CERTIFICATE_BREADCRUMB = 'Annual assessment certificates';

    public function getBreadcrumbs($personId, AbstractActionController $controller, $currentStep = null)
    {
        $breadcrumbs = parent::getBreadcrumbs($personId, $controller, $currentStep);

        return $breadcrumbs;
    }

    public function getRoute()
    {
        return parent::getRoute();
    }

    public function getQualificationDetailsRoute()
    {
        return $this->getRoute() . self::ROUTE_QUALIFICATION_DETAILS;
    }

    public function getBreadcrumbsForAnnualAssessmentCertificate(
        $personId,
        AbstractActionController $controller,
        $currentStep = null
    ) {
        $breadcrumbs = $this->getBreadcrumbs($personId, $controller, null);

        return $this->getBreadcrumbsForData(
            $breadcrumbs,
            self::ANNUAL_ASSESSMENT_CERTIFICATE_BREADCRUMB,
            self::ROUTE_ANNUAL_ASSESSMENT_CERTIFICATE,
            $controller,
            $currentStep
        );
    }

    public function getBreadcrumbsForQualificationDetails(
        $personId,
        AbstractActionController $controller,
        $currentStep = null
    ) {
        $breadcrumbs = $this->getBreadcrumbs($personId, $controller, null);

        return $this->getBreadcrumbsForData(
            $breadcrumbs,
            self::QUALIFICATION_DETAILS_BREADCRUMB,
            self::ROUTE_QUALIFICATION_DETAILS,
            $controller,
            $currentStep
        );
    }

    public function getRouteForData($breadcrumbRouteName)
    {
        $route = $this->getRoute();
        return $route . $breadcrumbRouteName;
    }

    private function getBreadcrumbsForData(
        $breadcrumbs,
        $breadcrumbName,
        $breadcrumbRouteName,
        AbstractActionController $controller,
        $currentStep
    ) {
        $breadcrumbs += [
            $breadcrumbName => $controller->url()->fromRoute($this->getRouteForData($breadcrumbRouteName),
                $controller->params()->fromRoute()),
        ];

        if (!empty($currentStep)) {
            $breadcrumbs += [
                $currentStep => '',
            ];
        }

        return $breadcrumbs;
    }
}