<?php

namespace Dashboard\ViewModel;

use Dashboard\Model\AuthorisedExaminer;
use Dashboard\Security\DashboardGuard;
use Iterator;
use Zend\Mvc\Controller\Plugin\Url;

class AuthorisedExaminersViewModel implements Iterator
{
    /** @var DashboardGuard $dashboardGuard */
    private $dashboardGuard;

    /** @var AuthorisedExaminerViewModel[] $authorisedExaminerViewModels */
    private $authorisedExaminerViewModels;

    /** @var int $currentPosition */
    private $currentPosition = 1;

    /**
     * AuthorisedExaminersViewModel constructor.
     *
     * @param DashboardGuard                $dashboardGuard
     * @param AuthorisedExaminerViewModel[] $authorisedExaminerViewModels
     */
    public function __construct($dashboardGuard, $authorisedExaminerViewModels)
    {
        $this->dashboardGuard = $dashboardGuard;
        $this->authorisedExaminerViewModels = $authorisedExaminerViewModels;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->dashboardGuard->canViewVehicleTestingStationList();
    }

    /**
     * @param DashboardGuard       $dashboardGuard
     * @param AuthorisedExaminer[] $authorisedExaminers
     * @param Url                  $url
     *
     * @return AuthorisedExaminersViewModel
     */
    public static function fromAuthorisedExaminers($dashboardGuard, $authorisedExaminers, $url)
    {
        $authorisedExaminerViewModels = [];

        foreach ($authorisedExaminers as $authorisedExaminer) {
            $authorisedExaminerViewModels[] = AuthorisedExaminerViewModel::fromAuthorisedExaminer(
                $dashboardGuard,
                $authorisedExaminer,
                $url
            );
        }

        return new AuthorisedExaminersViewModel($dashboardGuard, $authorisedExaminerViewModels);
    }

    /**
     * @return AuthorisedExaminerViewModel
     */
    public function current()
    {
        return $this->authorisedExaminerViewModels[$this->currentPosition];
    }

    public function next()
    {
        $this->currentPosition++;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->currentPosition;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return array_key_exists($this->currentPosition, $this->authorisedExaminerViewModels);
    }

    public function rewind()
    {
        $this->currentPosition = 0;
    }
}
