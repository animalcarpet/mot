<?php

namespace Core\TwoStepForm;

use Core\Action\AbstractRedirectActionResult;
use Core\ViewModel\Gds\Table\GdsTable;

interface TwoStepProcessInterface extends SingleStepProcessInterface
{
    /**
     * This will take the form and create a GdsTable that will be shown as summary
     * for user to review before completing the form.
     *
     * @param array $formData
     * @return GdsTable
     */
    public function transformFormIntoGdsTable(array $formData);

    /**
     * The title that will be displayed on the review page
     *
     * @return string
     */
    public function getReviewPageTitle();

    /**
     * The page lede that will be displayed on the review page
     *
     * @return string
     */
    public function getReviewPageLede();

    /**
     * The text that will be displayed on the review page button text
     *
     * @return string
     */
    public function getReviewPageButtonText();

    /**
     * @param $formUuid
     * @param $formData
     * @param GdsTable $table
     * @return Object Anything you want to pass to the view file
     */
    public function buildReviewStepViewModel($formUuid, $formData, GdsTable $table);

    /**
     * @param $formUuid
     * @return AbstractRedirectActionResult $authorisationService
     */
    public function redirectToReviewPage($formUuid);

    /**
     * @return AbstractRedirectActionResult $authorisationService
     */
    public function redirectToEditPage();

    /**
     * A two step form data needs to be saved in session to allow switching between form screens.
     * Data will be stored in the session under the key this method provides.
     *
     * @return string
     */
    public function getSessionStoreKey();

    /**
     * Does the process have a confirmation page at the end.
     * If true, will redirect to the confirmation page on the process,
     * if false will redirect to start with success message
     *
     * @return bool
     */
    public function hasConfirmationPage();

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToConfirmationPage();

    /**
     * @return mixed
     */
    public function populateConfirmationPageVariables();
}
