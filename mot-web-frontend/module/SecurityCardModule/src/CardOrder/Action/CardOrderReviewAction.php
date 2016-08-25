<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action;

use Application\Data\ApiPersonalDetails;
use Core\Action\ActionResult;
use Core\Action\RedirectToRoute;
use Core\Service\MotFrontendIdentityProvider;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\ViewModel\CardOrderReviewViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\Http\Request;

class CardOrderReviewAction
{
    const REVIEW_PAGE_TITLE = 'Review delivery address';
    const REVIEW_PAGE_SUBTITLE = 'Order a security card';

    /**
     * @var OrderNewSecurityCardSessionService $sessionService
     */
    private $sessionService;

    /**
     * @var ApiPersonalDetails $apiPersonalDetails
     */
    private $apiPersonalDetails;

    /**
     * @var SecurityCardService $securityCardService
     */
    private $securityCardService;

    /**
     * @var OrderSecurityCardStepService $stepService
     */
    private $stepService;

    /**
     * @var CardOrderProtection $cardOrderProtection
     */
    private $cardOrderProtection;

    public function __construct(OrderNewSecurityCardSessionService $sessionService,
                                ApiPersonalDetails $apiPersonalDetails,
                                SecurityCardService $securityCardService,
                                OrderSecurityCardStepService $stepService,
                                CardOrderProtection $cardOrderProtection)
    {
        $this->sessionService = $sessionService;
        $this->apiPersonalDetails = $apiPersonalDetails;
        $this->securityCardService = $securityCardService;
        $this->stepService = $stepService;
        $this->cardOrderProtection = $cardOrderProtection;
    }

    public function execute(Request $request, $userId)
    {
        $cardOrderProtectionResult = $this->cardOrderProtection->checkAuthorisation($userId);

        if ($cardOrderProtectionResult instanceof RedirectToRoute) {
            return $cardOrderProtectionResult;
        }

        if(!$this->stepService->isAllowedOnStep($userId, OrderSecurityCardStepService::REVIEW_STEP)) {
            return new RedirectToRoute('security-card-order/address', ['userId' => $userId]);
        }

        $personalDetailsData = $this->apiPersonalDetails->getPersonalDetailsData($userId);
        $personalDetails = new PersonalDetails($personalDetailsData);

        if($request->isPost()) {
            if (!$this->hasAlreadySubmittedOrder($userId)) {
                $addressStepData = $this->sessionService->loadByGuid($userId)[OrderNewSecurityCardSessionService::ADDRESS_STEP_STORE];
                $cardOrdered = (bool)$this->securityCardService->orderNewCard($personalDetails->getUsername(), $userId, $addressStepData);
                $this->setUpHasAlreadySubmittedOrder($userId, $cardOrdered);
                return new RedirectToRoute('security-card-order/confirmation', ['userId' => $userId]);
            } else {
                return new RedirectToRoute('security-card-order/confirmation', ['userId' => $userId]);
            }
        }
        $result = new ActionResult();

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
}