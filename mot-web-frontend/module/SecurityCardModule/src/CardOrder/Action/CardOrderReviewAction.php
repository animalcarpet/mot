<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action;

use Application\Data\ApiPersonalDetails;
use Core\Action\ViewActionResult;
use Core\Action\RedirectToRoute;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardEventService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardNotificationService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\ViewModel\CardOrderReviewViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Zend\Http\Request;

class CardOrderReviewAction
{
    const REVIEW_PAGE_TITLE = 'Review delivery address';
    const REVIEW_PAGE_SUBTITLE = 'Order a security card';

    /**
     * @var OrderNewSecurityCardSessionService
     */
    private $sessionService;

    /**
     * @var ApiPersonalDetails
     */
    private $apiPersonalDetails;

    /**
     * @var SecurityCardService
     */
    private $securityCardService;

    /**
     * @var OrderSecurityCardStepService
     */
    private $stepService;

    /**
     * @var CardOrderProtection
     */
    private $cardOrderProtection;

    /**
     * @var OrderSecurityCardEventService
     */
    private $eventService;

    /**
     * @var OrderSecurityCardNotificationService
     */
    private $notificationService;

    public function __construct(OrderNewSecurityCardSessionService $sessionService,
                                ApiPersonalDetails $apiPersonalDetails,
                                SecurityCardService $securityCardService,
                                OrderSecurityCardStepService $stepService,
                                CardOrderProtection $cardOrderProtection,
                                OrderSecurityCardNotificationService $notificationService,
                                OrderSecurityCardEventService $eventService)
    {
        $this->sessionService = $sessionService;
        $this->apiPersonalDetails = $apiPersonalDetails;
        $this->securityCardService = $securityCardService;
        $this->stepService = $stepService;
        $this->cardOrderProtection = $cardOrderProtection;
        $this->notificationService = $notificationService;
        $this->eventService = $eventService;
    }

    public function execute(Request $request, $userId)
    {
        $cardOrderProtectionResult = $this->cardOrderProtection->checkAuthorisation($userId);

        if ($cardOrderProtectionResult instanceof RedirectToRoute) {
            return $cardOrderProtectionResult;
        }

        if (!$this->stepService->isAllowedOnStep($userId, OrderSecurityCardStepService::REVIEW_STEP)) {
            return new RedirectToRoute('security-card-order/address', ['userId' => $userId]);
        }

        $personalDetailsData = $this->apiPersonalDetails->getPersonalDetailsData($userId);
        $personalDetails = new PersonalDetails($personalDetailsData);

        if ($request->isPost()) {
            if (!$this->hasAlreadySubmittedOrder($userId)) {
                $addressStepData = $this->sessionService->loadByGuid($userId)[OrderNewSecurityCardSessionService::ADDRESS_STEP_STORE];
                $cardOrdered = (bool) $this->securityCardService->orderNewCard($personalDetails->getUsername(), $userId, $addressStepData);
                if ($cardOrdered === true) {
                    $this->setUpHasAlreadySubmittedOrder($userId, $cardOrdered);
                    $this->eventService->createEvent($userId, $this->formatAddressForEvent($addressStepData));
                    $this->notificationService->sendNotification($userId);

                    return new RedirectToRoute('security-card-order/confirmation', ['userId' => $userId]);
                }
            } else {
                return new RedirectToRoute('security-card-order/confirmation', ['userId' => $userId]);
            }
        }
        $result = new ViewActionResult();

        $viewModel = $this->setUpViewModel($userId, $personalDetails);
        $result->setViewModel($viewModel);
        $result->layout()->setPageTitle(self::REVIEW_PAGE_TITLE);
        $result->layout()->setPageSubTitle(self::REVIEW_PAGE_SUBTITLE);
        $result->setTemplate('2fa/card-order/review');

        return $result;
    }

    private function setUpViewModel($userId, PersonalDetails $personalDetails)
    {
        $addressStepData = $this->sessionService->loadByGuid($userId)[OrderNewSecurityCardSessionService::ADDRESS_STEP_STORE];
        $viewModel = new CardOrderReviewViewModel();

        $viewModel
            ->setUserId($userId)
            ->setAddressLineOne($addressStepData['address1'])
            ->setAddressLineTwo($addressStepData['address2'])
            ->setAddressLineThree($addressStepData['address3'])
            ->setTown($addressStepData['townOrCity'])
            ->setPostcode($addressStepData['postcode'])
            ->setVtsName($addressStepData['vtsName'])
            ->setName($personalDetails->getFullName());

        return $viewModel;
    }

    private function setUpHasAlreadySubmittedOrder($userId, $cardOrdered)
    {
        $sessionStorage = $this->sessionService->loadByGuid($userId);
        $sessionStorage[OrderNewSecurityCardSessionService::HAS_ORDERED_STORE] = $cardOrdered;
        $this->sessionService->saveToGuid($userId, $sessionStorage);
    }

    private function hasAlreadySubmittedOrder($userId)
    {
        return $this->sessionService->loadByGuid($userId)[OrderNewSecurityCardSessionService::HAS_ORDERED_STORE];
    }

    private function formatAddressForEvent(array $addressData)
    {
        $addressData['addressChoice'] = '';
        $test = implode(', ', array_filter($addressData));

        return $test;
    }
}
