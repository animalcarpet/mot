<?php

namespace DvsaMotTest\Action;

use Core\Action\ActionResult;
use Core\Action\ActionResultLayout;
use Core\Action\RedirectToRoute;
use Core\Routing\MotTestRouteList;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaMotTest\Flash\VehicleCertificateSearchFlashMessage;
use DvsaMotTest\Form\VehicleSearch\AbstractDuplicateCertificateSearchForm;
use DvsaMotTest\ViewModel\VehicleSearch\DuplicateCertificateSearchViewModel;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

abstract class AbstractDuplicateCertificateSearchAction implements AutoWireableInterface
{
    const REPLACEMENT_CERTIFICATE_SEARCH_PAGE_TITLE = 'Duplicate or replacement certificate';

    private $flashMessenger;

    public function __construct(FlashMessenger $flashMessenger)
    {
        $this->flashMessenger = $flashMessenger;
    }

    public function execute($data)
    {
        $form = $this->getForm();
        $shouldWeRedirectToResults = $this->tryRedirectingToResults($form, $data);

        if ($shouldWeRedirectToResults) {
            return $shouldWeRedirectToResults;
        }

        return $this->createActionResult($form);
    }

    /**
     * @param $form
     * @return ActionResult
     */
    private function createActionResult($form)
    {
        $viewModel = (new DuplicateCertificateSearchViewModel())
            ->setForm($form)
            ->setShowNoResultsMessage($this->shouldShowNoResultsMessage());

        $actionResult = new ActionResult();
        $actionResult
            ->setViewModel($viewModel)
            ->setTemplate($this->getTemplate());

        $layout = $actionResult->layout();
        $layout
            ->setTemplate('layout/layout-govuk.phtml')
            ->setPageSubTitle(self::REPLACEMENT_CERTIFICATE_SEARCH_PAGE_TITLE)
            ->setBreadcrumbs([self::REPLACEMENT_CERTIFICATE_SEARCH_PAGE_TITLE => null]);

        $this->setAdditionalLayoutProperties($layout);

        return $actionResult;
    }

    /**
     * @param AbstractDuplicateCertificateSearchForm $form
     * @param array $data
     * @return RedirectToRoute
     */
    protected function tryRedirectingToResults(AbstractDuplicateCertificateSearchForm $form, $data)
    {
        $form->setData($data);
        if (isset($data[$form::FIELD_SUBMIT])) {
            if ($form->isValid()) {
                unset($data[$form::FIELD_SUBMIT]);

                return new RedirectToRoute(
                    'vehicle-certificates', [], $data // TODO: change to some const
                );
            }
        }

        return null;
    }

    private function shouldShowNoResultsMessage()
    {
        $messages = $this->flashMessenger->getMessages(VehicleCertificateSearchFlashMessage::getNamespace()->getName());
        if (count($messages) < 1) {
            return false;
        }
        foreach ($messages as $message) {
            if ($message === VehicleCertificateSearchFlashMessage::NOT_FOUND) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return AbstractDuplicateCertificateSearchForm
     */
    abstract protected function getForm();

    /**
     * @param ActionResultLayout $layout
     * @return ActionResultLayout
     */
    abstract protected function setAdditionalLayoutProperties(ActionResultLayout $layout);

    /**
     * @return string
     */
    abstract protected function getTemplate();
}