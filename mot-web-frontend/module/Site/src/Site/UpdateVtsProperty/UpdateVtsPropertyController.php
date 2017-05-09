<?php

namespace Site\UpdateVtsProperty;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class UpdateVtsPropertyController extends AbstractAuthActionController implements AutoWireableInterface
{
    private $updateAction;
    private $reviewAction;
    /** todo deprecated remove */
    private $processBuilder;

    public function __construct(
        UpdateVtsPropertyAction $updateAction,
        UpdateVtsPropertyReviewAction $reviewAction,
        UpdateVtsPropertyProcessBuilder $processBuilder
    ) {
        $this->updateAction = $updateAction;
        $this->reviewAction = $reviewAction;
        $this->processBuilder = $processBuilder;
    }

    public function editAction()
    {
        $propertyName = $this->params()->fromRoute('propertyName');
        $isPost = $this->getRequest()->isPost();
        $vtsId = $this->params()->fromRoute('id');
        $formData = $this->getRequest()->getPost()->getArrayCopy();
        $formUuid = $this->params()->fromQuery('formUuid');

        $actionResult = $this->updateAction->execute($isPost, $this->processBuilder->get($propertyName), new UpdateVtsContext($vtsId, $propertyName), $formUuid, $formData);

        return $this->applyActionResult($actionResult);
    }

    public function reviewAction()
    {
        $propertyName = $this->params()->fromRoute('propertyName');
        $isPost = $this->getRequest()->isPost();
        $vtsId = $this->params()->fromRoute('id');
        $formUuid = $this->params()->fromRoute('formUuid');

        $actionResult = $this->reviewAction->execute($isPost, $this->processBuilder->get($propertyName), new UpdateVtsContext($vtsId, $propertyName), $formUuid);

        return $this->applyActionResult($actionResult);
    }
}
