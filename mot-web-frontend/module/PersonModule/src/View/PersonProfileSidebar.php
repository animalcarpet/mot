<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModule\View;

use Core\ViewModel\Badge\Badge;
use Core\ViewModel\Sidebar\GeneralSidebar;
use Core\ViewModel\Sidebar\GeneralSidebarItemInterface;
use Core\ViewModel\Sidebar\GeneralSidebarLink;
use Core\ViewModel\Sidebar\GeneralSidebarLinkList;
use Core\ViewModel\Sidebar\GeneralSidebarStatusBox;
use Core\ViewModel\Sidebar\GeneralSidebarStatusItem;
use Dvsa\Mot\Frontend\PersonModule\Routes\PersonProfileRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuard;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Model\TesterAuthorisation;
use Event\Controller\EventController;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * PersonProfile Sidebar.
 */
class PersonProfileSidebar extends GeneralSidebar
{
    const USER_PROFILE_URL = '/your-profile/';
    const USER_ADMIN_PROFILE_URL = 'user-admin/user/';

    /** @var int */
    private $personId;

    /** @var PersonProfileGuard */
    private $personProfileGuard;

    /** @var TesterAuthorisation */
    private $testerAuthorisation;

    /** @var string */
    private $currentUrl;

    /** @var Url */
    private $urlPlugin;

    /** @var PersonProfileRoutes */
    private $personProfileRoutes;

    /** @var bool */
    private $isTwoFactorAuthEnabled;

    /** @var bool */
    private $canOrderSecurityCard;

    /** @var bool */
    private $hasSecurityCardOrders;

    /** @var bool */
    private $hasDeactivated2FaCard;

    /** @var bool */
    private $isAuthenticatedWithLostAndForgotten;

    /**
     * @param $personId
     * @param PersonProfileGuard  $personProfileGuard
     * @param TesterAuthorisation $testerAuthorisation
     * @param $currentUrl
     * @param PersonProfileRoutes $personProfileRoutes
     * @param Url                 $urlPlugin
     * @param $isTwoFactorAuthEnabled
     * @param $canOrderSecurityCard
     * @param $displayResetAccountByEmailButton
     */
    public function __construct(
        $personId,
        PersonProfileGuard $personProfileGuard,
        TesterAuthorisation $testerAuthorisation,
        $currentUrl,
        PersonProfileRoutes $personProfileRoutes,
        Url $urlPlugin,
        $isTwoFactorAuthEnabled,
        $canOrderSecurityCard,
        $hasSecurityCardOrders,
        $hasDeactivated2FaCard,
        $isAuthenticatedWithLostAndForgotten,
        $displayResetAccountByEmailButton
    ) {
        $this->personId = $personId;
        $this->personProfileGuard = $personProfileGuard;
        $this->testerAuthorisation = $testerAuthorisation;
        $this->currentUrl = $currentUrl;
        $this->isTwoFactorAuthEnabled = $isTwoFactorAuthEnabled;
        $this->canOrderSecurityCard = $canOrderSecurityCard;
        $this->personProfileRoutes = $personProfileRoutes;
        $this->urlPlugin = $urlPlugin;
        $this->hasSecurityCardOrders = $hasSecurityCardOrders;
        $this->hasDeactivated2FaCard = $hasDeactivated2FaCard;
        $this->isAuthenticatedWithLostAndForgotten = $isAuthenticatedWithLostAndForgotten;

        $this->setUpStatusBox();
        $this->setUpAccountSecurityBox();
        $this->setUpAccountManagementBox($displayResetAccountByEmailButton);
        $this->setUpRelatedLinksSection();
        $this->setUpQualificationsAndAnnualAssessmentSection();
    }

    /**
     * @param string $id of sidebar item to check for
     *
     * @return bool
     */
    public function hasItem($id)
    {
        $getId = function (GeneralSidebarItemInterface $item) {
            return $item->getId();
        };

        return in_array($id, array_map($getId, $this->getSidebarItems()));
    }

    /**
     * @param $code
     *
     * @return Badge CSS class modifier for status badge
     */
    private function getQualificationStatusModifier($code)
    {
        switch ($code) {
            case AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED:
                return Badge::normal();
                break;
            case AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED:
                return Badge::warning();
                break;
            case AuthorisationForTestingMotStatusCode::QUALIFIED:
                return Badge::success();
                break;
            case AuthorisationForTestingMotStatusCode::SUSPENDED:
                return Badge::alert();
                break;
            default:
                return Badge::normal();
        }
    }

    private function setUpStatusBox()
    {
        $statusBox = new GeneralSidebarStatusBox();
        $statusBox->setId('qualification_status');

        if (true === $this->personProfileGuard->shouldDisplayTesterQualificationStatusBox()) {
            $statusBox->addItem(new GeneralSidebarStatusItem(
                'group-a',
                'Group A',
                $this->testerAuthorisation->getGroupAStatus()->getName(),
                $this->getQualificationStatusModifier($this->testerAuthorisation->getGroupAStatus()->getCode()),
                'Class 1 and 2'));
            $statusBox->addItem(new GeneralSidebarStatusItem(
                'group-b',
                'Group B',
                $this->testerAuthorisation->getGroupBStatus()->getName(),
                $this->getQualificationStatusModifier($this->testerAuthorisation->getGroupBStatus()->getCode()),
                'Class 3, 4, 5 and 7'));
        }

        $this->addItem($statusBox);
    }

    private function setUpAccountSecurityBox()
    {
        if (!$this->personProfileGuard->canViewAccountSecurity()) {
            return;
        }

        $changePasswordUrl = self::USER_PROFILE_URL.'change-password';
        $changeSecurityQuestionsUrl = self::USER_PROFILE_URL.'change-security-questions';
        $resetPinUrl = self::USER_PROFILE_URL.'security-question';

        $accountSecurityBox = new GeneralSidebarLinkList('Account security');
        $accountSecurityBox->setId('account_security');
        $accountSecurityBox->addLink(new GeneralSidebarLink('change-password', 'Change your password', $changePasswordUrl));
        $accountSecurityBox->addLink(new GeneralSidebarLink('change-security-questions', 'Change your security questions', $changeSecurityQuestionsUrl));

        if ($this->isTwoFactorAuthEnabled) {
            if ($this->canOrderSecurityCard) {
                $accountSecurityBox->addLink(
                    new GeneralSidebarLink('security-card-order', 'Order a security card', 'security-card-order/new'));
            } elseif ($this->personProfileGuard->isExpectedToRegisterForTwoFactorAuth($this->hasSecurityCardOrders,
                $this->hasDeactivated2FaCard, $this->isAuthenticatedWithLostAndForgotten)) {
                $accountSecurityBox->addLink(
                    new GeneralSidebarLink('register-card', 'Activate your security card', '/register-card'));
            }
        }

        $this->addItem($accountSecurityBox);
    }

    private function setUpAccountManagementBox($displayResetByEmailButton = true)
    {
        if (!$this->personProfileGuard->canViewAccountManagement()) {
            return;
        }

        $accountManagementBox = new GeneralSidebarLinkList('Account management');
        $accountManagementBox->setId('account_management');

        if (true === $this->personProfileGuard->canResetAccount()) {
            if (true === $displayResetByEmailButton) {
                $accountManagementBox->addLink(
                    new GeneralSidebarLink(
                        'reset-by-email',
                        'Reset account security by email',
                        '/'.self::USER_ADMIN_PROFILE_URL.$this->personId.'/claim-reset',
                        'related-button--warning'
                    )
                );
                $accountManagementBox->addLink(
                    new GeneralSidebarLink(
                        'reset-by-post',
                        'Reset account by post',
                        '/'.self::USER_ADMIN_PROFILE_URL.$this->personId.'/password-reset', '', 'or '
                    )
                );
            }
            if (false === $displayResetByEmailButton) {
                $accountManagementBox->addLink(
                    new GeneralSidebarLink(
                        'reset-by-post',
                        'Reset account by post',
                        '/'.self::USER_ADMIN_PROFILE_URL.$this->personId.'/password-reset', ''
                    )
                );
            }
        }

        if (true === $this->personProfileGuard->canSendUserIdByPost()) {
            $accountManagementBox->addLink(
                new GeneralSidebarLink(
                    'id-by-post',
                    'Send User ID by post',
                    '/'.self::USER_ADMIN_PROFILE_URL.$this->personId.'/username-recover'
                )
            );
        }

        if (true === $this->personProfileGuard->canSendPasswordResetByPost()) {
            $accountManagementBox->addLink(
                new GeneralSidebarLink(
                    'password-by-post',
                    'Send password reset by post',
                    '/'.self::USER_ADMIN_PROFILE_URL.$this->personId.'/claim-reset/post'
                )
            );
        }

        if ($this->isTwoFactorAuthEnabled && true === $this->personProfileGuard->canOrderSecurityCardForAnotherPerson()) {
            $accountManagementBox->addLink(
                new GeneralSidebarLink(
                    'management-order-card',
                    'Order Security Card',
                    '/security-card-order/new/'.$this->personId
                )
            );
        }

        $this->addItem($accountManagementBox);
    }

    private function setUpQualificationsAndAnnualAssessmentSection()
    {
        $qualificationDetailsUrl = $this->currentUrl.'/qualification-details';

        $annualAssessmentCertificatesUrl = $this->currentUrl.'/annual-assessment-certificates';

        $relatedBox = new GeneralSidebarLinkList('MOT training and certificates');
        $relatedBox->setId('qualifications');

        if ($this->personProfileGuard->canViewQualificationDetails()) {
            $relatedBox->addLink(
                new GeneralSidebarLink(
                    'qualification-details',
                    'MOT tester training certificates',
                    $qualificationDetailsUrl
                )
            );
        }

        if ($this->personProfileGuard->canViewAnnualAssessmentCertificates()) {
            $relatedBox->addLink(
                new GeneralSidebarLink(
                    'annual-assessment-certificates',
                    'Annual assessment certificates',
                    $annualAssessmentCertificatesUrl
                )
            );
        }

        if (!empty($relatedBox->getLinks())) {
            $this->addItem($relatedBox);
        }
    }

    private function setUpRelatedLinksSection()
    {
        $changeQualificationStatusUrl = $this->currentUrl.'/change-qualification-status/';
        $changeGroupAQualificationUrl = $changeQualificationStatusUrl.'A';
        $changeGroupBQualificationUrl = $changeQualificationStatusUrl.'B';

        $rolesAndAssociationsUrl = $this->currentUrl.'/trade-roles';

        $internalRolesUrl = $this->currentUrl.'/manage-internal-role';

        $testQualityInformationUrl = $this->currentUrl.
                sprintf(
                    '/test-quality-information/%s',
                    DateUtils::subtractCalendarMonths(
                        DateUtils::toUserTz(DateUtils::firstOfThisMonth()), 1)
                        ->format('m/Y'));

        $relatedBox = new GeneralSidebarLinkList('Related');
        $relatedBox->setId('related');

        if (true === $this->personProfileGuard->canViewTradeRoles()) {
            $relatedBox->addLink(
                new GeneralSidebarLink(
                    'roles-and-associations',
                    'Roles and associations',
                    $rolesAndAssociationsUrl
                )
            );
        }

        if (true === $this->personProfileGuard->canManageDvsaRoles()) {
            $relatedBox->addLink(
                new GeneralSidebarLink(
                    'manage-roles',
                    'Manage roles',
                    $internalRolesUrl
                )
            );
        }

        if (true === $this->personProfileGuard->canChangeTesterQualificationStatus()) {
            $relatedBox->addLink(
                new GeneralSidebarLink(
                    'change-group-a-qualification',
                    'Change Group A qualification status',
                    $changeGroupAQualificationUrl
                )
            );
            $relatedBox->addLink(
                new GeneralSidebarLink(
                    'change-group-b-qualification',
                    'Change Group B qualification status',
                    $changeGroupBQualificationUrl
                )
            );
        }

        if (true === $this->personProfileGuard->canViewEventHistory()) {
            $relatedBox->addLink(
                new GeneralSidebarLink(
                    'event-history',
                    'Event history',
                    sprintf('%s%s?%s=%s', '/event/list/person/', $this->personId,
                        EventController::PERSON_PROFILE_GO_BACK_PARAMETER, urlencode($this->currentUrl))
                )
            );
        }

        if ($this->personProfileGuard->canViewTestLogs()) {
            $relatedBox->addLink(
                new GeneralSidebarLink(
                    'test-logs',
                    'Test logs',
                    $this->urlPlugin->fromRoute($this->personProfileRoutes->getTestLogsRoute(),
                        ['id' => $this->personId]
                    )
                )
            );
        }

        if ($this->personProfileGuard->canViewTestQuality()) {
            $relatedBox->addLink(
                new GeneralSidebarLink(
                    'test-quality-information',
                    'Test quality information',
                    $testQualityInformationUrl
                )
            );
        }

        if (!empty($relatedBox->getLinks())) {
            $this->addItem($relatedBox);
        }
    }
}
