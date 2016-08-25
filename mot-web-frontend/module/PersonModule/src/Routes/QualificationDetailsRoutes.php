<?php
namespace Dvsa\Mot\Frontend\PersonModule\Routes;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class QualificationDetailsRoutes extends PersonProfileRoutes implements AutoWireableInterface
{
    const ROUTE_QUALIFICATION_DETAILS = '/qualification-details';
    const ROUTE_QUALIFICATION_DETAILS_ADD = '/add';
    const ROUTE_QUALIFICATION_DETAILS_EDIT = '/edit';
    const ROUTE_QUALIFICATION_DETAILS_REVIEW = '/review';
    const ROUTE_QUALIFICATION_DETAILS_REMOVE = '/remove';
    const ROUTE_QUALIFICATION_DETAILS_ADD_CONFIRMATION = '/add-confirmation';

    public function getRoute()
    {
        $route = parent::getRoute();
        return $route.self::ROUTE_QUALIFICATION_DETAILS;
    }

    public function getAddRoute()
    {
        return $this->getRoute().self::ROUTE_QUALIFICATION_DETAILS_ADD;
    }

    public function getAddReviewRoute()
    {
        return $this->getAddRoute().self::ROUTE_QUALIFICATION_DETAILS_REVIEW;
    }

    public function getAddConfirmationRoute()
    {
        return $this->getAddRoute().self::ROUTE_QUALIFICATION_DETAILS_ADD_CONFIRMATION;
    }

    public function getEditRoute()
    {
        return $this->getRoute().self::ROUTE_QUALIFICATION_DETAILS_EDIT;
    }

    public function getEditReviewRoute()
    {
        return $this->getEditRoute().self::ROUTE_QUALIFICATION_DETAILS_REVIEW;
    }

    public function getRemoveRoute()
    {
        return $this->getRoute().self::ROUTE_QUALIFICATION_DETAILS_REMOVE;
    }
}