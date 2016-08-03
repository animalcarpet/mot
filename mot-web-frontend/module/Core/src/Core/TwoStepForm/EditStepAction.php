<?php

namespace Core\TwoStepForm;

use Core\Action\AbstractRedirectActionResult;
use Core\Action\ActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use Zend\Form\Form;

final class EditStepAction implements AutoWireableInterface
{
    private $formContainer;
    private $authorisationService;

    public function __construct(
        TwoStepFormContainer $formContainer,
        MotAuthorisationServiceInterface $authorisationService
    )
    {
        $this->formContainer = $formContainer;
        $this->authorisationService = $authorisationService;
    }

    /**
     * @param $isPost
     * @param SingleStepProcessInterface $process
     * @param FormContextInterface $context
     * @param $formUuid
     * @param array $formData
     * @return ActionResult
     */
    public function execute($isPost, SingleStepProcessInterface $process, FormContextInterface $context, $formUuid, array $formData = [])
    {
        if ($isPost) {
            return $this->executePost($process, $context, $formData);
        } else {
            return $this->executeGet($process, $context, $formUuid);
        }
    }

    protected function executeGet(SingleStepProcessInterface $process, FormContextInterface $context, $formUuid)
    {
        $process->setContext($context);

        $this->assertGranted($process);

        if ($process instanceof TwoStepProcessInterface && $formUuid) {
            $formData = $this->formContainer->get($formUuid, $process->getSessionStoreKey());
            if ($formData === null) {
                return $process->redirectToEditPage();
            }
        } else {
            $formData = $process->getPrePopulatedData();
        }

        $form = $process->createEmptyForm();
        $form->setData($formData);

        return $this->buildActionResult($process, $form);
    }

    protected function executePost(SingleStepProcessInterface $process, FormContextInterface $context, array $formData = [])
    {
        $process->setContext($context);

        $this->assertGranted($process);

        $form = $process->createEmptyForm();
        $form->setData($formData);

        $errors = [];
        if ($form->isValid()) {
            if ($process instanceof TwoStepProcessInterface) {
                $formUuid = $this->formContainer->store($process->getSessionStoreKey(), $formData);
                return $process->redirectToReviewPage($formUuid);
            }

            try {

                return $this->updateAndRedirectToStartPage($process, $formData);
            } catch (ValidationException $exception) {
                $errors = $exception->getDisplayMessages();
            }
        }

        return $this->buildActionResult($process, $form, $errors);
    }

    /**
     * @param SingleStepProcessInterface $process
     * @param array $formData
     * @return AbstractRedirectActionResult
     */
    protected function updateAndRedirectToStartPage(SingleStepProcessInterface $process, array $formData)
    {
        $process->update($formData);
        $result = $process->redirectToStartPage();
        $result->addSuccessMessage($process->getSuccessfulEditMessage());

        return $result;
    }

    private function assertGranted(SingleStepProcessInterface $process)
    {
        if (!$process->isAuthorised($this->authorisationService)) {
            throw new UnauthorisedException("Not authorised to edit the form");
        }
    }

    protected function buildActionResult(SingleStepProcessInterface $process, Form $form, $errors = [])
    {
        $breadcrumbs = $process->getBreadcrumbs($this->authorisationService);

        $vm = $process->buildEditStepViewModel($form);

        $actionResult = new ActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->addErrorMessages($errors);

        $actionResult->layout()->setPageTitle($process->getEditStepPageTitle());
        $actionResult->layout()->setPageSubTitle($process->getPageSubTitle());
        $actionResult->layout()->setPageLede($process->getEditPageLede());

        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');

        if ($breadcrumbs !== null) {
            $actionResult->layout()->setBreadcrumbs($breadcrumbs);
        }

        return $actionResult;
    }
}
