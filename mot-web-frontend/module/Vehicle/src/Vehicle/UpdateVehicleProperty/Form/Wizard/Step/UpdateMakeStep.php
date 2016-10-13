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
    const PARTIAL_EDIT_MAKE = "partials/edit-make.phtml";

    private $makeApiResource;
    private $breadcrumbsBuilder;
    private $tertiaryTitleBuilder;

    /**
     * @var Context
     */
    protected $context;

    public function __construct(
        Url $url,
        MakeApiResource $makeApiResource,
        VehicleEditBreadcrumbsBuilder $breadcrumbsBuilder
    )
    {
        parent::__construct($url);

        $this->makeApiResource = $makeApiResource;
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
        return $this->formContainer->dataExists($this->getSessionStoreKey(), $formUuid);
    }

    /**
     * @param MakeForm $form
     * @param $formUuid
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
        return new RedirectToRoute(
            VehicleRouteList::VEHICLE_CHANGE_MAKE_AND_MODEL,
            ["id" => $this->context->getObfuscatedVehicleId(), "property" => self::NAME],
            $queryParams
        );
    }

    protected function getLayoutData()
    {
        $breadcrumbs = $this
            ->breadcrumbsBuilder
            ->getVehicleEditBreadcrumbs("Change make and model", $this->context->getObfuscatedVehicleId());

        $layoutData = new LayoutData();
        $layoutData->setBreadcrumbs($breadcrumbs);
        $layoutData->setPageTitle("Change make and model");
        $layoutData->setPageSubTitle("Vehicle");

        return $layoutData;
    }

    protected function createViewModel(Form $form, $formUuid)
    {
        $formActionUrl = $this->getRoute(["formUuid" => $formUuid])->toString($this->url);
        $backUrl = $this->getBackUrl($formUuid);

        $tertiaryTitle = $this->tertiaryTitleBuilder->getTertiaryTitleForVehicle($this->context->getVehicle());

        return (new UpdateVehiclePropertyViewModel())
            ->setForm($form)
            ->setSubmitButtonText("Continue")
            ->setPartial(self::PARTIAL_EDIT_MAKE)
            ->setBackUrl($backUrl)
            ->setFormActionUrl($formActionUrl)
            ->setPageTertiaryTitle($tertiaryTitle);
    }

    protected function getPrePopulatedData()
    {
        $makeId = ($this->context->getVehicle()->getMake() !== null)? $this->context->getVehicle()->getMake()->getId() : null;

        return [MakeForm::FIELD_MAKE_NAME => $makeId];
    }

    protected function getBackRoute($formUuid = null)
    {
        return new RedirectToRoute(VehicleRouteList::VEHICLE_DETAIL, ["id" => $this->context->getObfuscatedVehicleId()]);
    }
}
