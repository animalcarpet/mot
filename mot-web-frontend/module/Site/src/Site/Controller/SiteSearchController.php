<?php

namespace Site\Controller;

use DvsaClient\MapperFactory;
use DvsaCommon\Dto\Site\SiteListDto;
use DvsaCommon\UrlBuilder\SiteUrlBuilderWeb;
use DvsaCommon\Utility\DtoHydrator;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Site\Service\SiteSearchService;
use Site\ViewModel\SiteSearchViewModel;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;
use Report\Table\Table;

/**
 * Class SiteSearchController.
 */
class SiteSearchController extends AbstractDvsaMotTestController
{
    const NO_RESULT = 0;
    const ONE_RESULT = 1;

    const NO_RESULT_FOUND = 'Unable to find any matches. Try expanding your search criteria ';

    const PAGE_TITLE_SEARCH = 'Search for site information by...';
    const PAGE_TITLE_RESULT = 'Results with "%s"';

    const SITE_SEARCH_TEMPLATE = 'site/site-search/search';

    /**
     * @var SiteSearchViewModel
     */
    private $viewModel;

    /**
     * @var MapperFactory
     */
    protected $mapper;

    /**
     * @var SiteSearchService
     */
    protected $service;

    protected $breadcrumbSearch;
    protected $breadcrumbResult;

    /**
     * @param MapperFactory     $mapper
     * @param SiteSearchService $service
     */
    public function __construct(MapperFactory $mapper, SiteSearchService $service)
    {
        $this->mapper = $mapper;
        $this->service = $service;
        $this->viewModel = new SiteSearchViewModel();

        $this->breadcrumbSearch = [
            'Search site information' => '',
        ];
        $this->breadcrumbResult = [
            'Search site information' => SiteUrlBuilderWeb::search(),
            'Site search results' => '',
        ];
    }

    /**
     * This action is the end point to search for a site.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function searchAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $this->viewModel->populateFromQuery($request->getQuery()->toArray());

        return $this->initViewModelInformation(self::PAGE_TITLE_SEARCH, $this->breadcrumbSearch);
    }

    /**
     * This action is the end point to the result search of sites
     * If there is no result we display back the search page
     * If there is only one result we redirect to the detail page.
     *
     * @return Response|ViewModel
     */
    public function resultAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $this->viewModel->populateFromQuery($request->getQuery()->toArray());

        if ($this->viewModel->isFormEmpty($this->flashMessenger()) === false && $this->viewModel->isValid()) {
            return $this->getResultFromApi();
        }

        return $this->initViewModelInformation(self::PAGE_TITLE_SEARCH, $this->breadcrumbSearch)
            ->setTemplate(self::SITE_SEARCH_TEMPLATE);
    }

    /**
     * Get the result of the search from the API.
     *
     * @return ViewModel
     */
    private function getResultFromApi()
    {
        try {
            $searchParams = $this->viewModel->prepareSearchParams();

            /** @var SiteListDto $result */
            $result = $this->mapper->Site->search(DtoHydrator::dtoToJson($searchParams));
            $result->setSearched($searchParams);

            /** @var Table $table */
            $table = $this->service->initTable($result);
        } catch (\Exception $e) {
            $this->addErrorMessage(self::NO_RESULT_FOUND);

            return $this->initViewModelInformation(self::PAGE_TITLE_SEARCH, $this->breadcrumbSearch)
                ->setTemplate(self::SITE_SEARCH_TEMPLATE);
        }

        /* Show the search page if no result */
        if (self::NO_RESULT === (int) $result->getTotalResultCount()) {
            $this->addErrorMessage(self::NO_RESULT_FOUND);

            return $this->initViewModelInformation(self::PAGE_TITLE_SEARCH, $this->breadcrumbSearch)
                ->setTemplate(self::SITE_SEARCH_TEMPLATE);
        }
        /* Redirect to the detail page if only one result */
        if (self::ONE_RESULT === (int) $result->getTotalResultCount()) {
            return $this->redirect()->toUrl(SiteUrlBuilderWeb::of($result->getData()[0]['id']));
        }

        $this->viewModel->setTable($table);

        return $this->initViewModelInformation(
            sprintf(self::PAGE_TITLE_RESULT, $this->viewModel->displaySearchCriteria()),
            $this->breadcrumbResult
        );
    }

    /**
     * This function initialise the view model.
     *
     * @param string $title
     * @param array  $breadcrumbs
     *
     * @return ViewModel
     */
    private function initViewModelInformation($title, $breadcrumbs)
    {
        $this->layout('layout/layout-govuk.phtml');

        $this->layout()->setVariable('pageTitle', $title);
        $this->layout()->setVariable('pageSubTitle', 'Site search');
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        return new ViewModel(
            [
                'viewModel' => $this->viewModel,
            ]
        );
    }
}
