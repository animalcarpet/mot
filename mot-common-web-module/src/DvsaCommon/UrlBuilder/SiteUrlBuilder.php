<?php

namespace DvsaCommon\UrlBuilder;

/**
 * Url for SiteApi
 */
class SiteUrlBuilder extends UrlBuilder
{
    const SITE = 'site/:id';
    const POSITION = '/position[/:positionId]';
    const USAGE = '/slot-usage';
    const USAGE_PERIOD_DATA = '/period-data';
    const VALIDATE = '/position-validate';
    const EVENT = '/event';

    protected $routesStructure
        = [
            self::SITE => [
                self::EVENT => '',
                self::POSITION => '',
                self::USAGE => [
                    self::USAGE_PERIOD_DATA => '',
                ],
                self::VALIDATE => ''
            ],
        ];

    public static function site($id)
    {
        return (new self())->appendRoutesAndParams(self::SITE)->routeParam('id', $id);
    }

    public function createEvent()
    {
        return $this->appendRoutesAndParams(self::EVENT);
    }

    public function position()
    {
        return $this->appendRoutesAndParams(self::POSITION);
    }

    public function usage()
    {
        return $this->appendRoutesAndParams(self::USAGE);
    }

    public function periodData()
    {
        return $this->appendRoutesAndParams(self::USAGE_PERIOD_DATA);
    }

    public function validate()
    {
        return $this->appendRoutesAndParams(self::VALIDATE);
    }

    public function getVts($id)
    {
        return $this->appendRoutesAndParams(self::VEHICLE_TESTING_STATION)->routeParam('id', $id);

    }
}
