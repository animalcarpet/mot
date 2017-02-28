<?php

namespace Dashboard\ViewModel;

use Dashboard\Model\AuthorisedExaminer;
use Dashboard\Security\DashboardGuard;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilderWeb;

class AuthorisedExaminerViewModel
{
    /** @var AuthorisedExaminerUrlBuilderWeb $url */
    private $url;

    /** @var int $vtsCount */
    private $vtsCount;

    /** @var string $name */
    private $name;

    /** @var string $reference */
    private $reference;

    /** @var VehicleTestingStationViewModel[] $vts */
    private $vts;

    /** @var int $slots */
    private $slots;

    /**
     * AuthorisedExaminerViewModel constructor.
     *
     * @param AuthorisedExaminerUrlBuilderWeb  $url
     * @param int                              $vtsCount
     * @param string                           $name
     * @param string                           $reference
     * @param VehicleTestingStationViewModel[] $vts
     * @param int                              $slots
     */
    public function __construct($url, $vtsCount, $name, $reference, $vts, $slots)
    {
        $this->url = $url;
        $this->vtsCount = $vtsCount;
        $this->name = $name;
        $this->reference = $reference;
        $this->vts = $vts;
        $this->slots = $slots;
    }

    /**
     * @param DashboardGuard     $dashboardGuard
     * @param AuthorisedExaminer $authorisedExaminer
     *
     * @return AuthorisedExaminerViewModel
     */
    public static function fromAuthorisedExaminer(DashboardGuard $dashboardGuard, AuthorisedExaminer $authorisedExaminer)
    {
        $url = AuthorisedExaminerUrlBuilderWeb::of($authorisedExaminer->getId());
        $name = $authorisedExaminer->getName();
        $reference = $authorisedExaminer->getReference();

        $vts = [];
        foreach ($authorisedExaminer->getSites() as $site) {
            if ($dashboardGuard->canViewVehicleTestingStation($site->getId()))
            {
                $vts[] = new VehicleTestingStationViewModel(
                    VehicleTestingStationUrlBuilderWeb::byId($site->getId()),
                    $site->getSiteNumber(),
                    $site->getName(),
                    implode(', ', $site->getPositions())
                );
            }
        }
        $authorisedExaminer->setSites($vts);

        $vtsCount = $authorisedExaminer->getSiteCount();

        $slots = $authorisedExaminer->getSlots();

        return new AuthorisedExaminerViewModel($url, $vtsCount, $name, $reference, $vts, $slots);
    }

    /**
     * @return AuthorisedExaminerUrlBuilderWeb
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getVtsCount()
    {
        return $this->vtsCount;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return VehicleTestingStationViewModel[]
     */
    public function getVts()
    {
        return $this->vts;
    }

    /**
     * @return int
     */
    public function getSlots()
    {
        return $this->slots;
    }
}