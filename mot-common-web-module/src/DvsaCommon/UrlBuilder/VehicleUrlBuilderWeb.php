<?php

namespace DvsaCommon\UrlBuilder;

/**
 * Url Builder for web for Vehicle.
 *
 * @deprecated Use the route name directly instead, while using the URL generator helper.
 */
class VehicleUrlBuilderWeb extends AbstractUrlBuilder
{
    const VEHICLE = '/vehicle[/:id]';
    const SEARCH = '/search';
    const SEARCH_RESULT = '/result';
    const HISTORY_MOTTESTS = '/history';

    protected $routesStructure
        = [
            self::VEHICLE =>
                [
                    self::SEARCH                        => '',
                    self::SEARCH_RESULT                 => '',
                    self::HISTORY_MOTTESTS              => '',
                ],
        ];


    public static function vehicle($id = null)
    {
        $url = self::of()->appendRoutesAndParams(self::VEHICLE);

        if ($id !== null) {
            $url->routeParam('id', $id);
        }

        return $url;
    }

    public static function search()
    {
        return self::of()
            ->appendRoutesAndParams(self::VEHICLE)
            ->appendRoutesAndParams(self::SEARCH);
    }

    public static function searchResult()
    {
        return self::of()
            ->appendRoutesAndParams(self::VEHICLE)
            ->appendRoutesAndParams(self::SEARCH_RESULT);
    }

    /**
     * Build url to page with history of any MOT tests
     *
     * @return VehicleUrlBuilderWeb
     */
    public static function historyMotTests($vehicleId)
    {
        return self::vehicle($vehicleId)->appendRoutesAndParams(self::HISTORY_MOTTESTS);
    }
}
