<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action;

use Application\Model\RoleSummaryCollection;
use Core\Action\RedirectToRoute;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\ApiClient\Exception\ResourceConflictException;
use Dvsa\Mot\ApiClient\Exception\ResourceNotFoundException;
use Dvsa\Mot\ApiClient\Exception\ResourceValidationException;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Form\SecurityCardActivationForm;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Model\GtmSecurityCardPinValidationCallback;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Model\GtmSecurityCardSerialNumberValidationCallback;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardViewStrategy;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel\RegisterCardViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\TwoFactorNominationNotificationService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\Http\Request;

class RegisterCardPostAction extends RegisterCardAction implements AutoWireableInterface
{
    /** @var RegisterCardService */
    protected $registerCardService;

    /**
     * @var TwoFactorNominationNotificationService
     */
    protected $twoFactorNominationNotificationService;

    /**
     * @var MotFrontendIdentityProviderInterface
     */
    protected $identityProvider;

    public function __construct(
        RegisterCardViewStrategy $viewStrategy,
        RegisterCardService $cardService,
        TwoFactorNominationNotificationService $twoFactorNominationNotificationService,
        MotFrontendIdentityProviderInterface $identityProvider
    ) {
        parent::__construct($viewStrategy);
        $this->registerCardService = $cardService;
        $this->twoFactorNominationNotificationService = $twoFactorNominationNotificationService;
        $this->identityProvider = $identityProvider;
    }

    public function doExecute(Request $request)
    {
        $result = $this->defaultActionResult();
        $gtmPinCallback = new GtmSecurityCardPinValidationCallback();
        $gtmSerialNumberCallback = new GtmSecurityCardSerialNumberValidationCallback();
        $form = new SecurityCardActivationForm($gtmPinCallback, $gtmSerialNumberCallback);
        $postData = $request->getPost()->toArray();
        $form->setData($postData);
        /** @var RegisterCardViewModel $viewModel */
        $viewModel = $result->getViewModel();
        $viewModel->setForm($form);

        if ($form->isValid()) {
            try {
                $this->registerCardService->registerCard($postData['serial_number'], $postData['pin']);
                $identity = $this->identityProvider->getIdentity();

                $updatedNominations = $this->twoFactorNominationNotificationService
                    ->sendNotificationsForPendingNominations($identity->getUserId());

                return $this->getSuccessRedirect($updatedNominations);
            } catch (ResourceNotFoundException $e) {
                $form->setCustomError($form->getSerialNumberField(), 'Enter a valid serial number');
                $viewModel->setInvalidSerialNumber(true);
            } catch (ResourceConflictException $e) {
                $viewModel->setCardAlreadyRegistered(true);
            } catch (ResourceValidationException $e) {
                $viewModel->setPinMismatch(true);
            }
        } else {
            $form->clearPin();
            $viewModel->setGtmData(array_merge($gtmPinCallback->toGtmData(), $gtmSerialNumberCallback->toGtmData()));
        }

        return $result;
    }

    private function getSuccessRedirect(RoleSummaryCollection $updatedNominations)
    {
        $routeParams = [];
        $queryParams = [];
        if ($updatedNominations->containsOrganisationRole('AEDM')) {
            $queryParams = ['newlyAssignedRoles' => 'AEDM'];
        }

        return new RedirectToRoute('register-card/success', $routeParams, $queryParams);
    }
}
