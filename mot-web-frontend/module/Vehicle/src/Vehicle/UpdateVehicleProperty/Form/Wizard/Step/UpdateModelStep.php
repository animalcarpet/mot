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

    private $modelApiResource;
    private $breadcrumbsBuilder;
    private $tertiaryTitleBuilder;

    /**
     * @var Context
     */
    protected $context;

    public function __construct(
        Url $url,
        ModelApiResource $modelApiResource,
        VehicleEditBreadcrumbsBuilder $breadcrumbsBuilder
    )
    {
        parent::__construct($url);

        $this->modelApiResource = $modelApiResource;
        $this->url = $url;
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->tertiaryTitleBuilder = new VehicleTertiaryTitleBuilder();
    }

    public function getName()
    {
        return self::NAME;
    }

    public function setContext(WizardContextInterface $context)
    {
        TypeCheck::assertInstance($context, Context::class);
        return parent::setContext($context);
    }

    protected function dataExists($formUuid)
    {
        $storedData = $this->getStoredData($formUuid);
        return (empty($storedData) === false);
    }

    protected function getLayoutData()
    {
        $breadcrumbs = $this
            ->breadcrumbsBuilder
            ->getVehicleEditBreadcrumbs("Change make and model", $this->context->getObfuscatedVehicleId());

        $layout = new LayoutData();
        $layout->setBreadcrumbs($breadcrumbs);
        $layout->setPageTitle("Change make and model");
        $layout->setPageSubTitle("Vehicle");

        return $layout;
    }

    protected function createViewModel(Form $form, $formUuid)
    {
        $formActionUrl = $this->getRoute(["formUuid" => $formUuid])->toString($this->url);
        $backUrl = $this->getBackUrl($formUuid);

        $tertiaryTitle = $this->tertiaryTitleBuilder->getTertiaryTitleForVehicle($this->context->getVehicle());

        if ($this->hasOtherMakeId()) {
            $partial = self::PARTIAL_EDIT_OTHER_MODEL;
        } else {
            $partial = self::PARTIAL_EDIT_MODEL;
        }

        return (new UpdateVehiclePropertyViewModel())
            ->setForm($form)
            ->setSubmitButtonText("Review make and model")
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

        $data = $this->getAllStoredData($formUuid);
        $data[$this->getName()] = array_merge($form->getData(), ["name" => $form->getSelectedModelName()]);

        return $this->formContainer->store($this->getSessionStoreKey(), $data, $formUuid);
    }

    public function getRoute(array $queryParams = [])
    {
        return new RedirectToRoute(
            VehicleRouteList::VEHICLE_CHANGE_MAKE_AND_MODEL,
            ["id" => $this->context->getObfuscatedVehicleId(), "property" => self::NAME],
            $queryParams
        );
    }

    protected function getPrePopulatedData()
    {
        $makeId = ($this->context->getVehicle()->getMake() === null)? null : $this->context->getVehicle()->getMake()->getId();
        $modelId = ($this->context->getVehicle()->getModel() === null)? null : $this->context->getVehicle()->getModel()->getId();

        $modelId = $this->getMakeId() == $makeId ? $modelId : null;

        return [ModelForm::FIELD_MODEL_NAME => $modelId];
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
        if (array_key_exists("name", $data)) {
            return $data["name"];
        }

        return "";
    }
}
