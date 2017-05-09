<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service;

use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\UrlBuilder\EventUrlBuilder;
use Core\Service\MotFrontendIdentityProvider;

class OrderSecurityCardEventService
{
    private $jsonClient;

    private $identityProvider;

    /**
     * @param HttpRestJsonClient $jsonClient
     */
    public function __construct(HttpRestJsonClient $jsonClient,
                                MotFrontendIdentityProvider $identityProvider,
                                DateTimeHolder $dateTimeHolder)
    {
        $this->jsonClient = $jsonClient;
        $this->identityProvider = $identityProvider;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function createEvent($recipientId, $address)
    {
        $url = EventUrlBuilder::of()->addPersonEvent($recipientId)->toString();
        $identity = $this->identityProvider->getIdentity();
        $postData = $this->formatApiRequest($identity->getUsername(), $address);

        try {
            $response = $this->jsonClient->post($url, $postData);

            return true;
        } catch (GeneralRestException $e) {
            return false;
        }
    }

    private function formatApiRequest($username, $address)
    {
        $date = $this->dateTimeHolder->getUserCurrent();

        return [
            'eventTypeCode' => EventTypeCode::CREATE_SECURITY_CARD_ORDER,
            'description' => 'Security card ordered by '.$username.' at '.$date->format('g:ia').' to '.$address,
        ];
    }
}
