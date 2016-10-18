<?php

namespace DvsaMotTest\NewVehicle\Controller;

use Application\Service\CanTestWithoutOtpService;
use Application\Service\ContingencySessionManager;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Model\FuelTypeAndCylinderCapacity;
use DvsaMotTest\NewVehicle\Form\CreateVehicleStepThreeForm;
use DvsaMotTest\NewVehicle\Form\VehicleWizard\CreateVehicleFormWizard;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use DvsaMotTest\NewVehicle\Form\VehicleWizard\SummaryStep;
use DvsaMotTest\NewVehicle\Form\VehicleWizard\VehicleIdentificationStep;
use DvsaMotTest\NewVehicle\Form\VehicleWizard\VehicleSpecificationStep;
use GuzzleHttp\Exception\ClientException;
use Zend\Form\Element;
use Zend\Form\Element\DateSelect;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;
use Zend\Validator\NotEmpty;
use Zend\View\Model\ViewModel;

/**
 * @description Vehicle Generation as part of the MOT TEST process
 */
class CreateVehicleController extends AbstractDvsaMotTestController
{
    const ROUTE = 'vehicle-step';

    const STEP_ACTION_NAME_ONE = 'add-step-one';
    const STEP_ACTION_NAME_TWO = 'add-step-two';
    const STEP_ACTION_NAME_CONFIRM = 'confirm';

    const FIELD_NAME_VRM = 'reg';

    /** @var MotAuthorisationServiceInterface */
    private $authorisationService;

    /** @var  CreateVehicleFormWizard */
    private $wizard;

    /**
     * @var ContingencySessionManager
     */
    private $contingencySessionManager;

    /**
     * @var CanTestWithoutOtpService
     */
    private $canTestWithoutOtpService;

    public function __construct(
        CreateVehicleFormWizard $wizard,
        MotAuthorisationServiceInterface $authorisationService,
        Request $request,
        ContingencySessionManager $contingencySessionManager,
        CanTestWithoutOtpService $canTestWithoutOtpService
    ) {
        $this->authorisationService = $authorisationService;
        $this->request = $request;
        $this->wizard = $wizard;
        $this->contingencySessionManager = $contingencySessionManager;
        $this->canTestWithoutOtpService = $canTestWithoutOtpService;
    }

    public function onDispatch(MvcEvent $e)
    {
        $this->assertGranted(PermissionInSystem::VEHICLE_CREATE);
        parent::onDispatch($e);
    }

    public function indexAction()
    {
        $this->wizard->clear();

        return $this->redirect()->toRoute(
            self::ROUTE,
            [
                'action' => self::STEP_ACTION_NAME_ONE
            ],
            [
                'query' => [
                    self::FIELD_NAME_VRM => $this->request->getQuery(self::FIELD_NAME_VRM)
                ]
            ]
        );
    }

    public function cancelAction()
    {
        $this->wizard->clear();
        return $this->redirect()->toRoute('vehicle-search');
    }

    public function addStepOneAction()
    {
        $step = $this->wizard->getStep(VehicleIdentificationStep::getName());
        $form = $step->createForm();

        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());
            if ($form->isValid()) {
                $step->saveForm($form);
                $this->redirectToStepTwoAction();
            }
        } else {
            if ($this->params()->fromQuery(self::FIELD_NAME_VRM)) {
                $form->populateValues($form->setData([
                    'vehicleForm' =>
                        ['registrationNumber' => $this->params()->fromQuery(self::FIELD_NAME_VRM)]
                ]));
            }
            $form->populateValues($step->getData());
        }

        return (new ViewModel(['form' => $form]))->setTemplate('dvsa-mot-test/create-vehicle/add-step-one');
    }


    public function addStepTwoAction()
    {
        $this->redirectToSuitableStepsIfRequired(self::STEP_ACTION_NAME_TWO);

        $step = $this->wizard->getStep(VehicleSpecificationStep::getName());
        $form = $step->createForm();

        if ($this->request->isPost()) {
            if ($this->IsSubmittedBackButton()) {
                $form->setData($this->request->getPost());
                $step->saveForm($form);
                $this->redirectToStepOneAction();
            } else {
                $form->setData($this->request->getPost());
                if ($form->isValid()) {
                    $step->saveForm($form);
                    return $this->redirectToConfirmAction();
                }
            }
        }

        $form->populateValues($step->getData());
        $cylinderCapacityRequired = FuelTypeAndCylinderCapacity::getAllFuelTypeCodesWithCompulsoryCylinderCapacityAsString();

        return (new ViewModel(
            [
                'form' => $form,
                'ccRequiredFuelType' => $cylinderCapacityRequired,
            ]
        ))->setTemplate('dvsa-mot-test/create-vehicle/add-step-two');
    }

    public function confirmAction()
    {
        $this->redirectToSuitableStepsIfRequired(self::STEP_ACTION_NAME_CONFIRM);

        $step = $this->wizard->getStep(SummaryStep::getName());

        $otpErrorData = [];
        $otpErrorMessage = null;
        $otpShortMessage = null;

        if ($this->request->isPost()) {

            if ($this->IsSubmittedBackButton()) {

                $this->redirectToStepTwoAction();

            } else {

                $form = $step->createForm();
                $form->setData($this->request->getPost());

                $canCreateVehicleWithoutOtp = $this->canTestWithoutOtpService->canTestWithoutOtp();

                if ($canCreateVehicleWithoutOtp || $form->isValid()) {
                    try {
                        $result = $step->saveForm($form);
                        $isMotContingency = $result['isMotContingency'];
                        $motTestNumber = $result['startedMotTestNumber'];
                        $this->wizard->clear();
                        if ($isMotContingency) {
                            return $this->redirect()->toRoute("mot-test", ["motTestNumber" => $motTestNumber]);
                        } else {
                            return $this->redirect()->toRoute("mot-test/options", ["motTestNumber" => $motTestNumber]);
                        }
                    } catch (ValidationException $ve) {
                        $this->flashMessenger()->addMessage($ve->getDisplayMessages());
                    } catch (ClientException $ce) {
                        if ($ce->getCode() === Response::STATUS_CODE_403) {
                            $this->addErrorMessage(CreateVehicleStepThreeForm::VALIDATION_MESSAGE_INCORRECT_OTP);
                            $otpErrorMessage = CreateVehicleStepThreeForm::VALIDATION_MESSAGE_INCORRECT_OTP;
                        } else {
                            throw $ce;
                        }
                    }
                } else {
                    $formMessages= $form->getInputFilter()->getMessages();
                    $emptyOtpValidationMessage =
                        $formMessages[CreateVehicleStepThreeForm::FIELD_NAME_OTP][NotEmpty::IS_EMPTY];

                    $this->flashMessenger()->addMessage(
                        $emptyOtpValidationMessage
                    );

                    $otpErrorMessage = $emptyOtpValidationMessage;
                    $this->addErrorMessage($emptyOtpValidationMessage);
                }

            }
        }

        $stepData = $step->getData();
        $canCreateVehicleWithoutOtp = $this->canTestWithoutOtpService->canTestWithoutOtp();

        return (new ViewModel([
            'sectionOneData' => $stepData['sectionOneData'],
            'sectionTwoData' => $stepData['sectionTwoData'],
            'otpErrorData' => $otpErrorData,
            'canTestWithoutOtp' => $canCreateVehicleWithoutOtp,
            'otpErrorMessage' => $otpErrorMessage,
            'otpErrorShortMessage' => $otpShortMessage,
        ]))->setTemplate('dvsa-mot-test/create-vehicle/confirm');
    }

    public function completeAction()
    {
        return (new ViewModel([]))->setTemplate('dvsa-mot-test/create-vehicle/complete');
    }

    /**
     * This will handle undesirable attempts to get to an step without satisfying its previous step(s)
     *
     * @param string $fromStep the requested step
     */
    private function redirectToSuitableStepsIfRequired($fromStep)
    {
        $isStepOneValid = $this->wizard->isStepValid(VehicleIdentificationStep::getName());
        $isStepTwoValid = $this->wizard->isStepValid(VehicleSpecificationStep::getName());

        switch ($fromStep) {
            case self::STEP_ACTION_NAME_TWO :
                if (!$isStepOneValid) {
                    $this->redirectToStepOneAction();
                }
                break;
            case self::STEP_ACTION_NAME_CONFIRM :
                if (!$isStepOneValid) {
                    $this->redirectToStepOneAction();

                } elseif (!$isStepTwoValid) {
                    $this->redirectToStepTwoAction();
                }
                break;
        }
    }

    /**
     * Redirect to the given action in this controller
     * @param $actionName
     * @return \Zend\Http\Response
     */
    private function redirectToAction($actionName)
    {
        return $this->redirect()->toRoute(self::ROUTE, ['action' => $actionName]);
    }

    /**
     * @return \Zend\Http\Response
     */
    private function redirectToStepOneAction()
    {
        return $this->redirectToAction(self::STEP_ACTION_NAME_ONE);
    }

    /**
     * @return \Zend\Http\Response
     */
    private function redirectToStepTwoAction()
    {
        return $this->redirectToAction(self::STEP_ACTION_NAME_TWO);
    }

    /**
     * @return \Zend\Http\Response
     */
    private function redirectToConfirmAction()
    {
        return $this->redirectToAction(self::STEP_ACTION_NAME_CONFIRM);
    }

    /**
     * @return bool
     */
    private function IsSubmittedBackButton()
    {
        return $this->request->isPost() && $this->request->getPost('back');
    }
}
