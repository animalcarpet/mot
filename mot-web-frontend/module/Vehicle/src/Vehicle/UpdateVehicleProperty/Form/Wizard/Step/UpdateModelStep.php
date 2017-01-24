<?php
namespace Vehicle\UpdateVehicleProperty\Form\Wizard\Step;

use Core\Action\RedirectToRoute;
use Core\FormWizard\LayoutData;
use Core\FormWizard\WizardContextInterface;
use Core\Routing\VehicleRouteList;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\ApiClient\Vehicle\Dictionary\ModelApiResource;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\TypeCheck;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaMotTest\Service\StartTestChangeService;
use PhpParser\Node\Expr\AssignOp\Mod;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\MakeForm;
use Vehicle\UpdateVehicleProperty\Form\ModelForm;
use Vehicle\UpdateVehicleProperty\Form\OtherModelForm;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Context;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\UpdateVehiclePropertyViewModel;
use Zend\Form\Form;
use Zend\View\Helper\Url;

class UpdateModelStep extends AbstractStep implements AutoWireableInterface
{
    const NAME = "model";
    const PARTIAL_EDIT_MODEL = "partials/edit-model.phtml";
    const PARTIAL_EDIT_OTHER_MODEL = "partials/edit-other-model.phtml";
    const PAGE_TITLE = 'Change make and model';
    const PAGE_SUBTITLE_UPDATE_DURING_TEST = "Change vehicle record";
    const PAGE_TITLE_UPDATE_DURING_TEST = "What is the vehicle's model?";

    private $modelApiResource;
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
        ModelApiResource $modelApiResource,
        VehicleEditBreadcrumbsBuilder $breadcrumbsBuilder,
        StartTestChangeService $startTestChangeService
    )
    {
        parent::__construct($url);

        $this->modelApiResource = $modelApiResource;
        $this->url = $url;
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
        $storedData = $this->getStoredData($formUuid);
        return (empty($storedData) === false);
    }

    protected function getLayoutData()
    {
        $breadcrumbs = $this->getBreadcrumbs();
        $isUpdateUnderTest = $this->context->isUpdateVehicleDuringTest();

        $layout = new LayoutData();
        $layout->setBreadcrumbs($breadcrumbs);
        $layout->setPageTitle($isUpdateUnderTest ? self::PAGE_TITLE_UPDATE_DURING_TEST : self::PAGE_TITLE);
        $layout->setPageSubTitle($isUpdateUnderTest ? self::PAGE_SUBTITLE_UPDATE_DURING_TEST : "Vehicle");

        return $layout;
    }

    protected function createViewModel(Form $form, $formUuid)
    {
        $isUpdateUnderTest = $this->context->isUpdateVehicleDuringTest();
        $formActionUrl = $this->getRoute(["formUuid" => $formUuid])->toString($this->url);
        $backUrl = $this->getBackUrl($formUuid);

        $tertiaryTitle = $this->getTertiaryTitleForVehicle();

        if ($this->hasOtherMakeId()) {
            $partial = self::PARTIAL_EDIT_OTHER_MODEL;
        } else {
            $partial = self::PARTIAL_EDIT_MODEL;
        }

        return (new UpdateVehiclePropertyViewModel())
            ->setForm($form)
            ->setSubmitButtonText($isUpdateUnderTest ? 'Continue' : "Review make and model")
            ->setPartial($partial)
            ->setBackUrl($backUrl)
            ->setBackLinkText("Back")
            ->setFormActionUrl($formActionUrl)
            ->setPageTertiaryTitle($tertiaryTitle);
    }

    protected function createForm(array $formData = [])
    {
        if ($this->hasOtherMakeId()) {
            $form = new OtherModelForm();
        } else {
            try {
                $modelList = $this->modelApiResource->getList($this->getMakeId());
            } catch (NotFoundException $e) {
                $modelList = [];
            }

            $form = new ModelForm($modelList, $this->getMakeName());
        }

        $form->setData($formData);

        return $form;
    }

    /**
     * @param OtherModelForm $form
     * @param string $formUuid
     * @return string
     */
    protected function saveData(Form $form, $formUuid)
    {
        TypeCheck::assertInstance($form, OtherModelForm::class);

        if ($this->context->isUpdateVehicleDuringTest()) {
            $modelId = $this->getModelId($form);
            $modelName = $this->getModelName($form);
            $makeId = $this->getMakeId();
            $makeName = $this->getMakeName();

            if ($this->context->isUpdateVehicleDuringTest()) {
                $this->startTestChangeService
                    ->saveChange(StartTestChangeService::CHANGE_MAKE, [
                        'makeName' => $this->getMakeName(),
                        'makeId' => $this->getMakeId(),
                    ]);
                $this->startTestChangeService->updateChangedValueStatus(StartTestChangeService::CHANGE_MAKE, true);
            }

            $this->startTestChangeService
                ->saveChange(StartTestChangeService::CHANGE_MODEL, [
                    'modelId' => $modelId,
                    'modelName'  => $modelName,
                ]);
            $this->startTestChangeService->updateChangedValueStatus(StartTestChangeService::CHANGE_MODEL, true);
        }

        $data = $this->getAllStoredData($formUuid);
        $data[$this->getName()] = array_merge($form->getData(), ["name" => $form->getSelectedModelName()]);

        return $this->formContainer->store($this->getSessionStoreKey(), $data, $formUuid);
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

    protected function getPrePopulatedData()
    {
        $isUpdateUnderTest = $this->context->isUpdateVehicleDuringTest();

        if ($isUpdateUnderTest) {
            // need to default to please select when updating under test
            return [ModelForm::FIELD_MODEL_NAME => ''];
        } else {
            $makeId = ($this->context->getVehicle()->getMake() === null)? null : $this->context->getVehicle()->getMake()->getId();
            $modelId = ($this->context->getVehicle()->getModel() === null)? null : $this->context->getVehicle()->getModel()->getId();

            $modelId = $this->getMakeId() == $makeId ? $modelId : null;

            return [ModelForm::FIELD_MODEL_NAME => $modelId];
        }
    }

    private function hasOtherMakeId()
    {
        return ($this->getMakeId() === MakeForm::OTHER_ID);
    }

    private function getMakeId()
    {
        $data = $this->getPrevStep()->getStoredData($this->formUuid);
        if (array_key_exists(MakeForm::FIELD_MAKE_NAME, $data)) {
            return $data[MakeForm::FIELD_MAKE_NAME];
        }

        return null;
    }

    private function getMakeName()
    {
        $data = $this->getPrevStep()->getStoredData($this->formUuid);

        if ($this->context->isUpdateVehicleDuringTest()) {
            if ($data['name'] == 'OTHER') {
                return $data['otherMake'];
            }

            return $data['name'];
        }

        if (array_key_exists("name", $data)) {
            return $data["name"];
        }

        return "";
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
            return $this->breadcrumbsBuilder->getChangeVehicleUnderTestBreadcrumbs(
                $this->context->getObfuscatedVehicleId());
        }

        return $this->breadcrumbsBuilder->getVehicleEditBreadcrumbs(
            self::PAGE_TITLE,
            $this->context->getObfuscatedVehicleId());
    }

    /**
     * @param Form $form
     *
     * @return mixed|null|string
     */
    private function getModelId(Form $form)
    {
        if ($form instanceof ModelForm) {
            return $form->getModelElement()->getValue();
        }

        if ($form instanceof OtherModelForm) {
            return OtherModelForm::FIELD_OTHER_MODEL_ID;
        }

        return null;
    }

    /**
     * @param Form $form
     *
     * @return mixed|null|string
     */
    private function getModelName(Form $form)
    {
        if ($form instanceof ModelForm) {
            if ($form->getSelectedModelName() === 'OTHER') {
                return $form->get(ModelForm::FIELD_OTHER_MODEL_ID)->getValue();
            }

            return $form->getSelectedModelName();
        }

        if ($form instanceof OtherModelForm) {
            return $form->getOtherModelElement()->getValue();
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
