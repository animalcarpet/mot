<?php

namespace Organisation\Controller;

use Core\Action\RedirectToRoute;
use Core\Controller\AbstractAuthActionController;
use Core\Routing\AeRouteList;
use Core\Routing\AeRoutes;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use Organisation\Traits\OrganisationServicesTrait;
use Organisation\UpdateAeProperty\UpdateAeContext;
use Organisation\UpdateAeProperty\UpdateAePropertyProcessBuilder;
use Organisation\ViewModel\AuthorisedExaminer\AeRemovePrincipalViewModel;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;
use Organisation\UpdateAeProperty\UpdateAePropertyReviewAction;
use Zend\View\Model\ViewModel;
use DvsaCommon\Auth\PermissionAtOrganisation;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * Class AuthorisedExaminerPrincipalController.
 */
class AuthorisedExaminerPrincipalController extends AbstractAuthActionController implements AutoWireableInterface
{
    const ROUTE_REMOVE_CONFIRMATION = 'authorised-examiner/remove-principal-confirmation';
    const REMOVE_TITLE = 'Remove a Principal';
    use OrganisationServicesTrait;

    private $authorisationService;
    private $mapper;
    private $updateAction;
    private $reviewAction;
    private $processBuilder;

    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        MapperFactory $mapper,
        UpdateAePropertyAction $updateAction,
        UpdateAePropertyReviewAction $reviewAction,
        UpdateAePropertyProcessBuilder $processBuilder
    ) {
        $this->authorisationService = $authorisationService;
        $this->mapper = $mapper;
        $this->updateAction = $updateAction;
        $this->reviewAction = $reviewAction;
        $this->processBuilder = $processBuilder;
    }

    public function createAction()
    {
        $propertyName = UpdateAePropertyAction::AE_CREATE_AEP_PROPERTY;
        $isPost = $this->getRequest()->isPost();
        $aeId = $this->params()->fromRoute('id');
        $formData = $this->getRequest()->getPost()->getArrayCopy();
        $formUuid = $this->params()->fromQuery('formUuid');

        $actionResult = $this->updateAction->execute($isPost, $this->processBuilder->get($propertyName), new UpdateAeContext($aeId, $propertyName), $formUuid, $formData);

        return $this->applyActionResult($actionResult);
    }

    public function reviewAction()
    {
        $propertyName = UpdateAePropertyAction::AE_CREATE_AEP_PROPERTY;
        $isPost = $this->getRequest()->isPost();
        $aeId = $this->params()->fromRoute('id');
        $formUuid = $this->params()->fromRoute('formUuid');

        $actionResult = $this->reviewAction->execute($isPost, $this->processBuilder->get($propertyName), new UpdateAeContext($aeId, $propertyName), $formUuid);

        return $this->applyActionResult($actionResult);
    }

    public function removeConfirmationAction()
    {
        $authorisedExaminerId = $this->params()->fromRoute('id');
        $permission = PermissionAtOrganisation::AUTHORISED_EXAMINER_PRINCIPAL_REMOVE;
        $this->authorisationService->assertGrantedAtOrganisation($permission, $authorisedExaminerId);

        $authorisedExaminer = $this->mapper->Organisation->getAuthorisedExaminer($authorisedExaminerId);
        $principalId = $this->params()->fromRoute('principalId');
        $principal = $this->mapper->AuthorisedExaminerPrincipal->getByIdentifier($authorisedExaminerId, $principalId);

        $urlAE = AeRoutes::of($this->url())->ae($authorisedExaminerId);

        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $this->mapper->AuthorisedExaminerPrincipal->removePrincipalsForOrganisation($authorisedExaminerId, $principalId);
                $this->addSuccessMessage('Principal - '.$principal->getDisplayName().' has been removed successfully.');
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }

            return $this->applyActionResult(new RedirectToRoute(AeRouteList::AE, ['id' => $authorisedExaminerId]));
        }

        $viewModel = new AeRemovePrincipalViewModel();
        $viewModel
            ->setAuthorisedExaminer($authorisedExaminer->getName())
            ->setPrincipalName($principal->getDisplayName())
            ->setAddress($principal->getContactDetails()->getAddress())
            ->setDateOfBirth($principal->displayDateOfBirth())
            ->setCancelUrl($urlAE);

        $breadcrumbs = [$authorisedExaminer->getName() => $urlAE];

        $lede = 'Are you sure you want to remove '.$viewModel->getPrincipalName().' as an Authorised Examiner Principal?';

        return $this->prepareViewModel(
            new ViewModel(['viewModel' => $viewModel]), self::REMOVE_TITLE, 'Authorised Examiner', $lede, $breadcrumbs
        );
    }

    /**
     * Prepare the view model for all the step of the create ae.
     *
     * @param ViewModel $view
     * @param string    $title
     * @param string    $subtitle
     * @param null      $breadcrumbs
     *
     * @return ViewModel
     */
    private function prepareViewModel(
        ViewModel $viewModel,
        $title,
        $subtitle,
        $lede,
        $breadcrumbs = null
    ) {
        $breadcrumbs = array_merge($breadcrumbs, [$title => '']);

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageTitle', $title)
            ->setVariable('pageSubTitle', $subtitle)
            ->setVariable('pageLede', $lede)
            ->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        return $viewModel;
    }
}
