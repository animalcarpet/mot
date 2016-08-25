<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action;

use Application\Model\RoleSummaryCollection;
use Core\Action\RedirectToRoute;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\ApiClient\Exception\ResourceNotFoundException;
use Dvsa\Mot\ApiClient\Exception\ResourceValidationException;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Form\SecurityCardActivationForm;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardViewStrategy;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel\RegisterCardViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\TwoFactorNominationNotificationService;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaFeature\FeatureToggles;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class RegisterCardPostAction extends RegisterCardAction implements AutoWireableInterface
{
    /** @var RegisterCardService */
    protected $registerCardService;

    /**
     * @var TwoFactorNominationNotificationService $twoFactorNominationNotificationService
     */
    protected $twoFactorNominationNotificationService;

    /**
     * @var MotFrontendIdentityProviderInterface $identityProvider
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
        $form = new SecurityCardActivationForm();
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
                $form->setCustomError($form->getSerialNumberField(), "Enter a valid serial number");
            } catch (ResourceValidationException $e) {
                $viewModel->setPinMismatch(true);
            }
        } else {
            $form->clearPin();
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

        return new RedirectToRoute("register-card/success", $routeParams, $queryParams);
    }
}
