<?php

namespace Dvsa\Mot\Frontend\PersonModule\Routes;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class AnnualAssessmentCertificatesRoutes extends PersonProfileRoutes implements AutoWireableInterface
{
    const ROUTE_INDEX = '/annual-assessment-certificates';
    const ROUTE_ADD = '/add';
    const ROUTE_REVIEW = '/review';
    const ROUTE_REMOVE = '/remove';
    const ROUTE_EDIT = '/edit';

    public function getRoute()
    {
        $route = parent::getRoute();

        return $route.self::ROUTE_INDEX;
    }

    public function getAddRoute()
    {
        return $this->getRoute().self::ROUTE_ADD;
    }

    public function getAddReviewRoute()
    {
        return $this->getAddRoute().self::ROUTE_REVIEW;
    }

    public function getRemove()
    {
        return $this->getRoute().self::ROUTE_REMOVE;
    }

    public function getEditRoute()
    {
        return $this->getRoute().self::ROUTE_EDIT;
    }

    public function getEditReviewRoute()
    {
        return $this->getEditRoute().self::ROUTE_REVIEW;
    }
}
