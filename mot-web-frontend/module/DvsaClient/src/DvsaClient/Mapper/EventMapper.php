<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Dto\Event\EventDto;
use DvsaCommon\Dto\Event\EventListDto;
use DvsaCommon\UrlBuilder\EventUrlBuilder;

/**
 * Class EventMapper.
 */
class EventMapper extends DtoMapper
{
    /**
     * @param int    $id
     * @param string $type
     * @param string $formDto
     *
     * @return EventListDto
     */
    public function getEventList($id, $type, $formDto)
    {
        $url = EventUrlBuilder::of()->eventList($id, $type)->toString();

        return $this->post($url, $formDto);
    }

    /**
     * @param int $id
     *
     * @return EventDto
     */
    public function getEvent($id)
    {
        $url = EventUrlBuilder::of()->event($id)->toString();

        return $this->get($url);
    }

    protected function post($url, $params)
    {
        $response = $this->client->post($url, $params);

        return $this->getHydator()->doHydration($response['data']);
    }
}
