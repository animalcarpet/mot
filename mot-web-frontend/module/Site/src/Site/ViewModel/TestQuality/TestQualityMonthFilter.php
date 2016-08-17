<?php

namespace Site\ViewModel\TestQuality;

use Core\Routing\VtsRouteList;
use Core\ViewModel\MonthFilter\MonthFilter;
use Zend\Mvc\Controller\Plugin\Url;


class TestQualityMonthFilter extends MonthFilter
{
    private $params;
    private $queryParams;

    /** @var Url $url */
    private $url;

    /**
     * @param array $params
     * @param array $queryParams
     * @param Url $url
     */
    public function __construct(array $params, array $queryParams = array(), Url $url)
    {
        $this->params = $params;
        $this->url = $url;
        $this->queryParams = $queryParams;
    }

    /**
     * @param Url $url
     * @param $month
     * @param $year
     * @return mixed
     */
    public function getUrlForMonth(Url $url, $month, $year)
    {
        return $this->url->fromRoute(VtsRouteList::VTS_TEST_QUALITY,
            array_replace($this->params,
                [
                    'month' => $month,
                    'year'  => $year,
                ]
            ),
            $this->queryParams
        );
    }

    public function getUrl()
    {
        return $this->url;
    }
}