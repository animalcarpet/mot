<?php

namespace Core\TwoStepForm;

use Core\Action\ViewActionResult;
use Core\Action\NotFoundActionResult;
use Core\ViewModel\Gds\Table\GdsTable;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\Exception\ValidationException;

final class ReviewStepAction implements AutoWireableInterface
{
    private $formContainer;
    private $authorisationService;

    public function __construct(
        TwoStepFormContainer $formContainer,
        MotAuthorisationServiceInterface $authorisationService
    ) {
        $this->authorisationService = $authorisationService;
        $this->formContainer = $formContainer;
    }

    /**
     * @param $isPost
     * @param SingleStepProcessInterface $process
     * @param FormContextInterface       $context
     * @param $formUuid
     *
     * @return ViewActionResult
     *
     * @throws UnauthorisedException
     */
    public function execute($isPost, SingleStepProcessInterface $process, FormContextInterface $context, $formUuid)
    {
        $process->setContext($context);

        if (!$process instanceof TwoStepProcessInterface) {
            return new NotFoundActionResult();
        }

        $this->assertGranted($process);

        $formData = $this->formContainer->get($formUuid, $process->getSessionStoreKey());

        if (!$isPost) {
            if ($formData === null) {
                return $process->redirectToEditPage();
            }
        }

        $gdsTable = $process->transformFormIntoGdsTable($formData);

        $errors = [];
        if ($isPost) {
            try {
                $process->update($formData);

                if ($process->hasConfirmationPage()) {
                    return $process->redirectToConfirmationPage();
                }
                $result = $process->redirectToStartPage();
                $result->addSuccessMessage($process->getSuccessfulEditMessage());

                return $result;
            } catch (ValidationException $exception) {
                $errors = $exception->getDisplayMessages();
            }
        }

        return $this->buildActionResult($process, $formUuid, $formData, $gdsTable, $errors);
    }

    private function assertGranted(SingleStepProcessInterface $process)
    {
        if (!$process->isAuthorised($this->authorisationService)) {
            throw new UnauthorisedException('Not authorised to edit the form');
        }
    }

    /**
     * @param TwoStepProcessInterface $process
     * @param $formUuid
     * @param $formData
     * @param GdsTable $table
     * @param null     $errors
     *
     * @return ViewActionResult
     */
    protected function buildActionResult(TwoStepProcessInterface $process, $formUuid, $formData, GdsTable $table, $errors = null)
    {
        $breadcrumbs = $process->getBreadcrumbs($this->authorisationService);

        $vm = $process->buildReviewStepViewModel($formUuid, $formData, $table);

        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->addErrorMessages($errors);

        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');

        $actionResult->layout()->setPageTitle($process->getReviewPageTitle());
        $actionResult->layout()->setPageSubTitle($process->getPageSubTitle());
        $actionResult->layout()->setPageLede($process->getReviewPageLede());
        $actionResult->layout()->setBreadcrumbs($breadcrumbs);

        return $actionResult;
    }
}
