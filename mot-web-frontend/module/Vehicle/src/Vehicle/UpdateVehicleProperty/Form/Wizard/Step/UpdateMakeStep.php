<?php
namespace Vehicle\UpdateVehicleProperty\Form\Wizard\Step;

use Core\Action\RedirectToRoute;
use Core\FormWizard\LayoutData;
use Core\FormWizard\WizardContextInterface;
use Core\Routing\VehicleRouteList;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\ApiClient\Vehicle\Dictionary\MakeApiResource;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\TypeCheck;
use DvsaMotTest\Service\StartTestChangeService;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\MakeForm;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Context;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\UpdateVehiclePropertyViewModel;
use Zend\Form\Form;
use Zend\View\Helper\Url;

class UpdateMakeStep extends AbstractStep implements AutoWireableInterface
{
    const NAME = "make";
    const PAGE_TITLE = "Change make and model";
    const PAGE_TITLE_UPDATE_DURING_TEST = "What is the vehicle's make?";
    const PAGE_SUBTITLE_UPDATE_DURING_TEST = "Change vehicle record";
    const PARTIAL_EDIT_MAKE = "partials/edit-make.phtml";

    private $makeApiResource;
    private $breadcrumbsBuilder;
    private $tertiaryTitleBuilder;

    /** @var  StartTestChangeService */
    private $startTestChangeService;

    /**
     * @var UpdateVehicleContext
     */
    protected $context;

    public function __construct(
        Url $url,
        MakeApiResource $makeApiResource,
        VehicleEditBreadcrumbsBuilder $breadcrumbsBuilder,
        StartTestChangeService $startTestChangeService
    )
    {
        parent::__construct($url);

        $this->makeApiResource = $makeApiResource;
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->tertiaryTitleBuilder = new VehicleTertiaryTitleBuilder();
        $this->startTestChangeService = $startTestChangeService;
    }

    public function getName()
    {
        return self::NAME;
    }

    public function setContext(WizardContextInterface $context)
    {
        TypeCheck::assertInstance($context, UpdateVehicleContext::class);
        return parent::setContext($context);
    }

    protected function dataExists($formUuid)
    {
        return $this->formContainer->dataExists($this->getSessionStoreKey(), $formUuid);
    }

    /**
     * @param MakeForm $form
     * @return string
     */
    protected function saveData(Form $form, $formUuid)
    {
        TypeCheck::assertInstance($form, MakeForm::class);

        $data = [$this->getName() => array_merge($form->getData(), ["name" => $form->getSelectedMakeName()])];
        return $this->formContainer->store($this->getSessionStoreKey(), $data, $formUuid);
    }

    protected function createForm(array $formData = [])
    {
        $form = new MakeForm($this->makeApiResource->getList());
        $form->setData($formData);

        return $form;
    }

    public function getRoute(array $queryParams = [])
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return new RedirectToRoute(
                VehicleRouteList::VEHICLE_CHANGE_UNDER_TEST_MAKE_AND_MODEL,
                ["id" => $this->context->getObfuscatedVehicleId(), "property" => self::NAME],
                $queryParams
            );
        }

        return new RedirectToRoute(
            VehicleRouteList::VEHICLE_CHANGE_MAKE_AND_MODEL,
            ["id" => $this->context->getObfuscatedVehicleId(), "property" => self::NAME],
            $queryParams
        );
    }

    protected function getLayoutData()
    {
        $breadcrumbs = $this->getBreadcrumbs();
        $isUpdateUnderTest = $this->context->isUpdateVehicleDuringTest();

        $layoutData = new LayoutData();
        $layoutData->setBreadcrumbs($breadcrumbs);
        $layoutData->setPageTitle($isUpdateUnderTest ? self::PAGE_TITLE_UPDATE_DURING_TEST : self::PAGE_TITLE);
        $layoutData->setPageSubTitle($isUpdateUnderTest ? self::PAGE_SUBTITLE_UPDATE_DURING_TEST : "Vehicle");

        return $layoutData;
    }

    protected function createViewModel(Form $form, $formUuid)
    {
        $isUpdateUnderTest = $this->context->isUpdateVehicleDuringTest();
        $formActionUrl = $this->getRoute(["formUuid" => $formUuid])->toString($this->url);
        $veBackUrl = $this->getBackUrl($formUuid);
        $underTestBackUrl = $this->startTestChangeService->underTestReturnUrl($this->context->getObfuscatedVehicleId(), self::NAME);

        $tertiaryTitle = $this->getTertiaryTitleForVehicle();

        return (new UpdateVehiclePropertyViewModel())
            ->setForm($form)
            ->setSubmitButtonText("Continue")
            ->setPartial(self::PARTIAL_EDIT_MAKE)
            ->setBackUrl($isUpdateUnderTest ? $underTestBackUrl : $veBackUrl)
            ->setBackLinkText($this->getBackButtonText())
            ->setFormActionUrl($formActionUrl)
            ->setPageTertiaryTitle($tertiaryTitle);
    }

    protected function getPrePopulatedData()
    {
        $isUpdateUnderTest = $this->context->isUpdateVehicleDuringTest();
        $makeId = $this->getMakeId();

        if ($isUpdateUnderTest) {
            $makeIdFromSession = $this->startTestChangeService
                ->isMakeAndModelChanged()
                ? $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_MAKE)['makeId']
                : $makeId;

            if ($makeIdFromSession == 'other') {
                $makeNameFromSession = $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_MAKE)['makeName'];
                return [MakeForm::FIELD_MAKE_NAME => $makeIdFromSession, MakeForm::FIELD_OTHER_MAKE_NAME => $makeNameFromSession];
            }

            return [MakeForm::FIELD_MAKE_NAME => $makeIdFromSession];
        }

        return [MakeForm::FIELD_MAKE_NAME => $makeId];
    }

    protected function getBackRoute($formUuid = null)
    {
        return new RedirectToRoute(VehicleRouteList::VEHICLE_DETAIL, ["id" => $this->context->getObfuscatedVehicleId()]);
    }

    /**
     * Creates breadcrumbs for edit page.
     * Returning null means there are no breadcrumbs to display.
     *
     * @return array
     */
    private function getBreadcrumbs()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return $this->breadcrumbsBuilder->getChangeVehicleUnderTestBreadcrumbs($this->context->getObfuscatedVehicleId());
        }

        return $this->breadcrumbsBuilder->getVehicleEditBreadcrumbs(
            self::PAGE_TITLE,
            $this->context->getObfuscatedVehicleId());
    }

    /**
     * What should be displayed on the submit button control.
     *
     * @return string
     */
    private function getBackButtonText()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return "Back";
        }

        return "Cancel and return to vehicle";
    }

    private function getMakeId()
    {
        if ($this->startTestChangeService->isDvlaVehicle()) {
            return null;
        }

        if ($this->context->getVehicle()->getMake() !== null) {
            return $this->context->getVehicle()->getMake()->getId();
        }

        return null;
    }

    /**
     * @return \Core\ViewModel\Header\HeaderTertiaryList|string
     */
    private function getTertiaryTitleForVehicle()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return '';
        }

        return $this->tertiaryTitleBuilder->getTertiaryTitleForVehicle($this->context->getVehicle());
    }
}
