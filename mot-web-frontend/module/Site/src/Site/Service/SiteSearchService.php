<?php

namespace Site\Service;

use DvsaCommon\Dto\Site\SiteListDto;
use DvsaCommon\UrlBuilder\SiteUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;
use Report\Table\Formatter\SubRow;
use Report\Table\Table;
use Report\Table\Formatter\SiteLink;
use Zend\Http\Request;

/**
 * Class SiteSearchService.
 */
class SiteSearchService
{
    /**
     * Replace empty fields with text.
     *
     * @var array fieldname => replacement
     */
    protected static $emptyFields = [
        'roles' => 'None',
    ];

    public function initTable(SiteListDto $result)
    {
        $result = $this->prepareResults($result);

        return (new Table())->setColumns(
            [
                // Site Number + link Column
                [
                    'title' => 'Site',
                    'sortBy' => 'siteNumber',
                    'field' => 'site_number',
                    'formatter' => SiteLink::class,
                ],
                // Site Name and Phone Column
                [
                    'title' => 'Name/phone',
                    'sortBy' => 'siteName',
                    'sub' => [
                        [
                            'field' => 'name',
                        ],
                        [
                            'field' => 'phone',
                            'formatter' => SubRow::class,
                        ],
                    ],
                ],
                // Address Town and Postcode Column
                [
                    'title' => 'City/postcode',
                    'sortBy' => 'siteTownPostcode',
                    'sub' => [
                        [
                            'field' => 'town',
                        ],
                        [
                            'field' => 'postcode',
                            'formatter' => SubRow::class,
                        ],
                    ],
                ],
                // Site Classes Column
                [
                    'title' => 'Classes',
                    'sortBy' => 'siteClasses',
                    'field' => 'roles',
                ],
                // Site Type and Status Column
                [
                    'title' => 'Type/status',
                    'sortBy' => 'siteTypeStatus',
                    'sub' => [
                        [
                            'field' => 'type',
                        ],
                        [
                            'field' => 'status',
                            'formatter' => SubRow::class,
                        ],
                    ],
                ],
            ]
        )
            ->setData($result->getData())
            ->setRowsTotalCount($result->getTotalResultCount())
            ->setSearchParams($result->getSearched());
    }

    /**
     * Build the url with the request.
     *
     * @param SiteUrlBuilderWeb $url
     * @param Request           $request
     *
     * @return string
     */
    public function buildParams(SiteUrlBuilderWeb $url, Request $request)
    {
        return $url.'?'.http_build_query($request->getQuery()->toArray());
    }

    /**
     * Replacing empty fields with default replacements.
     *
     * @param SiteListDto $result
     *
     * @return $this
     */
    protected function prepareResults(SiteListDto $result)
    {
        $resultData = $result->getData();
        if (is_array($resultData)) {
            foreach ($resultData as &$item) {
                foreach (static::$emptyFields as $emptyFieldName => $replacement) {
                    if (ArrayUtils::tryGet($item, $emptyFieldName) == null) {
                        $item[$emptyFieldName] = $replacement;
                    }
                }
            }
        }

        return $result->setData($resultData);
    }
}
