<?php

namespace Dvsa\Mot\Behat\Support\Api;

class Event extends MotApi
{
    const PATH_PERSON_LIST = 'event/list/person/{personId}';
    const PATH_SITE_LIST = 'event/list/site/{siteId}';
    const PATH_ORGANISATION_LIST = 'event/list/ae/{organisationId}';
    const PATH_CREATE_EVENT_PERSON = 'person/{id}/event';
    const PATH_CREATE_EVENT_SITE = 'site/{id}/event';
    const PATH_CREATE_EVENT_ORGANISATION = 'organisation/{id}/event';
    const PATH_CREATE_NON_MANUAL_EVENT = 'event/add/person/{personId}';

    private $params = [
        "dateFrom" => [
            "date" => null,
            "day" => null,
            "month" => null,
            "year" => null,
            "_class" => "DvsaCommon\\Dto\\Common\\DateDto"
        ],
        "dateTo" => [
            "date" => null,
            "day" => null,
            "month" => null,
            "year" => null,
            "_class" => "DvsaCommon\\Dto\\Common\\DateDto"
        ],
        "search" => "",
        "isShowDate" => false,
        "pageNumber" => null,
        "displayStart" => 0,
        "displayLength" => 10,
        "sortCol" => 1,
        "sortDir" => "DESC",
        "_class" => "DvsaCommon\\Dto\\Event\\EventFormDto"
    ];

    public function getPersonEventsData($token, $personId)
    {
        $path = str_replace("{personId}", $personId, self::PATH_PERSON_LIST);

        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            $path,
            $this->params
        );
    }

    public function getSiteEventsData($token, $siteId)
    {
        $path = str_replace("{siteId}", $siteId, self::PATH_SITE_LIST);

        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            $path,
            $this->params
        );
    }

    public function getOrganisationEventsData($token, $organisationId)
    {
        $path = str_replace("{organisationId}", $organisationId, self::PATH_ORGANISATION_LIST);

        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            $path,
            $this->params
        );
    }

    public function postNonManualEvent($token, $id, $data)
    {
        $path = str_replace("{personId}", $id, self::PATH_CREATE_NON_MANUAL_EVENT);

        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            $path,
            $data
        );
    }

    public function postEvent($token, $category, $id, $data)
    {
        $url = null;
        switch ($category) {
            case 'NT':
                $url = self::PATH_CREATE_EVENT_PERSON;
                break;
            case 'AE':
                $url = self::PATH_CREATE_EVENT_ORGANISATION;
                break;
            case 'VTS':
                $url = self::PATH_CREATE_EVENT_SITE;
                break;
        }
        if (null === $url) {
            throw new \Exception('No valid url, supplied :'.$category);
        }
        $path = str_replace("{id}", $id, $url);

        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            $path,
            $data
        );
    }
    
}