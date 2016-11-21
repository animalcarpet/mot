<?php

namespace SiteTest\UpdateVtsProperty;

use Core\Action\ViewActionResult;
use Core\Action\NotFoundActionResult;
use Core\Action\RedirectToRoute;
use Core\Routing\VtsRouteList;
use Core\TwoStepForm\EditStepAction;
use Core\TwoStepForm\ReviewStepAction;
use Core\TwoStepForm\TwoStepFormContainer;
use Core\ViewModel\Gds\Table\GdsTable;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\VtsUpdaterStub;
use DvsaCommonTest\TestUtils\XMock;
use Site\UpdateVtsProperty\Process\UpdateVtsPropertyProcess;
use Site\UpdateVtsProperty\Process\UpdateVtsReviewProcess;
use Site\UpdateVtsProperty\UpdateVtsContext;
use Site\UpdateVtsProperty\UpdateVtsPropertyReviewViewModel;
use Site\UpdateVtsProperty\UpdateVtsPropertyViewModel;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\StringLength;
use Zend\View\Helper\Url;

class UpdateVtsPropertyTest extends \PHPUnit_Framework_TestCase
{
    private $vtsId = 11;
    /**
     * @var SiteMapper
     */
    private $siteMapper;

    /**
     * @var AuthorisationServiceMock
     */
    private $authorisationService;

    private $propertyName = "vts-property";

    private $requiredPermission = "PERMISSION";

    private $formElementName = 'vts-form-element-name';

    private $submitButtonText = "Submit changes";

    private $oldValueFromDb = "Old vts property";

    /** @var MethodSpy */
    private $updateVtsSpy;

    /** @var VtsUpdaterStub */
    private $vtsUpdater;

    private $successMessage = "You have changed the property";

    private $partial = "Partial file that displays form";

    private $pageTitle = "This is a proper page title";

    private $reviewPageTitle = "This is a proper review page title";

    private $reviewPageLede = "This is a proper review page lede";

    private $reviewSubmitButtonText = "Review button text";

    private $breadCrumb = ["Breadcrumb"];

    /** @var Url */
    private $urlHelper;

    /** @var TwoStepFormContainer */
    private $formSession;

    private $pageSubTitle = "Subtitle text";

    private $formStoreKey = UpdateVtsReviewProcess::SESSION_KEY;

    protected function setUp()
    {
        $vtsDto = new VehicleTestingStationDto();
        $vtsDto->setName($this->oldValueFromDb);

        $this->siteMapper = XMock::of(SiteMapper::class);
        $this->siteMapper->expects($this->any())
            ->method('getById')
            ->willReturn($vtsDto);

        $this->vtsUpdater = XMock::of(VtsUpdaterStub::class);
        $this->updateVtsSpy = new MethodSpy($this->vtsUpdater, 'update');

        $this->urlHelper = Xmock::of(Url::class);
        $this->formSession = new TwoStepFormContainer();

        $this->authorisationService = new AuthorisationServiceMock();
        $this->authorisationService->grantedAtSite($this->requiredPermission, $this->vtsId);
    }

    public function test_firstStepGet_viewFormFirstTimeAction()
    {
        // GIVEN I'm viewing the form for editing
        $process = $this->createProcess(false);
        $action = new EditStepAction($this->formSession, $this->authorisationService);

        // AND I do it for the first time, so there is no form in the user session
        $formUuid = null;

        // WHEN I view it
        $result = $action->execute(false, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), null, []);

        // THEN I'm not redirected anywhere
        $this->assertInstanceOf(ViewActionResult::class, $result);

        // AND I receive a proper view model
        /** @var UpdateVtsPropertyViewModel $vm */
        $vm = $result->getViewModel();
        $this->assertInstanceOf(UpdateVtsPropertyViewModel::class, $vm);

        //AND the form is pre-populated with data from database
        $this->assertEquals($this->oldValueFromDb, $vm->getForm()->get($this->formElementName)->getValue());

        // AND the form button has a proper name
        $this->assertEquals($this->submitButtonText, $vm->getSubmitButtonText());

        // AND title and subtitle are correctly set
        $this->assertEquals($this->pageTitle, $result->layout()->getPageTitle());
        $this->assertEquals($this->pageSubTitle, $result->layout()->getPageSubTitle());

        //AND template is gov layout
        $this->assertEquals("layout/layout-govuk.phtml", $result->layout()->getTemplate());
    }

    public function test_firstStepGet_notAuthorised()
    {
        // GIVEN I'm viewing the form for editing
        $process = $this->createProcess(false);
        $action = new EditStepAction($this->formSession, $this->authorisationService);

        // AND I do not have permission to do it
        $this->authorisationService->clearAll();

        try {
            // WHEN I view it
            $action->execute(false, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), null, []);

            // THEN an exception is thrown
            $this->fail("The UnauthorisedException was not thrown");
        } catch (UnauthorisedException $exception) {
        }
    }

    public function test_firstStepGet_redirectsWhenTheFormUuidDoesNotExistInSession()
    {
        // GIVEN I'm viewing the form for editing
        $process = $this->createProcess(true);
        $action = new EditStepAction($this->formSession, $this->authorisationService);

        // WITH a formUuid that does not exist in the session

        $formUuid = "some_random_value";

        // WHEN I view it

        /** @var RedirectToRoute $actionResult */
        $actionResult = $action->execute(false, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), $formUuid, []);

        // THEN I'm redirected to the same page

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertEquals(VtsRouteList::VTS_EDIT_PROPERTY, $actionResult->getRouteName());
        $this->assertEquals($this->vtsId, $actionResult->getRouteParams()['id']);

        // AND the formUuid is unset
        $this->assertArrayNotHasKey('formUuid', $actionResult->getQueryParams());
    }

    public function test_firstStepGet_whenGoingBackFromReviewStep()
    {
        // GIVEN I'm viewing the form for editing after clicking "back" button on review page
        $previouslyEnteredValue = "Small garage";
        $formUuid = $this->formSession->store($this->formStoreKey, [$this->formElementName => $previouslyEnteredValue]);

        $process = $this->createProcess(true);
        $action = new EditStepAction($this->formSession, $this->authorisationService);

        // WHEN I view it
        $result = $action->execute(false, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), $formUuid, []);

        // THEN I'm not redirected anywhere
        $this->assertInstanceOf(ViewActionResult::class, $result);

        // AND I receive a proper view model
        /** @var UpdateVtsPropertyViewModel $vm */
        $vm = $result->getViewModel();
        $this->assertInstanceOf(UpdateVtsPropertyViewModel::class, $vm);

        //AND the form is pre-populated with data previously entered and stored in session
        $this->assertEquals($previouslyEnteredValue, $vm->getForm()->get($this->formElementName)->getValue());

        // AND the form button has a proper name
        $this->assertEquals($this->submitButtonText, $vm->getSubmitButtonText());

        // AND title and subtitle are correctly set
        $this->assertEquals($this->pageTitle, $result->layout()->getPageTitle());
        $this->assertEquals($this->pageSubTitle, $result->layout()->getPageSubTitle());

        //AND template is gov layout
        $this->assertEquals("layout/layout-govuk.phtml", $result->layout()->getTemplate());
    }

    public function test_firstStepPost_notAuthorised()
    {
        // GIVEN I want to post a form
        $isPost = true;
        $process = $this->createProcess(false);
        $action = new EditStepAction($this->formSession, $this->authorisationService);

        // AND I do not have permission to do it
        $this->authorisationService->clearAll();

        try {
            // WHEN I post it
            $action->execute($isPost, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), null, []);

            // THEN an exception is thrown
            $this->fail("The UnauthorisedException was not thrown");
        } catch (UnauthorisedException $exception) {
        }
    }

    public function test_firstStepPost_withoutRequiredReviewStepAndWithValidData()
    {
        // GIVEN I want to update a property that does not require review step
        $process = $this->createProcess(false);

        $action = new EditStepAction($this->formSession, $this->authorisationService);

        // AND I filled the form out properly
        $formData = [$this->formElementName => "Small Garage"];

        // WHEN I post the form
        /** @var RedirectToRoute $result */
        $result = $action->execute(true, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), null, $formData);

        // THEN the form is submitted to API
        $this->assertEquals(1, $this->updateVtsSpy->invocationCount());
        $this->assertEquals($this->vtsId, $this->updateVtsSpy->paramsForLastInvocation()[0]);
        $this->assertEquals([$this->formElementName => "Small Garage"], $this->updateVtsSpy->paramsForLastInvocation()[1]);

        // AND I get redirected to VTS overview page of the correct VTS
        $this->assertInstanceOf(RedirectToRoute::class, $result);
        $this->assertEquals(VtsRouteList::VTS, $result->getRouteName());
        $this->assertEquals($this->vtsId, $result->getRouteParams()['id']);

        // AND the success message is set
        $this->assertEquals(1, count($result->getSuccessMessages()));
        $this->assertEquals($this->successMessage, $result->getSuccessMessages()[0]);
    }

    public function test_firstStepPost_withRequiredReviewStepWithValidData()
    {
        // GIVEN I want to update a property that does not require review step
        $process = $this->createProcess(true);

        $action = new EditStepAction($this->formSession, $this->authorisationService);

        // AND I filled the form out properly
        $formData = [$this->formElementName => "Small Garage"];

        // WHEN I post the form
        /** @var RedirectToRoute $result */
        $result = $action->execute(true, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), null, $formData);

        // THEN the form is not submitted to API
        $this->assertEquals(0, $this->updateVtsSpy->invocationCount());

        // AND I get redirected to review page
        $this->assertInstanceOf(RedirectToRoute::class, $result);
        $this->assertEquals(VtsRouteList::VTS_EDIT_PROPERTY_REVIEW, $result->getRouteName());

        // of a correct VTS
        $this->assertEquals($this->vtsId, $result->getRouteParams()['id']);

        // AND a formUuid set
        $formUuid = $result->getRouteParams()['formUuid'];
        $this->assertNotEmpty($formUuid);

        // AND form is stored in session
        $this->assertEquals($formData, $this->formSession->get($formUuid, $this->formStoreKey));
    }

    public function test_firstStepPost_invalidData()
    {
        // GIVEN I want to update a property that does not require review step
        $process = $this->createProcess(true);

        $action = new EditStepAction($this->formSession, $this->authorisationService);

        // AND I filled the form out INCORRECTLY
        $tooLongName = 'this will not pass validation as it has more than 30 chars';
        $formData = [$this->formElementName => $tooLongName];

        // WHEN I post the form
        /** @var ViewActionResult $result */
        $result = $action->execute(true, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), null, $formData);

        // THEN the form is not submitted to API
        $this->assertEquals(0, $this->updateVtsSpy->invocationCount());

        // AND I'm not redirected anywhere
        $this->assertInstanceOf(ViewActionResult::class, $result);

        // AND I receive a proper view model
        /** @var UpdateVtsPropertyViewModel $vm */
        $vm = $result->getViewModel();
        $this->assertInstanceOf(UpdateVtsPropertyViewModel::class, $vm);

        //AND the form is populated with data submitted data
        $this->assertEquals($tooLongName, $vm->getForm()->get($this->formElementName)->getValue());

        // AND the form button has a proper name
        $this->assertEquals($this->submitButtonText, $vm->getSubmitButtonText());

        // AND title and subtitle are correctly set
        $this->assertEquals($this->pageTitle, $result->layout()->getPageTitle());
        $this->assertEquals($this->pageSubTitle, $result->layout()->getPageSubTitle());

        //AND template is gov layout
        $this->assertEquals("layout/layout-govuk.phtml", $result->layout()->getTemplate());
    }

    public function test_reviewStepGet_viewFormFirstTimeAction()
    {
        // GIVEN I've entered data into form

        $newValue = "Entered value";
        $enteredData = [$this->formElementName => $newValue];
        $uuid = $this->formSession->store($this->formStoreKey, $enteredData);

        $process = $this->createProcess(true);
        $action = new ReviewStepAction($this->formSession, $this->authorisationService);

        // WHEN I review it
        /** @var ViewActionResult $result */
        $result = $action->execute(false, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), $uuid);

        // THEN I'm not redirected anywhere
        $this->assertInstanceOf(ViewActionResult::class, $result);

        // AND I receive a proper view model
        /** @var UpdateVtsPropertyReviewViewModel $vm */
        $vm = $result->getViewModel();
        $this->assertInstanceOf(UpdateVtsPropertyReviewViewModel::class, $vm);

        //AND the view contains summary table with data entered previously
        $table = $vm->getSummary();
        $tableRow = $table->getRows()[0];
        $this->assertEquals($newValue, $tableRow->getValue()->getContent());

        // AND the confirm button has a proper name
        $this->assertEquals($this->reviewSubmitButtonText, $vm->getSubmitButtonText());

        // AND title and subtitle are correctly set
        $this->assertEquals($this->reviewPageTitle, $result->layout()->getPageTitle());
        $this->assertEquals($this->pageSubTitle, $result->layout()->getPageSubTitle());

        //AND template is gov layout
        $this->assertEquals("layout/layout-govuk.phtml", $result->layout()->getTemplate());
    }

    public function test_reviewStepGet_withoutFormUuid()
    {
        // GIVEN I'm entering second(review) step
        $process = $this->createProcess(true);
        $action = new ReviewStepAction($this->formSession, $this->authorisationService);

        // WITH a formUuid that does not exist in the session

        $formUuid = "some_random_value";

        // WHEN I view it

        /** @var RedirectToRoute $actionResult */
        $actionResult = $action->execute(false, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), $formUuid, []);

        // THEN I'm redirected to the first step page
        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertEquals(VtsRouteList::VTS_EDIT_PROPERTY, $actionResult->getRouteName());
        $this->assertEquals($this->vtsId, $actionResult->getRouteParams()['id']);

        // AND the formUuid is unset
        $this->assertArrayNotHasKey('formUuid', $actionResult->getQueryParams());
    }

    public function test_reviewStepGet_notAuthorised()
    {
        // GIVEN I'm trying to reach the review step
        $process = $this->createProcess(true);
        $action = new ReviewStepAction($this->formSession, $this->authorisationService);

        // AND I do not have permission to do it
        $this->authorisationService->clearAll();

        try {
            // WHEN I view it
            $action->execute(false, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), null);

            // THEN an exception is thrown
            $this->fail("The UnauthorisedException was not thrown");
        } catch (UnauthorisedException $exception) {
        }
    }

    public function test_reviewStepGet_cannotGetToReviewStepIfTheVtsPropertyDoesNotRequireReview()
    {
        // GIVEN I want to get to review step
        // of a property that does not require confirmation step
        $process = $this->createProcess(false);
        $action = new ReviewStepAction($this->formSession, $this->authorisationService);

        // WHEN I view it
        $result = $action->execute(false, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), null);

        $this->assertInstanceOf(NotFoundActionResult::class, $result);
    }

    public function test_reviewStepPost_notAuthorised()
    {
        // GIVEN I want to confirm the form
        $isPost = true;
        $process = $this->createProcess(true);
        $action = new ReviewStepAction($this->formSession, $this->authorisationService);

        // AND I do not have permission to do it
        $this->authorisationService->clearAll();

        try {
            // WHEN I post it
            $action->execute($isPost, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), "uuid");

            // THEN an exception is thrown
            $this->fail("The UnauthorisedException was not thrown");
        } catch (UnauthorisedException $exception) {
        }
    }

    public function test_reviewStepPost_cannotGetToReviewStepIfTheVtsPropertyDoesNotRequireReview()
    {
        // GIVEN I want to confirm for during review step
        // of a property that does not require review step
        $process = $this->createProcess(false);
        $action = new ReviewStepAction($this->formSession, $this->authorisationService);

        // WHEN I view it
        $result = $action->execute(true, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), null);

        $this->assertInstanceOf(NotFoundActionResult::class, $result);
    }

    public function test_reviewStepPost_sendsUpdateToApi()
    {
        // GIVEN I want to confirm the form
        $isPost = true;
        $dataThatShouldBePostToApi = ["key" => "value"];
        $formUuid = $this->formSession->store($this->formStoreKey, $dataThatShouldBePostToApi);

        $process = $this->createProcess(true);
        $action = new ReviewStepAction($this->formSession, $this->authorisationService);

        // WHEN I confirm the form
        /** @var RedirectToRoute $result */
        $result = $action->execute($isPost, $process, new UpdateVtsContext($this->vtsId, $this->propertyName), $formUuid);

        // THEN A call to API is made to save data
        $this->assertGreaterThan(0, $this->updateVtsSpy->invocationCount(), "No call to API was made");

        // for a correct VTS
        $this->assertEquals($this->vtsId, $this->updateVtsSpy->paramsForLastInvocation()[0]);

        // with correct data
        $this->assertEquals($dataThatShouldBePostToApi, $this->updateVtsSpy->paramsForLastInvocation()[1]);

        // AND I'm redirected
        $this->assertInstanceOf(RedirectToRoute::class, $result);

        // to the overview page of a correct VTS
        $this->assertEquals(VtsRouteList::VTS, $result->getRouteName());
        $this->assertEquals($this->vtsId, $result->getRouteParams()['id']);

        // with a success message
        $this->assertGreaterThan(0, count($result->getSuccessMessages()), "No success message set");
        $this->assertEquals($this->successMessage, $result->getSuccessMessages()[0]);
    }

    public function test_storingFormTwiceCreatesDifferentUuids()
    {
        //todo this should be tested somewhere else, for store tests
        $uuid1 = $this->formSession->store($this->formStoreKey, []);
        $uuid2 = $this->formSession->store($this->formStoreKey, []);

        $this->assertNotEquals($uuid1, $uuid2);
    }

    public function testA()
    {
        // todo test null breadcrumbs in first step
    }

    public function testB()
    {
        // todo test null breadcrumbs in second step
    }

    private function createProcess($requiresReview)
    {
        $form = new Form();
        $form->add((new Text())->setName($this->formElementName));

        $input = new Input($this->formElementName);
        $input->setRequired(true);
        $input->getValidatorChain()->attach((new StringLength())->setMax(30));

        $form->setInputFilter((new InputFilter())->add($input));

        if ($requiresReview) {
            return new UpdateVtsReviewProcess(
                $this->propertyName,
                $this->requiredPermission,
                $requiresReview,
                $this->partial,
                $form,
                $this->submitButtonText,
                function () {
                    return [$this->formElementName => $this->oldValueFromDb];
                }, function ($vtsId, $formData) {
                $this->vtsUpdater->update($vtsId, $formData);
            },
                $this->successMessage,
                $this->pageTitle,
                function () {
                    return new GdsTable();
                },
                $this->reviewPageTitle,
                $this->pageSubTitle,
                $this->reviewPageLede,
                $this->reviewSubmitButtonText,
                $this->breadCrumb
            );
        } else {
            return new UpdateVtsPropertyProcess(
                $this->propertyName,
                $this->requiredPermission,
                $this->partial,
                $form,
                $this->submitButtonText,
                function () {
                    return [$this->formElementName => $this->oldValueFromDb];
                },
                function ($vtsId, $formData) {
                    $this->vtsUpdater->update($vtsId, $formData);
                },
                $this->successMessage,
                $this->pageTitle,
                $this->pageSubTitle,
                $this->breadCrumb
            );
        }
    }
}
