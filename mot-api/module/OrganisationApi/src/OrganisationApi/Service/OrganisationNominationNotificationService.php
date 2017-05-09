<?php

namespace OrganisationApi\Service;

use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use NotificationApi\Dto\Notification;
use NotificationApi\Service\Helper\TwoFactorNotificationTemplateHelper;
use NotificationApi\Service\NotificationService;

/**
 * Send organisation nomination to user.
 */
class OrganisationNominationNotificationService
{
    /** @var NotificationService $notificationService */
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @param Person                                   $nominator
     * @param OrganisationBusinessRoleMap              $nomination
     * @param TwoFactorNotificationTemplateHelper|null $twoFactorNotificationTemplateHelper
     *
     * @return int notificationId
     */
    public function sendConditionalNominationNotification(
        Person $nominator,
        OrganisationBusinessRoleMap $nomination,
        $twoFactorNotificationTemplateHelper
    ) {
        $nominee = $nomination->getPerson();

        $template = Notification::TEMPLATE_ORGANISATION_NOMINATION;
        if ($twoFactorNotificationTemplateHelper instanceof TwoFactorNotificationTemplateHelper) {
            $template = $twoFactorNotificationTemplateHelper->getTemplate($template);
        }

        $data = (new Notification())
            ->setRecipient($nomination->getPerson()->getId())
            ->setTemplate($template)
            ->addField('siteOrOrganisationId', $nomination->getOrganisation()->getAuthorisedExaminer()->getNumber())
            ->addField('organisationName', $nomination->getOrganisation()->getName())
            ->addField('siteName', $nomination->getOrganisation()->getName())
            ->addField('positionName', $nomination->getOrganisationBusinessRole()->getFullName())
            ->addField('nominatorName', $nominator->getDisplayName())
            ->addField('nominatorId', $nominator->getId())
            ->addField('nominationId', $nomination->getId())
            ->addField('role', $nomination->getOrganisationBusinessRole()->getCode())
            ->addField('username', $nominee->getUsername())
            ->toArray();

        return $this->notificationService->add($data);
    }

    /**
     * @param Person                      $nominator
     * @param OrganisationBusinessRoleMap $nomination
     *
     * @return int
     */
    public function sendDirectNominationNotification(Person $nominator, OrganisationBusinessRoleMap $nomination)
    {
        $data = (new Notification())
            ->setRecipient($nomination->getPerson()->getId())
            ->setTemplate(Notification::TEMPLATE_ORGANISATION_NOMINATION_GIVEN)
            ->addField('siteOrOrganisationId', $nomination->getOrganisation()->getAuthorisedExaminer()->getNumber())
            ->addField('organisationName', $nomination->getOrganisation()->getName())
            ->addField('positionName', $nomination->getOrganisationBusinessRole()->getFullName())
            ->toArray();

        return $this->notificationService->add($data);
    }
}
