<?php

namespace Organisation\UpdateAeProperty;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class UpdateAePropertyController extends AbstractAuthActionController implements AutoWireableInterface
{
    private $updateAction;
    private $reviewAction;
    /** @var UpdateAePropertyProcessBuilder todo deprecated remove */
    private $processBuilder;

    public function __construct(
        UpdateAePropertyAction $updateAction,
        UpdateAePropertyReviewAction $reviewAction,
        UpdateAePropertyProcessBuilder $processBuilder
    ) {
        $this->updateAction = $updateAction;
        $this->reviewAction = $reviewAction;
        $this->processBuilder = $processBuilder;
    }

    public function editAction()
    {
        $propertyName = $this->params()->fromRoute('propertyName');
        $isPost = $this->getRequest()->isPost();
        $aeId = $this->params()->fromRoute('id');
        $formData = $this->getRequest()->getPost()->getArrayCopy();
        $formUuid = $this->params()->fromQuery('formUuid');

        $actionResult = $this->updateAction->execute($isPost, $this->processBuilder->get($propertyName), new UpdateAeContext($aeId, $propertyName), $formUuid, $formData);

        return $this->applyActionResult($actionResult);
    }

    public function reviewAction()
    {
        $propertyName = $this->params()->fromRoute('propertyName');
        $isPost = $this->getRequest()->isPost();
        $aeId = $this->params()->fromRoute('id');
        $formUuid = $this->params()->fromRoute('formUuid');

        $actionResult = $this->reviewAction->execute($isPost, $this->processBuilder->get($propertyName), new UpdateAeContext($aeId, $propertyName), $formUuid);

        return $this->applyActionResult($actionResult);
    }
}
