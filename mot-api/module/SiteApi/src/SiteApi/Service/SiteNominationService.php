<?php

namespace SiteApi\Service;

use DvsaAuthentication\Service\TwoFactorStatusService;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaFeature\FeatureToggles;
use NotificationApi\Dto\Notification;
use NotificationApi\Service\Helper\TwoFactorNotificationTemplateHelper;
use NotificationApi\Service\NotificationService;

/**
 * Send site nomination to user.
 */
class SiteNominationService
{
    /** @var NotificationService $notificationService */
    private $notificationService;

    /** @var TwoFactorStatusService $twoFactorStatusService */
    private $twoFactorStatusService;

    /** @var FeatureToggles */
    private $featureToggles;

    public function __construct(
        NotificationService $notificationService,
        TwoFactorStatusService $twoFactorStatusService,
        FeatureToggles $featureToggles
    ) {
        $this->notificationService = $notificationService;
        $this->twoFactorStatusService = $twoFactorStatusService;
        $this->featureToggles = $featureToggles;
    }

    /**
     * @param Person              $nominator
     * @param SiteBusinessRoleMap $nomination
     *
     * @return int notificationId
     */
    public function sendNomination(Person $nominator, SiteBusinessRoleMap $nomination)
    {
        $role = $nomination->getSiteBusinessRole()->getName();
        $nominee = $nomination->getPerson();

        $templateHelper = $this->getTwoFactorNotificationTemplateHelper($nominee);
        $template = $templateHelper->getTemplate(Notification::TEMPLATE_SITE_NOMINATION);

        $data = (new Notification())
            ->setRecipient($nomination->getPerson()->getId())
            ->setTemplate($template)
            ->addField('siteOrOrganisationId', $nomination->getSite()->getSiteNumber())
            ->addField('siteName', $nomination->getSite()->getName())
            ->addField('positionName', $role)
            ->addField('nominatorName', $nominator->getDisplayName())
            ->addField('nominatorId', $nominator->getId())
            ->addField('nominationId', $nomination->getId())
            ->addField('role', $this->getFacadeRoleName($role))
            ->addField('username', $nominee->getUsername())
            ->toArray();

        return $this->notificationService->add($data);
    }

    private function getFacadeRoleName($role)
    {
        $facadeRoles = [
            'Site manager' => SiteBusinessRoleCode::SITE_MANAGER,
            'Site admin' => SiteBusinessRoleCode::SITE_ADMIN,
            'Tester' => SiteBusinessRoleCode::TESTER,
        ];

        if (false === array_key_exists($role, $facadeRoles)) {
            throw new BadRequestException(
                'You cannot assign this role '.$role,
                BadRequestException::ERROR_CODE_BUSINESS_FAILURE
            );
        }

        return $facadeRoles[$role];
    }

    private function getTwoFactorNotificationTemplateHelper(Person $nominee)
    {
        $nomineeTwoFactorStatus = $this->twoFactorStatusService->getStatusForPerson($nominee);
        $isTwoFactorToggleEnabled = $this->featureToggles->isEnabled(FeatureToggle::TWO_FA);

        return TwoFactorNotificationTemplateHelper::forPendingConditionalNomination(
            $nomineeTwoFactorStatus,
            $isTwoFactorToggleEnabled
        );
    }
}
