<?php

namespace Core\Action;

use Zend\View\Helper\Url;

class RedirectToRoute extends AbstractRedirectActionResult
{
    private $routeName;

    private $routeParams = [];

    private $queryParams = [];

    public function __construct($routeName, $routeParams = [], $queryParams = [])
    {
        $this->routeName = $routeName;
        $this->routeParams = $routeParams;
        $this->queryParams = $queryParams;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function getRouteParams()
    {
        return $this->routeParams;
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function toString(Url $url)
    {
        return $url->__invoke($this->getRouteName(), $this->getRouteParams(), ['query' => $this->getQueryParams()]);
    }
}
