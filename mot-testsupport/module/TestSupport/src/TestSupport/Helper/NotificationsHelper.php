<?php

namespace TestSupport\Helper;

use TestSupport\Model\Account;
use DvsaCommon\UrlBuilder\NotificationUrlBuilder;

class NotificationsHelper
{
    const SITE_NOMINATION_ACCEPTED = 'SITE-NOMINATION-ACCEPTED';
    const SITE_NOMINATION_REJECTED = 'SITE-NOMINATION-REJECTED';

    /**
     * @var TestSupportRestClientHelper
     */
    private $testSupportRestClientHelper;

    /**
     * @param TestSupportRestClientHelper $testSupportRestClientHelper
     */
    public function __construct(TestSupportRestClientHelper $testSupportRestClientHelper)
    {
        $this->testSupportRestClientHelper = $testSupportRestClientHelper;
    }

    /**
     * Return a list of notifications for any given Account.
     *
     * @param Account $account
     *
     * @return array
     *
     * @throws \Exception if the API responded in a unexpected fashion
     */
    public function getNotifications(Account $account)
    {
        $restClient = $this->testSupportRestClientHelper->getJsonClient([]);
        $response = $restClient->get(
            NotificationUrlBuilder::of()
                ->notificationForPerson()
                ->routeParam('personId', $account->getPersonId())
                ->toString()
        );
        if (!isset($response['data'])) {
            throw new \Exception('Bad response from Rest Client');
        }

        return $response['data'];
    }

    /**
     * Accept the notifications for the account with option to only accept a certain template type.
     *
     * @param Account  $account
     * @param array    $notifications
     * @param int|null $templateIdFilter
     *
     * @throws \Exception
     */
    public function acceptUnreadNotification(Account $account, array $notifications, $templateIdFilter = null)
    {
        $this->processUnreadNotifications($account, $notifications, self::SITE_NOMINATION_ACCEPTED, $templateIdFilter);
    }

    /**
     * Reject the notifications for the account with option to only accept a certain template type.
     *
     * @param Account $account
     * @param array   $notifications
     * @param null    $templateIdFilter
     *
     * @throws \Exception
     */
    public function rejectUnreadNotifications(Account $account, array $notifications, $templateIdFilter = null)
    {
        $this->processUnreadNotifications($account, $notifications, self::SITE_NOMINATION_REJECTED, $templateIdFilter);
    }

    /**
     * Process any unread notifications marking them with the relevant action.
     *
     * @param Account  $account
     * @param array    $notifications
     * @param string   $action
     * @param null|int $templateIdFilter
     *
     * @throws \Exception if the rest client returns an unexpected response
     */
    private function processUnreadNotifications(Account $account, array $notifications, $action, $templateIdFilter = null)
    {
        $restClient = $this->testSupportRestClientHelper->getJsonClient(
            [
                'requestor' => [
                    'username' => $account->getUsername(),
                    'password' => $account->getPassword(),
                ],
            ]
        );

        foreach ($notifications as $notification) {

            // I think this is a 'read' flag, if this notification has already been read skip it
            if (!empty($notification['readOn'])) {
                continue;
            }

            // Optionally only process notifications matching a certain template
            if (isset($templateIdFilter) && $templateIdFilter != $notification['templateId']) {
                continue;
            }

            $actionPath = NotificationUrlBuilder::of()->notification($notification['id'])->action()->toString();
            $return = $restClient->put($actionPath, ['action' => $action]);

            if (!isset($return['data']) && $return['data'] === true) {
                throw new \Exception('Failed to action notification id '.$notification['id']);
            }
        }
    }
}
