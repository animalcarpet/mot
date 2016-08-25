<?php

namespace Site\UpdateVtsProperty\Process;

use Core\Action\AbstractRedirectActionResult;
use Core\Action\RedirectToRoute;
use Core\Routing\VtsRouteList;
use Core\TwoStepForm\TwoStepProcessInterface;
use Core\ViewModel\Gds\Table\GdsTable;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\ArrayUtils;
use Site\UpdateVtsProperty\UpdateVtsPropertyReviewViewModel;
use Zend\Form\Form;

/**
 *
 * todo rename this to testProcess
 * Class UpdateVtsReviewProcess
 * @package Site\UpdateVtsProperty\Process
 */
class UpdateVtsReviewProcess extends UpdateVtsPropertyProcess implements TwoStepProcessInterface, AutoWireableInterface
{
    private $formToGdsTableTransformer;
    private $reviewPageTitle;
    private $reviewPageLede;
    private $reviewPageButtonText;
    private $requiresReview;

    /** @deprecated Verify if required and used */
    const SESSION_KEY = 'SESSION_KEY';

    public function __construct(
        $propertyName,
        $permission, $requiresReview,
        $formPartial,
        Form $form,
        $submitButtonText,
        $currentValuesExtractor,
        $valueUpdater,
        $successfulEditMessage,
        $formPageTitle,
        $formToGdsTransformer,
        $reviewPageTitle,
        $pageSubTitle,
        $reviewPageLede,
        $reviewPageButtonText,
        $breadCrumb
    )
    {
        $this->formToGdsTableTransformer = $formToGdsTransformer;
        $this->reviewPageTitle = $reviewPageTitle;
        $this->reviewPageLede = $reviewPageLede;
        $this->reviewPageButtonText = $reviewPageButtonText;
        $this->propertyName = $propertyName;
        $this->permission = $permission;
        $this->requiresReview = $requiresReview;
        $this->form = $form;
        $this->submitButtonText = $submitButtonText;
        $this->formPartial = $formPartial;
        $this->currentValuesExtractor = $currentValuesExtractor;
        $this->valueUpdater = $valueUpdater;
        $this->successfulEditMessage = $successfulEditMessage;
        $this->formPageTitle = $formPageTitle;
        $this->breadCrumb = $breadCrumb;
        $this->pageSubTitle = $pageSubTitle;
    }

    public function transformFormIntoGdsTable(array $formData)
    {
        $table = new GdsTable();

        $value = ArrayUtils::firstOrNull($formData);

        $table->newRow()->setValue($value);

        return $table;
    }

    public function getReviewPageTitle()
    {
        return $this->reviewPageTitle;
    }

    public function getReviewPageLede()
    {
        return $this->reviewPageLede;
    }

    public function getReviewPageButtonText()
    {
        return $this->reviewPageButtonText;
    }

    /**
     * @param $formUuid
     * @param $formData
     * @param GdsTable $table
     * @return Object Anything you want to pass to the view file
     * @internal param $entityId
     */
    public function buildReviewStepViewModel($formUuid, $formData, GdsTable $table)
    {
        return new UpdateVtsPropertyReviewViewModel($this->context->getVtsId(), $this->context->getPropertyName(), $formUuid, $this->getReviewPageButtonText(), $formData, $table);
    }

    public function redirectToReviewPage($formUuid)
    {
        return new RedirectToRoute(VtsRouteList::VTS_EDIT_PROPERTY_REVIEW,
            ['id' => $this->context->getVtsId(), 'propertyName' => $this->context->getPropertyName(), 'formUuid' => $formUuid]
        );
    }

    /**
     * A two step form data needs to be saved in session to allow switching between form screens.
     * Data will be stored in the session under the key this method provides.
     *
     * @return string
     *
     * @deprecated ask if it's possible to use formUuid only
     */
    public function getSessionStoreKey()
    {
        return self::SESSION_KEY;
    }

    public function hasConfirmationPage()
    {
        return false;
    }

    public function redirectToConfirmationPage()
    {

    }

    public function populateConfirmationPageVariables()
    {

    }
}
