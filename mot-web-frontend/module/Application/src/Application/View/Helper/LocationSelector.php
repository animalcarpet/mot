<?php

namespace Application\View\Helper;

use DvsaMotTest\Controller\LocationSelectController;
use DvsaMotTest\Helper\LocationSelectContainerHelper;
use Zend\Mvc\Router\RouteMatch;
use Zend\View\Helper\AbstractHtmlElement;

/**
 * This view helper decides whether to display the "change site" button to the user
 * in the layout or not.  It also caches the current route, along with parameters,
 * so the location selector controller is able to put the user back where they
 * were before they decided to change their VTS location.
 *
 * Class LocationSelector
 */
class LocationSelector extends AbstractHtmlElement
{
    protected $routeMatch;
    protected $locationSelectContainerHelper;

    /**
     * @param LocationSelectContainerHelper $locationSelectContainerHelper
     * @param RouteMatch                    $routeMatch
     */
    public function __construct(LocationSelectContainerHelper $locationSelectContainerHelper, RouteMatch $routeMatch = null)
    {
        $this->routeMatch = $routeMatch;
        $this->locationSelectContainerHelper = $locationSelectContainerHelper;
    }

    /**
     * @return string
     */
    public function __invoke()
    {
        // No route was matched on calling this because the page that your asking for does not exist (404)
        if (null === $this->routeMatch) {
            return '';
        }

        if ($this->routeMatch->getMatchedRouteName() !== 'location-select') {
            $this->locationSelectContainerHelper->persistConfig(
                [
                    'route' => $this->routeMatch->getMatchedRouteName(),
                    'params' => $this->routeMatch->getParams(),
                ]
            );
        }
        if (
            !isset($this->getView()->hideChangeSiteLink)
            && ($this->getView()->getSiteCount() > 1)
            && !$this->getView()->currentMotTest()
        ) {
            $attribs = [
                'id' => 'change-site',
                'href' => $this->getView()->url(LocationSelectController::ROUTE),
            ];
            $attribs = $this->htmlAttribs($attribs);

            return '<div><a '.$attribs.'>Change site</a></div>';
        } else {
            return '';
        }
    }
}
