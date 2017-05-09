<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Service;

use Dvsa\Mot\ApiClient\Exception\ResourceNotFoundException;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardOrder;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\ApiClient\Request\OrderSecurityCardRequest;

class SecurityCardService
{
    /**
     * @var AuthorisationService
     */
    private $authorisationServiceClient;

    /**
     * @var TwoFactorNominationNotificationService
     */
    private $nominationService;

    /**
     * @param AuthorisationService                   $authorisationServiceClient
     * @param TwoFactorNominationNotificationService $nominationService
     */
    public function __construct(
        AuthorisationService $authorisationServiceClient,
        TwoFactorNominationNotificationService $nominationService
    ) {
        $this->authorisationServiceClient = $authorisationServiceClient;
        $this->nominationService = $nominationService;
    }

    /**
     * @return \Dvsa\Mot\ApiClient\Resource\Item\SecurityCard
     */
    public function getSecurityCardForUser($username)
    {
        try {
            return $this->authorisationServiceClient->getSecurityCardForUser($username);
        } catch (ResourceNotFoundException $exception) {
            return null;
        }
    }

    public function getSecurityCardOrdersForUser($username)
    {
        return $this->authorisationServiceClient->getSecurityCardOrders($username);
    }

    /**
     * @param string $username
     *
     * @return SecurityCardOrder|null
     */
    public function getMostRecentSecurityCardOrderForUser($username)
    {
        $cardOrderCollection = $this->authorisationServiceClient->getSecurityCardOrders($username);

        if ($cardOrderCollection->getCount() == 0) {
            return null;
        }

        $cardOrders = $cardOrderCollection->getAll();

        usort($cardOrders, function (SecurityCardOrder $a, SecurityCardOrder $b) {
            if ($a->getSubmittedOn() === $b->getSubmittedOn()) {
                return 0;
            }

            return ($a->getSubmittedOn() > $b->getSubmittedOn()) ? -1 : 1;
        });

        return $cardOrders[0];
    }

    /**
     * @param int
     * @param array
     *
     * @return bool
     */
    public function orderNewCard($recipientUsername, $recipientId, array $address)
    {
        $orderSecurityCardRequest = $this->buildRequest($recipientUsername, $address);
        $securityCardOrder = $this->authorisationServiceClient->orderSecurityCard($orderSecurityCardRequest);
        $orderSuccess = !empty($securityCardOrder);

        if ($orderSuccess) {
            $this->nominationService->sendNotificationsForPendingNominations($recipientId);
        }

        return $orderSuccess;
    }

    /**
     * @return OrderSecurityCardRequest
     */
    protected function buildRequest($recipientUsername, array $address)
    {
        $request = new OrderSecurityCardRequest();

        $request->setVtsName($address['vtsName']);
        $request->setRecipientUsername($recipientUsername);
        $request->setAddressLine1($address['address1']);
        $request->setAddressLine2($address['address2']);
        $request->setAddressLine3($address['address3']);
        $request->setTown($address['townOrCity']);
        $request->setPostcode($address['postcode']);

        return $request;
    }
}
