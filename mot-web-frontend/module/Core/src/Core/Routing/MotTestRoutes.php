<?php

namespace Core\Routing;

use Zend\Mvc\Controller\AbstractController;
use Zend\View\Helper\Url;
use Zend\View\Renderer\PhpRenderer;

class MotTestRoutes extends AbstractRoutes
{
    public function __construct($urlHelper)
    {
        parent::__construct($urlHelper);
    }

    /**
     * @param Url|PhpRenderer|AbstractController|\Zend\Mvc\Controller\Plugin\Url $object
     * @return MotTestRoutes
     */
    public static function of($object)
    {
        return new MotTestRoutes($object);
    }

    public function vehicleSearchByVin($vin)
    {
        return $this->url(MotTestRouteList::MOT_TEST_CERTIFICATE_SEARCH_BY_VIN, [], ['query' => ["vin" => $vin]]);
    }

    public function vehicleSearchByRegistration($vrm)
    {
        return $this->url(MotTestRouteList::MOT_TEST_CERTIFICATE_SEARCH_BY_REGISTRATION, [], ['query' => ["vrm" => $vrm]]);
    }

    public function vehicleSearchResults($searchParams)
    {
        return $this->url(MotTestRouteList::MOT_TEST_CERTIFICATE_SEARCH_RESULTS, [], ['query' => $searchParams]);
    }

    public function vehicleMotTestCertificateView($testNumber, $searchParams)
    {
        return $this->url(MotTestRouteList::MOT_TEST_CERTIFICATE_VIEW, ['motTestNumber' => $testNumber], ['query' => $searchParams]);
    }

    public function printDuplicateTestResult($motTest)
    {
       return $this->url(MotTestRouteList::MOT_TEST_PRINT_DUPLICATE_TEST_RESULT, ['motTestNumber' => $motTest]);
    }

    public function printDuplicateCertificate($motTest)
    {
        return $this->url(MotTestRouteList::MOT_TEST_CERTIFICATE_PRINT, ['motTestNumber' => $motTest]);
    }

    public function editDuplicateCertificate($motTest)
    {
        return $this->url(MotTestRouteList::MOT_TEST_CERTIFICATE_EDIT, ['motTestNumber' => $motTest]);
    }
}
