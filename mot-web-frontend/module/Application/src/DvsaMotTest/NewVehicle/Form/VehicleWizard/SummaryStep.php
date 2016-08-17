<?php
namespace DvsaMotTest\NewVehicle\Form\VehicleWizard;

use Dvsa\Mot\ApiClient\Request\CreateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Exception\OtpApplicationException;
use DvsaCommon\Model\FuelTypeAndCylinderCapacity;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;
use DvsaMotTest\NewVehicle\Fieldset\CreateVehicleStepOneFieldset;
use DvsaMotTest\NewVehicle\Fieldset\CreateVehicleStepTwoFieldset;
use DvsaMotTest\NewVehicle\Form\CreateVehicleStepThreeForm;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Element\DateSelect;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaMotTest\NewVehicle\Container\NewVehicleContainer;
use DvsaCommon\HttpRestJson\Client;
use Application\Service\CatalogService;
use Core\Service\MotFrontendIdentityProviderInterface;
use Application\Service\ContingencySessionManager;

class SummaryStep extends AbstractStep implements WizardStep
{
    /**
     * @var MotFrontendIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var VehicleService
     */
    private  $vehicleService;

    /**
     * @var ContingencySessionManager
     */
    private $contingencySessionManager;

    public function __construct(
        NewVehicleContainer $container,
        Client $client,
        CatalogService $catalogService,
        MotFrontendIdentityProviderInterface $identityProvider,
        VehicleService $vehicleService,
        ContingencySessionManager $contingencySessionManager
    ) {
        parent::__construct($container, $client, $catalogService);

        $this->identityProvider = $identityProvider;
        $this->vehicleService = $vehicleService;
        $this->contingencySessionManager = $contingencySessionManager;
    }

    /**
     * @return string
     */
    public static function getName()
    {
        return "step_three";
    }

    /**
     * @return CreateVehicleStepThreeForm
     */
    public function createForm()
    {
        return new CreateVehicleStepThreeForm();
    }


    /**
     * @param Form $form
     * @return array
     * @throws OtpApplicationException
     */
    public function saveForm(Form $form)
    {
        $oneTimePassword = $form->get('oneTimePassword')->getValue();

        $vehicle = $this->vehicleService->createDvsaVehicle(
            $this->prepareCreateVehicleRequest($oneTimePassword)
        );

        $motTest = $this->createMotTestForVehicle($vehicle, $oneTimePassword);

        $startedMotTestNumber = $motTest['data']['motTestNumber'];

        return [
            'isMotContingency' => $this->contingencySessionManager->isMotContingency(),
            'vehicle' => $vehicle,
            'startedMotTestNumber' => $startedMotTestNumber,
        ];
    }

    public function clearData()
    {
        $this->container->clear(AbstractStep::API_DATA);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $staticData = $this->getStaticData();

        return [
            'sectionOneData' => $this->prepareStepOneConfirmViewData($staticData),
            'sectionTwoData' => $this->prepareStepTwoConfirmViewData($staticData)
        ];
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    /**
     * @param array $vehicleData
     * @return array
     */
    private function prepareStepOneConfirmViewData(array &$vehicleData)
    {
        $otherKey = CreateVehicleStepOneFieldset::LABEL_OTHER_KEY;
        $stepOneData = $this->getVehicleIdentificationStepData();
        if ($stepOneData['vehicleForm']['make'] == $otherKey) {
            unset ($vehicleData['make']);
        }

        $form = $this->getVehicleIdentificationStep()->createForm();
        $form->populateValues($stepOneData);

        /** @var CreateVehicleStepOneFieldset $elements */
        $elements = $form->getFieldsets()['vehicleForm'];
        $fCor = $elements->get('countryOfRegistration');
        $fVrm = $elements->get('registrationNumber');
        $fEmptyVrmReason = $elements->get('emptyVrmReason');
        $fVin = $elements->get('VIN');
        $fEmptyVinReason = $elements->get('emptyVinReason');
        $fMake = $elements->get('make');
        $fMakeOther = $elements->get('makeOther');
        $fDateOfFirstUse = $elements->get('dateOfFirstUse');
        $fTransmissionType = $elements->get('transmissionType');

        $buildItem = self::confirmationItemBuilder();
        $data = [];
        $data[] = $this->getOptionLabelValuePair($fCor);
        $data[] = $buildItem(
            $fVrm->getValue() ? $fVrm->getName() : $fEmptyVrmReason->getName(),
            $fVrm->getLabel(),
            $fVrm->getValue() ?: $this->getSelectedOptionValue($fEmptyVrmReason)
        );
        $data[] = $buildItem(
            $fVin->getValue() ? $fVin->getName() : $fEmptyVinReason->getName(),
            $fVin->getLabel(),
            $fVin->getValue() ?: $this->getSelectedOptionValue($fEmptyVinReason)
        );
        $data[] = $buildItem(
            $fMake->getName(),
            $fMake->getLabel(),
            $fMake->getValue() === $otherKey ? $fMakeOther->getValue() : $this->getSelectedOptionValue($fMake)
        );
        $data[] = $this->getDatePair($fDateOfFirstUse);
        $data[] = $this->getOptionLabelValuePair($fTransmissionType);

        return $data;
    }

    /**
     * @param array $vehicleData
     * @return array
     */
    private function prepareStepTwoConfirmViewData(array &$vehicleData)
    {
        $otherKey = CreateVehicleStepOneFieldset::LABEL_OTHER_KEY;

        $stepTwoData = $this->getVehicleSpecificationStepData();
        if ($stepTwoData['vehicleForm']['model'] == CreateVehicleStepTwoFieldset::LABEL_OTHER_KEY) {
            unset ($vehicleData['model']);
        }

        $form = $this->getVehicleSpecificationStep()->createForm();
        $form->populateValues($stepTwoData);

        $elems = $form->getFieldsets()['vehicleForm'];

        $data = [];
        $fModel = $elems->get('model');
        $fModelOther = $elems->get('modelOther');
        $fFuelType = $elems->get('fuelType');
        $fCylinderCapacity = $elems->get('cylinderCapacity');
        $fTestClass = $elems->get('vehicleClass');
        $fColour1 = $elems->get('colour');
        $fColour2 = $elems->get('secondaryColour');

        $buildItem = self::confirmationItemBuilder();

        $data[] = $buildItem(
            $fModel->getName(),
            $fModel->getLabel(),
            $fModel->getValue() === $otherKey ? $fModelOther->getValue() : $this->getSelectedOptionValue($fModel)
        );

        $data[] = $this->getOptionLabelValuePair($fFuelType);
        if (strlen($fCylinderCapacity->getValue()) > 0) {
            $data[] = $buildItem(
                $fCylinderCapacity->getName(),
                $fCylinderCapacity->getLabel(),
                $fCylinderCapacity->getValue()
            );
        }

        $data[] = $this->getOptionLabelValuePair($fTestClass);
        $data[] = $this->getOptionLabelValuePair($fColour1);
        $data[] = $this->getOptionLabelValuePair($fColour2);

        return $data;
    }

    /**
     * @return callable
     */
    private static function confirmationItemBuilder()
    {
        return function ($id, $label, $value) {
            return ['id' => $id, 'label' => $label, 'value' => $value];
        };
    }

    /**
     * @param DateSelect $f
     * @return array
     */
    private function getDatePair(DateSelect $f)
    {
        $date = (new \DateTime())->setDate(
            $f->getYearElement()->getValue(),
            $f->getMonthElement()->getValue(),
            $f->getDayElement()->getValue()
        );

        $buildItem = self::confirmationItemBuilder();

        return $buildItem($f->getName(), $f->getLabel(), DateTimeDisplayFormat::date($date));
    }

    private function getOptionLabelValuePair(Element $f)
    {
        $buildItem = self::confirmationItemBuilder();

        return $buildItem($f->getName(), $f->getLabel(), $this->getSelectedOptionValue($f));
    }

    private function getSelectedOptionValue($field)
    {
        return $field->getValueOptions()[$field->getValue()];
    }

    /**
     * @param $oneTimePassword
     * @return CreateDvsaVehicleRequest
     */
    private function prepareCreateVehicleRequest($oneTimePassword)
    {
        $stepOneData = $this->getVehicleIdentificationStepData();
        $stepTwoData = $this->getVehicleSpecificationStepData();

        //Amending variable keys to pass through the Vehicle Model
        $stepTwoData['vehicleForm']['testClass'] = $stepTwoData['vehicleForm']['vehicleClass'];

        foreach ($stepOneData['vehicleForm'] as $k => $v) {
            if (empty($v)) {
                $v = null;
            }
            $stepOneData['vehicleForm'][$k] = $v;
        }

        //Merging forms data
        $stepsData = array_merge(
            $stepOneData['vehicleForm'],
            $stepTwoData['vehicleForm']
        );

        $OTHER_MAKE_OR_MODEL_ID = -1;
        $makeId = $stepsData['make'] === CreateVehicleStepOneFieldset::LABEL_OTHER_KEY ? $OTHER_MAKE_OR_MODEL_ID : $stepsData['make'];
        $modelId = $stepsData['model'] === CreateVehicleStepTwoFieldset::LABEL_OTHER_KEY ? $OTHER_MAKE_OR_MODEL_ID : $stepsData['model'];

        $createVehicleRequest = new CreateDvsaVehicleRequest();
        $createVehicleRequest
            ->setOneTimePassword($oneTimePassword)
            ->setColourId($stepsData['colour'])
            ->setCountryOfRegistrationId($stepsData['countryOfRegistration'])
            ->setFirstUsedDate(new \DateTime(vsprintf('%04d-%02d-%02d',array_reverse($stepsData['dateOfFirstUse']))))
            ->setFuelTypeId($stepsData['fuelType'])
            ->setMakeId($makeId)
            ->setModelId($modelId)
            ->setSecondaryColourId($stepsData['secondaryColour'])
            ->setVehicleClassId($stepsData['testClass'])
            ->setTransmissionTypeId($stepsData['transmissionType']);

        if (in_array(
            $stepsData['fuelType'],
            FuelTypeAndCylinderCapacity::getAllFuelTypeIdsWithCompulsoryCylinderCapacity())
        ) {
            $createVehicleRequest->setCylinderCapacity($stepsData['cylinderCapacity']);
        }

        if (isset($stepsData['makeOther'])) {
            $createVehicleRequest->setMakeOther($stepsData['makeOther']);
        }

        if (isset($stepsData['modelOther'])) {
            $createVehicleRequest->setModelOther($stepsData['modelOther']);
        }

        if (isset($stepsData['emptyVinReason'])) {
            $createVehicleRequest->setEmptyVinReasonId($stepsData['emptyVinReason']);
        } else {
            $createVehicleRequest->setVin($stepsData['VIN']);
        }

        if (isset($stepsData['emptyVrmReason'])) {
            $createVehicleRequest->setEmptyVrmReasonId($stepsData['emptyVrmReason']);
        } else {
            $createVehicleRequest->setRegistration($stepsData['registrationNumber']);
        }

        return $createVehicleRequest;
    }

    /**
     * @return array
     */
    private function getVehicleIdentificationStepData()
    {
        return $this->getVehicleIdentificationStep()->getData();
    }

    /**
     * @return array
     */
    private function getVehicleSpecificationStepData()
    {
        return $this->getVehicleSpecificationStep()->getData();
    }

    /**
     * @return WizardStep
     */
    private function getVehicleIdentificationStep()
    {
        return $this->getVehicleSpecificationStep()->getPrevStep();
    }

    /**
     * @return WizardStep
     */
    private function getVehicleSpecificationStep()
    {
        return $this->getPrevStep();
    }

    /**
     * @param DvsaVehicle $vehicle
     * @param $oneTimePassword
     * @return mixed|string
     */
    private function createMotTestForVehicle(DvsaVehicle $vehicle, $oneTimePassword)
    {
        $apiUrl = MotTestUrlBuilder::motTest();

        $newTestData = $this->prepareNewTestData($vehicle, $oneTimePassword);

        return $this->client->post($apiUrl, $newTestData);
    }

    /**
     * @param DvsaVehicle $vehicle
     * @param $oneTimePassword
     * @return array
     */
    private function prepareNewTestData(DvsaVehicle $vehicle, $oneTimePassword)
    {
        $vehicleTestingStationId = $this->identityProvider->getIdentity()->getCurrentVts()->getVtsId();
        $hasRegistration = is_null($vehicle->getEmptyVrmReason());

        $primaryColour = $this->evalColourCodeEnumsBasedOnTheColourName($vehicle->getColour());
        $secondaryColour = $this->evalColourCodeEnumsBasedOnTheColourName($vehicle->getColourSecondary());
        $fuelTypeId = $this->evalFuelTypeCodeEnumsBasedOnTheFuelName($vehicle->getFuelType());

        $data = [
            'vehicleId' => $vehicle->getId(),
            'primaryColour' => $primaryColour,
            'secondaryColour' => $secondaryColour,
            'fuelTypeId' => $fuelTypeId,
            'vehicleClassCode' => $vehicle->getVehicleClass(),
            'vehicleTestingStationId' => $vehicleTestingStationId,
            'hasRegistration' => $hasRegistration,
            'oneTimePassword' => $oneTimePassword,
            'motTestType' => MotTestTypeCode::NORMAL_TEST,
        ];

        if ($this->contingencySessionManager->isMotContingency()) {
            $contingencySession = $this->contingencySessionManager->getContingencySession();
            $data += [
                'contingencyId'     => $contingencySession['contingencyId'],
                'contingencyDto'    => DtoHydrator::dtoToJson($contingencySession['dto']),
            ];
        }

        return $data;
    }

    /**
     * @TODO (ABN) Once the new API accepts MOT-TEST creation calls all this mess will be gone!
     *             and hopefully the rest of the useless single dimension enums, or make them smarter
     *
     * @param string $enumPath
     * @param string $enumName
     * @return string
     */
    private function evalEnumByName($enumPath, $enumName)
    {
        $enumNameSpecialCharsRemoved = str_replace(['(', ')', ',', ':', '\'', '.', '?'], '', $enumName);
        $enumNameWithUnderscores = str_replace([' ', ' - ', '-', '/'], '_', $enumNameSpecialCharsRemoved);

        return constant(
            $enumPath . '::' .
            strtoupper($enumNameWithUnderscores)
        );
    }

    /**
     * @TODO (ABN) Same as evalEnumByName()!!
     *
     * @param string $colorName
     * @return string enum name from ColourCode
     */
    private function evalColourCodeEnumsBasedOnTheColourName($colorName)
    {
        return $this->evalEnumByName(ColourCode::class, $colorName);
    }

    /**
     * @TODO (ABN) Same as evalEnumByName()!!
     *
     * @param string $FuelTypeName
     * @return string enum name from ColourCode
     */
    private function evalFuelTypeCodeEnumsBasedOnTheFuelName($FuelTypeName)
    {
        return $this->evalEnumByName(FuelTypeCode::class, $FuelTypeName);
    }
}
