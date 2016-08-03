<?php
namespace DvsaMotTest\NewVehicle\Form\VehicleWizard;

use DvsaMotTest\NewVehicle\Container\NewVehicleContainer;
use DvsaMotTest\Service\AuthorisedClassesService;
use DvsaMotTest\NewVehicle\Fieldset\CreateVehicleStepOneFieldset;
use DvsaMotTest\NewVehicle\Form\CreateVehicleStepTwoForm;
use Core\Service\MotFrontendIdentityProviderInterface;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Enum\ColourCode;
use Application\Service\CatalogService;
use Zend\Form\Form;

class VehicleSpecificationStep extends AbstractStep implements WizardStep
{
    /** @var AuthorisedClassesService */
    private $authorisedClassesService;

    /** @var MotFrontendIdentityProviderInterface */
    private $identityProvider;

    public function __construct(
        NewVehicleContainer $container,
        Client $client,
        CatalogService $catalogService,
        AuthorisedClassesService $authorisedClassesService,
        MotFrontendIdentityProviderInterface $identityProvider
    )
    {
        parent::__construct($container, $client, $catalogService);

        $this->authorisedClassesService = $authorisedClassesService;
        $this->identityProvider = $identityProvider;
    }

    /**
     * @return string
     */
    public static function getName()
    {
        return "step_two";
    }

    /**
     * @return CreateVehicleStepTwoForm
     */
    public function createForm()
    {
        $form = new CreateVehicleStepTwoForm(
            [
                'vehicleData' => $this->getVehicleData()
            ]
        );

        return $form;
    }

    public function saveForm(Form $form)
    {
        $data = $this->getFormData($form);

        unset($data['submit']);
        unset($data['back']);

        $this->container->set(self::getName(), $data);
    }

    public function clearData()
    {
        $this->container->clear(self::getName());
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = $this->container->get(self::getName());

        if (!empty($data)) {
            return $data;
        }

        return ['vehicleForm' => ['secondaryColour' => ColourCode::NOT_STATED]];
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $data = $this->getData();

        $form = $this->createForm();
        $form->setData($data);

        return $form->isValid();
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getVehicleData()
    {
        $vehicleData = $this->getStaticData();

        $vehicleData['model'] = $this->getModels();

        $this->saveStaticData($vehicleData);

        $vehicleData['authorisedClasses'] = $this->getAuthorisedClassesForUserAndVTS();

        return $vehicleData;
    }

    /**
     * @param array $vehicleData
     * @return CreateVehicleStepTwoForm
     */
    public function createFormWithVehicleData(array $vehicleData)
    {
        $form = new CreateVehicleStepTwoForm(
            [
                'vehicleData' => $vehicleData
            ]
        );

        return $form;
    }

    /**
     * @return array
     */
    private function getModels()
    {
        $stepOneData = $this->getStepOneData();
        if (empty($stepOneData)) {
            return [];
        }

        $makeCode = $stepOneData['vehicleForm']['make'];
        $isMakeOther = $makeCode == CreateVehicleStepOneFieldset::LABEL_OTHER_KEY;
        $models = $isMakeOther ? [[]] :
            $this->client->get(UrlBuilder::vehicleDictionary()->make($makeCode)->models()->toString());

        return isset($models['data']) ? $models['data'] : [];
    }

    /**
     * @return array
     */
    private function getStepOneData()
    {
        return $this->getPrevStep()->getData();
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getAuthorisedClassesForUserAndVTS()
    {
        $key = 'authorised_classes';
        $authorisedClasses = $this->container->get($key);
        if (!empty($authorisedClasses)) {
            return $authorisedClasses;
        }

        $identity = $this->identityProvider->getIdentity();
        $userId = $identity->getUserId();

        $currentVts = $identity->getCurrentVts();
        if (!$currentVts) {
            throw new \Exception("VTS not found");
        }
        $siteId = $currentVts->getVtsId();

        $authorisedClassesCombined = $this->authorisedClassesService->getCombinedAuthorisedClassesForPersonAndVts(
            $userId,
            $siteId
        );

        $this->container->set($key,$authorisedClassesCombined);

        return $authorisedClassesCombined;
    }
}
