<?php

namespace Core\TwoStepForm;

use Core\Action\AbstractRedirectActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use Zend\Form\Form;

interface SingleStepProcessInterface
{
    public function setContext(FormContextInterface $context);

    /**
     * Will make a call to API to update the data from the form
     *
     * @param $formData
     * @return
     */
    public function update($formData);

    /**
     * Gets the values that the form should be pre-populated with.
     * (e.g. old values)
     * @return array
     */
    public function getPrePopulatedData();

    /**
     * What should be displayed on the submit button control.
     *
     * @return string
     */
    public function getSubmitButtonText();

    /**
     * Creates breadcrumbs for edit page.
     * Returning null means there are no breadcrumbs to display.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @return array
     */
    public function getBreadcrumbs(MotAuthorisationServiceInterface $authorisationService);

    /**
     * Zend form used to edit values
     *
     * @return Form
     */
    public function createEmptyForm();

    /**
     * Tells what message should be shown to the user when the form has been successfully submitted
     *
     * @return string
     */
    public function getSuccessfulEditMessage();

    /**
     * The title that will be displayed on the form page
     *
     * @return string
     */
    public function getEditStepPageTitle();

    /**
     * The sub title that will be displayed on the edit and review pages
     *
     * @return string
     */
    public function getPageSubTitle();

    /**
     * @param $form
     * @param string $requestUrl
     * @return Object Anything you want to pass to the view file
     */
    public function buildEditStepViewModel($form);

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToStartPage();

    /**
     * Says if the users is authorised to reach the page
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @return bool
     */
    public function isAuthorised(MotAuthorisationServiceInterface $authorisationService);

    public function getEditPageLede();
}
