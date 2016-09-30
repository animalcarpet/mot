<?php

namespace Vehicle\UpdateVehicleProperty\Process;

use Core\Action\AbstractRedirectActionResult;
use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\SingleStepProcessInterface;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\Form\Form;

class UpdateEngineProcess implements SingleStepProcessInterface, AutoWireableInterface
{

    public function setContext(FormContextInterface $context)
    {
        // TODO: Implement setContext() method.
    }

    /**
     * Will make a call to API to update the data from the form
     *
     * @param $formData
     * @return
     */
    public function update($formData)
    {
        // TODO: Implement update() method.
    }

    /**
     * Gets the values that the form should be pre-populated with.
     * (e.g. old values)
     * @return array
     */
    public function getPrePopulatedData()
    {
        // TODO: Implement getPrePopulatedData() method.
    }

    /**
     * What should be displayed on the submit button control.
     *
     * @return string
     */
    public function getSubmitButtonText()
    {
        // TODO: Implement getSubmitButtonText() method.
    }

    /**
     * Creates breadcrumbs for edit page.
     * Returning null means there are no breadcrumbs to display.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @return array
     */
    public function getBreadcrumbs(MotAuthorisationServiceInterface $authorisationService)
    {
        // TODO: Implement getBreadcrumbs() method.
    }

    /**
     * Zend form used to edit values
     *
     * @return Form
     */
    public function createEmptyForm()
    {
        // TODO: Implement createEmptyForm() method.
    }

    /**
     * Tells what message should be shown to the user when the form has been successfully submitted
     *
     * @return string
     */
    public function getSuccessfulEditMessage()
    {
        // TODO: Implement getSuccessfulEditMessage() method.
    }

    /**
     * The title that will be displayed on the form page
     *
     * @return string
     */
    public function getEditStepPageTitle()
    {
        // TODO: Implement getEditStepPageTitle() method.
    }

    /**
     * The sub title that will be displayed on the edit and review pages
     *
     * @return string
     */
    public function getPageSubTitle()
    {
        // TODO: Implement getPageSubTitle() method.
    }

    /**
     * @param $form
     * @return Object Anything you want to pass to the view file
     */
    public function buildEditStepViewModel($form)
    {
        // TODO: Implement buildEditStepViewModel() method.
    }

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToStartPage()
    {
        // TODO: Implement redirectToStartPage() method.
    }

    /**
     * Says if the users is authorised to reach the page
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @return bool
     */
    public function isAuthorised(MotAuthorisationServiceInterface $authorisationService)
    {
        // TODO: Implement isAuthorised() method.
    }

    public function getEditPageLede()
    {
        // TODO: Implement getEditPageLede() method.
    }
}