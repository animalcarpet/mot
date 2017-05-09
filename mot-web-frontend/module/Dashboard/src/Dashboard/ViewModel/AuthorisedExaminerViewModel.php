<?php

namespace Dashboard\ViewModel;

use Dashboard\Model\AuthorisedExaminer;
use Dashboard\Security\DashboardGuard;
use Zend\Mvc\Controller\Plugin\Url;

class AuthorisedExaminerViewModel
{
    /** @var string $authorisedExaminerUrl */
    private $authorisedExaminerUrl;

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
     * @param string                           $authorisedExaminerUrl
     * @param int                              $vtsCount
     * @param string                           $name
     * @param string                           $reference
     * @param VehicleTestingStationViewModel[] $vts
     * @param int                              $slots
     */
    public function __construct($authorisedExaminerUrl, $vtsCount, $name, $reference, $vts, $slots)
    {
        $this->authorisedExaminerUrl = $authorisedExaminerUrl;
        $this->vtsCount = $vtsCount;
        $this->name = $name;
        $this->reference = $reference;
        $this->vts = $vts;
        $this->slots = $slots;
    }

    /**
     * @param DashboardGuard     $dashboardGuard
     * @param AuthorisedExaminer $authorisedExaminer
     * @param Url                $url
     *
     * @return AuthorisedExaminerViewModel
     */
    public static function fromAuthorisedExaminer(DashboardGuard $dashboardGuard, AuthorisedExaminer $authorisedExaminer, Url $url)
    {
        $authorisedExaminerUrl = $url->fromRoute('authorised-examiner', ['id' => $authorisedExaminer->getId()]);

        $name = $authorisedExaminer->getName();
        $reference = $authorisedExaminer->getReference();

        $visibleVtsViewModels = [];
        $visibleVtsDomainObjects = [];
        foreach ($authorisedExaminer->getSites() as $site) {
            if ($dashboardGuard->canViewVehicleTestingStation($site->getId())) {
                $visibleVtsViewModels[] = new VehicleTestingStationViewModel(
                    $url->fromRoute('vehicle-testing-station', ['id' => $site->getId()]),
                    $site->getSiteNumber(),
                    $site->getName(),
                    $site->getPositions()
                );
                $visibleVtsDomainObjects[] = $site;
            }
        }
        $authorisedExaminer->setSites($visibleVtsDomainObjects);

        $vtsCount = $authorisedExaminer->getSiteCount();

        $slots = $authorisedExaminer->getSlots();

        return new self($authorisedExaminerUrl, $vtsCount, $name, $reference, $visibleVtsViewModels, $slots);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->authorisedExaminerUrl;
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
