<?php

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommonApi\Model\OutputFormat;
use DvsaCommonApi\Model\SearchParam;
use DvsaCommonApi\Service\AbstractSearchService;
use DvsaEntities\DqlBuilder\SearchParam\TesterSearchParam;
use DvsaMotApi\Model\OutputFormat\OutputFormatDataObjectTester;
use DvsaMotApi\Model\OutputFormat\OutputFormatDataTablesTester;
use DvsaMotApi\Model\OutputFormat\OutputFormatTypeAheadTester;

/**
 * Class TesterSearchService.
 */
class TesterSearchService extends AbstractSearchService
{
    const SEARCH_TESTER_ID_PARAMETER = 'testerId';
    const SEARCH_TESTER_USERNAME_PARAMETER = 'testerUserName';

    const SEARCH_REQUIRED_DISPLAY_MESSAGE = 'You need to enter a search value to perform the search';
    const SEARCH_INVALID_DATA_DISPLAY_MESSAGE = 'Search should contain alphanumeric and space characters only';

    protected $testerRepository;
    protected $objectHydrator;
    protected $authService;

    public function __construct(
        EntityManager $entityManager,
        DoctrineObject $objectHydrator,
        AuthorisationServiceInterface $authService
    ) {
        parent::__construct($entityManager);

        $this->testerRepository = $this->entityManager->getRepository(
            \DvsaEntities\Entity\Person::class
        );

        $this->objectHydrator = $objectHydrator;
        $this->authService = $authService;
    }

    /**
     * @SuppressWarnings(unused)
     *
     * @param null $values
     *
     * @return TesterSearchParam|void
     */
    public function getSearchParams($values = null)
    {
        return new TesterSearchParam();
    }

    /**
     * Provide output formats for Testers for rendering as data tables, type ahead and standard objects.
     *
     * @param $searchParams
     *
     * @return OutputFormatDataObjectTester|OutputFormatDataTablesTester|OutputFormatTypeAheadTester
     *
     * @throws \Exception
     */
    public function getOutputFormat($searchParams)
    {
        if ($searchParams->getFormat() == SearchParamConst::FORMAT_DATA_OBJECT) {
            return new OutputFormatDataObjectTester($this->objectHydrator);
        } elseif ($searchParams->getFormat() == SearchParamConst::FORMAT_DATA_TABLES) {
            return new OutputFormatDataTablesTester();
        } elseif ($searchParams->getFormat() == SearchParamConst::FORMAT_TYPE_AHEAD) {
            return new OutputFormatTypeAheadTester();
        }

        throw new \Exception('Unknown search format: '.$searchParams->getFormat());
    }

    /**
     * Perform the search.
     *
     * @param SearchParam  $params
     * @param OutputFormat $format
     *
     * @return mixed
     */
    public function repositorySearch(SearchParam $params, OutputFormat $format)
    {
        return $this->testerRepository->search($params, $format);
    }

    /**
     * Provides the ability to check the users access to the current search.
     */
    protected function checkPermissions()
    {
        //todo: Update this permission.. What do we need to list and display testers?
        //$this->authService->assertGranted(PermissionAtOrganisation::VEHICLE_TESTING_STATION_READ);
    }
}
