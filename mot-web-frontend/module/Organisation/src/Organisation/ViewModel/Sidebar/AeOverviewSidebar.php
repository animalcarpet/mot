<?php
namespace Organisation\ViewModel\Sidebar;

use Core\Routing\AeRouteList;
use Core\ViewModel\Sidebar\GeneralSidebar;
use Core\ViewModel\Sidebar\GeneralSidebarLink;
use Core\ViewModel\Sidebar\GeneralSidebarLinkList;
use Core\ViewModel\Sidebar\GeneralSidebarStatusBox;
use Core\ViewModel\Sidebar\GeneralSidebarStatusItem;
use Core\ViewModel\Sidebar\SidebarBadge;
use Core\ViewModel\Sidebar\SidebarButton;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\AuthorisationForAuthorisedExaminerStatusCode;
use Event\Controller\EventController;
use Organisation\Authorisation\AuthorisedExaminerViewAuthorisation;
use Organisation\Controller\MotTestLogController;
use SlotFinance\Controller\AdjustmentController;
use SlotFinance\Controller\Amendment\RefundController;
use SlotFinance\Controller\Purchase\IndexController as FinancePurchaseController;
use SlotPurchase\Controller\DirectDebit\AbstractDirectDebitController;
use SlotPurchase\Controller\Journey\AbstractJourneyController;
use SlotPurchase\Controller\Report\SlotUsageController;
use SlotPurchase\Controller\Report\TransactionHistoryController;
use SlotPurchase\Service\DirectDebitService;
use Zend\Mvc\Controller\Plugin\Url;

class AeOverviewSidebar extends GeneralSidebar
{
    /**
     * @var AuthorisedExaminerViewAuthorisation
     */
    private $authorisationForView;

    /**
     * @var string
     */
    private $url;

    /** @var OrganisationDto */
    private $organisation;

    /** @var  GeneralSidebarLinkList */
    private $relatedLinks;

    /** @var  GeneralSidebarLinkList */
    private $slotsLinks;

    public function __construct(
        OrganisationDto $organisation,
        AuthorisedExaminerViewAuthorisation $authorisationForView,
        Url $url,
        DirectDebitService $directDebitService
    ) {
        $this->directDebitService = $directDebitService;
        $this->url = $url;
        $this->authorisationForView = $authorisationForView;
        $this->organisation = $organisation;

        $this->setUp();
    }

    private function setUp()
    {
        $this->buildStatusBox();
        $this->buildBuySlotsSection();

        $slotsLinks = $this->buildSlotsLinks();
        if(!empty($slotsLinks)) {
            $this->addItem($slotsLinks);
        }

        $relatedLinks = $this->buildRelatedLinks();
        if (!empty($relatedLinks)) {
            $this->addItem($relatedLinks);
        }
    }

    private function buildStatusBox()
    {
        $statusBox = new GeneralSidebarStatusBox();

        if ($this->authorisationForView->canViewAeStatus()) {
            $aeAuth = $this->organisation->getAuthorisedExaminerAuthorisation();
            $aeAuthStatus = $aeAuth->getStatus();
            if (!empty($aeAuth->getStatusChangedOn())) {
                $statusChangeDate = DateTimeDisplayFormat::textDate($aeAuth->getStatusChangedOn());
            } else {
                $statusChangeDate = "N/A";
            }

            $badge = $this->badgeForVtsStatus($aeAuthStatus->getCode());

            $statusBox->addItem(new GeneralSidebarStatusItem("ae-status", "Status", $aeAuthStatus->getName(),
                $badge, $statusChangeDate));
        }

        if ($this->authorisationForView->canViewSlotBalance()) {
            $badge = $this->organisation->getSlotBalance() > 0 ? SidebarBadge::info(): SidebarBadge::normal();

            $statusBox->addItem(new GeneralSidebarStatusItem("slot-count", "Slots",
                number_format($this->organisation->getSlotBalance(), 0, '.', ','), $badge));
        }

        $this->addItem($statusBox);
    }

    private function buildBuySlotsSection()
    {
        $list = new GeneralSidebarLinkList('');

        if ($this->authorisationForView->canSettlePayment()) {
            $url = $this->url->fromRoute(FinancePurchaseController::ROUTE_NAME_PURCHASE_TYPE,
                ['organisationId' => $this->organisation->getId()]);
        } elseif ($this->authorisationForView->canBuySlots()) {
            $url = $this->url->fromRoute(AbstractJourneyController::ROUTE_NAME_START,
                ['organisationId' => $this->organisation->getId()]);
        }

        if (!empty($url)) {
            $list->addLink(new SidebarButton("add-slots", "Buy slots", $url));
        }

        $directDebitLink = $this->buildDirectDebitLink();

        if ($directDebitLink) {
            $list->addLink($directDebitLink);
        }

        if (!empty($list->getLinks())) {
            $this->addItem($list);
        }
    }

    private function buildDirectDebitLink()
    {
        if (!$this->directDebitService->isMandateSetupInProgress($this->organisation->getId())) {
            if ($this->directDebitService->isMandateSetup($this->organisation->getId())) {
                if ($this->authorisationForView->canManageDirectDebit()) {
                    $link = new GeneralSidebarLink('manageDirectDebit', 'Manage Direct Debit',
                        $this->url->fromRoute(AbstractDirectDebitController::ROUTE_NAME_MANAGE, [
                            'organisationId' => $this->organisation->getId()
                        ]));
                }
            } elseif ($this->authorisationForView->canSetupDirectDebit()) {
                $link = new GeneralSidebarLink('setupDirectDebit', 'Setup Direct Debit',
                    $this->url->fromRoute(AbstractDirectDebitController::ROUTE_NAME_START, [
                        'organisationId' => $this->organisation->getId()
                    ]));
            }

            if (!empty($link)) {
                return $link;
            }
        }

        return null;
    }

    private function buildSlotsLinks()
    {
        if ($this->authorisationForView->canViewTransactionHistory()) {
            $this->getSlotsLinks()->addLink(
                new GeneralSidebarLink("transaction-history",
                    "Transaction history",
                    $this->url->fromRoute(TransactionHistoryController::ROUTE_NAME,
                        ['organisationId' => $this->organisation->getId()])
                )
            );
        }

        if ($this->authorisationForView->canViewSlotUsage()) {
            $this->getSlotsLinks()->addLink(
                new GeneralSidebarLink("slot-usage",
                    "Slot usage",
                    $this->url->fromRoute(SlotUsageController::ROUTE_NAME,
                        ['organisationId' => $this->organisation->getId()])
                )
            );
        }

        if ($this->authorisationForView->canAdjustSlotBalance()) {
            $this->getSlotsLinks()->addLink(
                new GeneralSidebarLink("slot-adjustment",
                    "Slot adjustment",
                    $this->url->fromRoute(AdjustmentController::ROUTE_NAME_START,
                        ['organisationId' => $this->organisation->getId()])
                )
            );
        }

        if ($this->authorisationForView->canRefund()) {
            $this->getSlotsLinks()->addLink(
                new GeneralSidebarLink("slots-refund",
                    "Refund slots",
                    $this->url->fromRoute(RefundController::ROUTE_NAME,
                        ['organisationId' => $this->organisation->getId()])
                )
            );
        }

        return $this->slotsLinks;
    }

    private function buildRelatedLinks()
    {
        if ($this->authorisationForView->canViewTestLogs()) {
            $this->getRelatedLinks()->addLink(
                new GeneralSidebarLink("test-log",
                    "Test logs",
                    $this->url->fromRoute(MotTestLogController::ROUTE_INDEX, ['id' => $this->organisation->getId()])
                )
            );
        }

        if ($this->authorisationForView->canViewEventHistory()) {
            $this->getRelatedLinks()->addLink(
                new GeneralSidebarLink("event-history",
                    "Event history",
                    $this->url->fromRoute(EventController::ROUTE_LIST, [
                        'id' => $this->organisation->getId(),
                        'type' => EventController::TYPE_AE,
                    ])
                )
            );
        }

        if ($this->authorisationForView->canViewAETestQualityInformation()) {
            $this->getRelatedLinks()->addLink(
                new GeneralSidebarLink("test-quality-information",
                    "Test quality information",
                    $this->url->fromRoute(AERouteList::AE_TEST_QUALITY, ['id' => $this->organisation->getId()])
                )
            );
        }

        return $this->relatedLinks;
    }

    private function getRelatedLinks()
    {
        if (empty($this->relatedLinks)) {
            $this->relatedLinks = new GeneralSidebarLinkList("Related");
        }

        return $this->relatedLinks;
    }

    private function getSlotsLinks()
    {
        if (empty($this->slotsLinks)) {
            $this->slotsLinks = new GeneralSidebarLinkList("Slots");
        }

        return $this->slotsLinks;
    }

    private function badgeForVtsStatus($statusCode)
    {
        switch ($statusCode) {
            case AuthorisationForAuthorisedExaminerStatusCode::APPLIED:
                return SidebarBadge::normal();
            case AuthorisationForAuthorisedExaminerStatusCode::APPROVED:
                return SidebarBadge::success();
            case AuthorisationForAuthorisedExaminerStatusCode::LAPSED:
                return SidebarBadge::normal();
            case AuthorisationForAuthorisedExaminerStatusCode::REJECTED:
                return SidebarBadge::alert();
            case AuthorisationForAuthorisedExaminerStatusCode::RETRACTED:
                return SidebarBadge::normal();
            case AuthorisationForAuthorisedExaminerStatusCode::SURRENDERED:
                return SidebarBadge::normal();
            case AuthorisationForAuthorisedExaminerStatusCode::WITHDRAWN:
                return SidebarBadge::normal();
            case AuthorisationForAuthorisedExaminerStatusCode::UNKNOWN:
                return SidebarBadge::normal();
            default:
                return SidebarBadge::normal();
        }

    }
}