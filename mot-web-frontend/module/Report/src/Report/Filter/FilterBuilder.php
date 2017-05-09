<?php

namespace Report\Filter;

use DvsaCommon\Constants\SearchParamConst;
use Zend\Navigation\Navigation;
use Zend\Navigation\Page\Uri;
use Zend\Stdlib\Parameters;
use Zend\View\Renderer\PhpRenderer;

/**
 * Class FilterBuilder.
 */
class FilterBuilder
{
    /**
     * @var Navigation
     */
    private $filterPeriodNavigation;
    /**
     * @var array
     */
    private $options;
    /**
     * @var Parameters
     */
    private $queryParams;

    public function setQueryParams(Parameters $params)
    {
        $this->queryParams = $params;

        return $this;
    }

    /**
     * @return Parameters
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Build time period navigation.
     *
     * @param PhpRenderer $phpRenderer
     *
     * @return Navigation
     */
    public function getTimePeriodNavigation(PhpRenderer $phpRenderer)
    {
        if ($this->filterPeriodNavigation !== null) {
            return $this->filterPeriodNavigation;
        }

        $options = $this->getOptions();

        $navigation = new Navigation();

        foreach ($options as $period) {
            $page = new Uri();

            $dateFrom = (new \DateTime('@'.$period['from']))->getTimestamp();
            $dateTo = (new \DateTime('@'.$period['to']))->getTimestamp();

            $urlParams = [
                SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM => $dateFrom,
                SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM => $dateTo,
            ];

            $uri = $phpRenderer->url(null, [], ['query' => $urlParams], true);

            $page
                ->setUri($uri)
                ->setLabel($period['label'])
                ->setActive($this->ifSameDates($this->queryParams, $urlParams));

            $navigation->addPage($page);
        }

        $this->filterPeriodNavigation = $navigation;

        return $this->filterPeriodNavigation;
    }

    public function ifSameDates(Parameters $queryParams, $optionParams)
    {
        $dateFromQuery = $queryParams->get(SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM, false);
        $dateToQuery = $queryParams->get(SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM, false);

        return
            $dateFromQuery && $dateToQuery
            && (int) $dateFromQuery === $optionParams[SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM]
            && (int) $dateToQuery === $optionParams[SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM]
        ;
    }
}
