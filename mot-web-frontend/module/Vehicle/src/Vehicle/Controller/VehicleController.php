<?php

namespace Vehicle\Controller;

use Application\Service\CatalogService;
use Core\Controller\AbstractAuthActionController;
use Dvsa\Mot\ApiClient\Resource\Item\SearchVehicle;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaClient\Mapper\VehicleExpiryMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use DvsaCommon\UrlBuilder\VehicleUrlBuilderWeb;
use GuzzleHttp\Exception\ClientException;
use Vehicle\Helper\VehicleViewModelBuilder;
use Vehicle\Service\VehicleSearchService;
use Zend\View\Model\ViewModel;

/**
 * Class VehicleController.
 *
 * This class is responsible for coordinating the different search methods to user so they can quickly locate an
 * existing VTS station, vehicle or other entity.
 */
class VehicleController extends AbstractAuthActionController implements AutoWireableInterface
{
    const BACK_TO_DETAIL = 'detail';
    const BACK_TO_RESULT = 'result';
    const BACK_TO_SEARCH = 'search';
    const PARAM_BACK_TO = 'backTo';
    const SEARCH_RETUREND_ONE_RESULT = 'oneResult';
    const ERR_MSG_INVALID_VEHICLE_ID = 'No Vehicle Id provided';
    const FORM_ERROR = 'Unable to find Vehicle';
    const NO_RESULT_FOUND = 'Search term(s) not found...';

    protected $paramObfuscator;
    private $vehicleService;
    /** @var MotTestService */
    private $motTestServiceClient;
    private $vehicleTableBuilder;
    private $catalogService;
    private $authorisationService;
    private $vehicleExpiryMapper;

    public function __construct(
        ParamObfuscator $paramObfuscator,
        VehicleService $vehicleService,
        CatalogService $catalogService,
        MotAuthorisationServiceInterface $authorisationService,
        VehicleViewModelBuilder $vehicleTableBuilder,
        VehicleExpiryMapper $vehicleExpiryMapper
    ) {
        $this->paramObfuscator = $paramObfuscator;
        $this->vehicleService = $vehicleService;
        $this->catalogService = $catalogService;
        $this->authorisationService = $authorisationService;
        $this->vehicleTableBuilder = $vehicleTableBuilder;
        $this->vehicleExpiryMapper = $vehicleExpiryMapper;
    }

    /**
     * Show vehicle details.
     *
     * @throws \Exception
     * @throws \DvsaCommon\Obfuscate\InvalidArgumentException
     *
     * @return array|\Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $this->motTestServiceClient = $this->getServiceLocator()->get(MotTestService::class);
        $this->authorisationService->assertGranted(PermissionInSystem::FULL_VEHICLE_MOT_TEST_HISTORY_VIEW);

        $obfuscatedVehicleId = (string) $this->params('id');
        $vehicleId = (int) $this->paramObfuscator->deobfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $obfuscatedVehicleId);

        if ($vehicleId == 0) {
            return $this->notFoundAction();
        }

        $vehicle = null;
        try {
            /** @var VehicleService $vehicleService */
            $vehicle = $this->vehicleService->getDvsaVehicleById($vehicleId);
            $mostRecentWeight = $this->motTestServiceClient->getVehicleTestWeight($vehicleId);
            if ($mostRecentWeight !== 0) {
                $vehicle->setWeight($mostRecentWeight);
            }
            $expiryDateForVehicle = $this->vehicleExpiryMapper->getExpiryForVehicle($vehicleId);
        } catch (ValidationException $e) {
            return $this->notFoundAction();
        } catch (ClientException $e) {
            return $this->notFoundAction();
        }

        $this->layout('layout/layout-govuk.phtml');

        $vehicleTableBuilder = $this->vehicleTableBuilder
            ->setVehicle($vehicle)
            ->setSearchData($this->getRequest()->getQuery())
            ->setObfuscatedVehicleId($obfuscatedVehicleId)
            ->setExpiryDateInformation($expiryDateForVehicle);

        return new ViewModel([
                'viewModel' => $vehicleTableBuilder->getViewModel(),
            ]
        );
    }

    /**
     * This action is responsible for showing the search screen for a vehicle and handle the post of the form.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function searchAction()
    {
        $this->layout('layout/layout_enforcement');

        if (!$this->getAuthorizationService()->isGranted(PermissionInSystem::VEHICLE_READ)) {
            return $this->redirect()->toUrl(PersonUrlBuilderWeb::home());
        }

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $searchParams = $request->isPost() ? $request->getPost() : $request->getQuery();

        return new ViewModel(
            [
                'searchParams' => $searchParams,
            ]
        );
    }

    /**
     * This action is responsible for handling the search and showing the results/page details of the vehicle in
     * function of the number of results.
     * If there no results found the action redirect to search page with an error message.
     *
     * @return ViewModel
     */
    public function resultAction()
    {
        $this->layout('layout/layout_enforcement');

        if (!$this->getAuthorizationService()->isGranted(PermissionInSystem::VEHICLE_READ)) {
            return $this->redirect()->toUrl(PersonUrlBuilderWeb::home());
        }

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $form = $request->isPost() ? $request->getPost() : $request->getQuery();
        if (!isset($form)) {
            return $this->redirect()->toUrl(VehicleUrlBuilderWeb::search());
        }

        $vehicleSearchService = new VehicleSearchService($this, $this->getRestClient(), $form, $this->paramObfuscator);

        try {
            $results = $vehicleSearchService->getVehicleResults();
        } catch (RestApplicationException $e) {
            $this->addErrorMessages(self::NO_RESULT_FOUND);

            return $this->redirect()->toUrl(VehicleUrlBuilderWeb::search());
        }

        if ($results->getCount() === 1) {
            /** @var SearchVehicle $vehicle */
            $vehicle = $results->getItem(0);

            return $this->redirect()->toRoute(
                'vehicle/detail',
                [
                    'id' => $this->paramObfuscator->obfuscate($vehicle->getId()),
                ],
                [
                    'query' => [
                        'backTo' => self::BACK_TO_SEARCH,
                        'type' => $form[VehicleSearchService::VEHICLE_TYPE_TERM],
                        'search' => $form[VehicleSearchService::VEHICLE_SEARCH_TERM],
                    ],
                ]
            );
        }

        return $vehicleSearchService->checkVehicleResults();
    }

    /**
     * Callback function to add an error from the decorator.
     *
     * @param $errors
     */
    public function addErrorMessagesFromService($errors)
    {
        $this->addErrorMessages($errors);
    }
}
