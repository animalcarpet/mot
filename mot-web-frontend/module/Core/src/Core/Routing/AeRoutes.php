<?php

namespace Core\Routing;

use Zend\Mvc\Controller\AbstractController;
use Zend\View\Helper\Url;
use Zend\View\Renderer\PhpRenderer;

class AeRoutes extends AbstractRoutes
{
    public function __construct($urlHelper)
    {
        parent::__construct($urlHelper);
    }

    public function ae($id)
    {
        return $this->url(AeRouteList::AE, ['id' => $id]);
    }

    /**
     * @param Url|PhpRenderer|AbstractController|\Zend\Mvc\Controller\Plugin\Url $object
     *
     * @return AeRoutes
     */
    public static function of($object)
    {
        return new self($object);
    }

    public function aeEditProperty($id, $propertyName, $formUuid = null)
    {
        return $this->url(
            AeRouteList::AE_EDIT_PROPERTY,
            ['id' => $id, 'propertyName' => $propertyName],
            [
                'query' => ['formUuid' => $formUuid],
            ]);
    }

    public function aeReviewEditProperty($id, $propertyName, $formUuid)
    {
        return $this->url(
            AeRouteList::AE_EDIT_PROPERTY_REVIEW,
            ['id' => $id, 'propertyName' => $propertyName, 'formUuid' => $formUuid]
        );
    }

    public function aeAddPrincipal($id, $formUuid = null)
    {
        return $this->url(
            AeRouteList::AE_ADD_PRINCIPAL,
            ['id' => $id],
            [
                'query' => ['formUuid' => $formUuid],
            ]
        );
    }

    public function aeServiceReports($id)
    {
        return $this->url(
            AeRouteList::AE_TEST_QUALITY,
            ['id' => $id]
        );
    }

    public function aeTestQuality($id)
    {
        return $this->url(
            AeRouteList::AE_TEST_QUALITY, ['id' => $id]
        );
    }
}
