<?php

namespace Core\Routing;

use Zend\Mvc\Controller\AbstractController;
use Zend\View\Helper\Url;
use Zend\View\Renderer\PhpRenderer;

class VtsRoutes extends AbstractRoutes
{
    public function __construct($urlHelper)
    {
        parent::__construct($urlHelper);
    }

    public function vts($id)
    {
        return $this->url(VtsRouteList::VTS, ['id' => $id]);
    }

    public function vtsEditProperty($id, $propertyName, $formUuid = null)
    {
        return $this->url(
            VtsRouteList::VTS_EDIT_PROPERTY,
            ['id' => $id, 'propertyName' => $propertyName],
            [
                'query' => ['formUuid' => $formUuid],
            ]);
    }

    public function vtsReviewEditProperty($id, $propertyName, $formUuid)
    {
        return $this->url(
            VtsRouteList::VTS_EDIT_PROPERTY_REVIEW,
            ['id' => $id, 'propertyName' => $propertyName, 'formUuid' => $formUuid]
        );
    }

    public function vtsTestQuality($id, $month, $year)
    {
        return $this->url(
            VtsRouteList::VTS_TEST_QUALITY,
            [
                'id'    => $id,
                'month' => $month,
                'year'  => $year,
            ]
        );
    }

    /**
     * @param Url|PhpRenderer|AbstractController|\Zend\Mvc\Controller\Plugin\Url $object
     * @return VtsRoutes
     */
    public static function of($object)
    {
        return new VtsRoutes($object);
    }
}
