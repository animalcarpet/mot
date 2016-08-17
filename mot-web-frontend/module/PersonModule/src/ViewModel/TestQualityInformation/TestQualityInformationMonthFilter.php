<?php

namespace Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation;

use Core\ViewModel\MonthFilter\MonthFilter;
use Dvsa\Mot\Frontend\PersonModule\Routes\PersonProfileRoutes;
use Zend\Mvc\Controller\Plugin\Url;


class TestQualityInformationMonthFilter extends MonthFilter
{
    /** @var PersonProfileRoutes $routes */
    private $routes;
    private $params;

    /** @var Url $url */
    private $url;

    /**
     * @param PersonProfileRoutes $routes
     * @param array $params
     * @param Url $url
     */
    public function __construct(PersonProfileRoutes $routes, array $params, Url $url)
    {
        $this->routes = $routes;
        $this->params = $params;
        $this->url = $url;
    }

    /**
     * @param Url $url
     * @param $month
     * @param $year
     * @return mixed
     */
    public function getUrlForMonth(Url $url, $month, $year)
    {
        return $url->fromRoute($this->routes->getTestQualityRoute(),
            array_replace($this->params,
                [
                    'month' => $month,
                    'year'  => $year,
                ]
            ));
    }


    public function getUrl()
    {
        return $this->url;
    }
}