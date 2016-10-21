<?php

namespace UserAdmin\Controller;

use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\IncorrectDateFormatException;
use DvsaCommon\Date\Exception\NonexistentDateException;
use DvsaCommon\Dto\Person\SearchPersonResultDto;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use UserAdmin\Service\DateOfBirthFilterService;
use UserAdmin\Traits\UserAdminServicesTrait;
use UserAdmin\ViewModel\UserSearchViewModel;
use Zend\Http\PhpEnvironment\Request;
use UserAdmin\View\Helper\UserSearchHelper;

/**
 * Controller for user search
 */
class UserSearchController extends AbstractDvsaMotTestController
{
    use UserAdminServicesTrait;

    const PAGE_TITLE_INDEX = 'User search';
    const PAGE_TITLE_RESULTS = 'Search Results';
    const PAGE_SUBTITLE_INDEX = 'User management';
    const PAGE_SUBTITLE_RESULTS = 'User search';
    const ROUTE_USER_HOME = 'user-home';
    const ROUTE_USER_SEARCH = 'user_admin/user-search';
    const ROUTE_USER_SEARCH_RESULTS = 'user_admin/user-search-results';
    const PARAM_USERNAME = 'username';
    const PARAM_FIRSTNAME = 'firstName';
    const PARAM_LASTNAME = 'lastName';
    const PARAM_EMAIL = 'email';
    const PARAM_TOWN = 'town';
    const PARAM_POSTCODE = 'postcode';
    const PARAM_DOB = 'dateOfBirth';
    const PARAM_DOB_DAY = 'dobDay';
    const PARAM_DOB_MONTH = 'dobMonth';
    const PARAM_DOB_YEAR = 'dobYear';
    const MESSAGE = 'Using more than one search criteria will improve your chances of finding a particular user. A good search uses last name, date of birth and postcode.';
    const EXCEPTION_VALIDATION_DOB_INVALID_DATE = 'The date of birth is an invalid date.';
    const EXCEPTION_VALIDATION_DOB_DATE_IN_FUTURE= 'The date of birth specified is in the future.';
    const EXCEPTION_VALIDATION_DOB_INCORRECT_FORMAT = 'The date of birth is not in the correct format.';
    const EXCEPTION_VALIDATION_NO_CRITERIA = 'You must enter information in at least one of the fields below to search for a user.';
    const ERROR_CODE_TOO_MANY_RESULTS = 22;
    const ERROR_CODE_TOO_FEW_RESULTS = 23;

    /** @var DateOfBirthFilterService $dateOfBirthFilterService */
    private $dateOfBirthFilterService;

    public function __construct(DateOfBirthFilterService $dateOfBirthFilterService)
    {
        $this->dateOfBirthFilterService = $dateOfBirthFilterService;
    }

    public function indexAction()
    {
        //  --  check permissions
        $this->getAuthorizationService()->assertGranted(PermissionInSystem::USER_SEARCH);

        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE_INDEX);
        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE_INDEX);

        $viewModel = new UserSearchViewModel(
            [],
            $this->getFullSearchCriteria()
        );

        $userSearchExtended = $this->getAuthorizationService()->isGranted(PermissionInSystem::USER_SEARCH_EXTENDED);

        $this->layout('layout/layout-govuk.phtml');

        $systemMessage = $this->flashMessenger()->getErrorMessages();
        $infoMessage = current($this->flashMessenger()->getInfoMessages());

        return [
            'viewModel' => $viewModel,
            'userHomeRoute' => self::ROUTE_USER_HOME,
            'searchResultsRoute' => self::ROUTE_USER_SEARCH_RESULTS,
            'userSearchExtended' => $userSearchExtended,
            'systemMessage' => $systemMessage,
            'infoMessage' => $infoMessage,
            'message' => self::MESSAGE
        ];
    }

    public function resultsAction()
    {
        $this->getAuthorizationService()->assertGranted(PermissionInSystem::USER_SEARCH);

        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE_RESULTS);
        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE_RESULTS);

        if (false === $this->isSearchDataValid()) {
            return $this->redirectToSearchWithQuery();
        }

        try {
            $users = $this->getUsers();
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        }

        $this->dateOfBirthFilterService->filterPersonalDetails($users);

        $viewModel = new UserSearchViewModel(
            $users,
            $this->getFullSearchCriteria()
        );

        $userSearchExtended = $this->getAuthorizationService()->isGranted(PermissionInSystem::USER_SEARCH_EXTENDED);

        $userSearchRoute = $this->url()
            ->fromRoute(
                self::ROUTE_USER_SEARCH,
                [],
                [
                    'query' => $this->getFullSearchCriteria()
                ]
            );

        $this->layout('layout/layout-govuk.phtml');

        return [
            'viewModel' => $viewModel,
            'userHomeRoute' => self::ROUTE_USER_HOME,
            'escUserSearchRoute' => $userSearchRoute,
            'helper' => new UserSearchHelper($this->getAuthorizationService()),
            'resultsQueryArray' => $this->getRequest()->getQuery()->toArray(),
            'userSearchExtended' => $userSearchExtended
        ];
    }

    private function redirectToSearchWithQuery()
    {
        return $this->redirect()->toRoute(
            self::ROUTE_USER_SEARCH,
            [],
            [
                'query' => $this->getFullSearchCriteria(),
            ]
        );
    }

    /**
     * @return array|\DvsaCommon\Dto\Person\SearchPersonResultDto[]
     * @throws Exception
     */
    private function getUsers()
    {
        $criteria = $this->getFilteredSearchCriteria();

        if (count($criteria) === 0) {
            return [];
        }

        if (isset($criteria[self::PARAM_TOWN])) {
            $this->getAuthorizationService()->assertGranted(PermissionInSystem::USER_SEARCH_EXTENDED);
        }

        return $this->getMapperFactory()->UserAdmin->searchUsers($criteria);
    }

    /**
     * @return array
     */
    private function getSearchCriteria()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        return [
            self::PARAM_USERNAME => $request->getQuery(self::PARAM_USERNAME),
            self::PARAM_FIRSTNAME => $request->getQuery(self::PARAM_FIRSTNAME),
            self::PARAM_LASTNAME => $request->getQuery(self::PARAM_LASTNAME),
            self::PARAM_EMAIL => $request->getQuery(self::PARAM_EMAIL),
            self::PARAM_DOB => $this->getDobSearchCriteria(),
            self::PARAM_TOWN => $request->getQuery(self::PARAM_TOWN),
            self::PARAM_POSTCODE => $request->getQuery(self::PARAM_POSTCODE)
        ];
    }

    /**
     * @return array
     */
    private function getFilteredSearchCriteria()
    {
        return array_filter($this->getSearchCriteria(), 'strlen');
    }

    /**
     * @return array
     */
    private function getFullSearchCriteria()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        return array_merge(
            $this->getFilteredSearchCriteria(),
            [
                self::PARAM_DOB_YEAR => $request->getQuery(self::PARAM_DOB_YEAR),
                self::PARAM_DOB_MONTH => $request->getQuery(self::PARAM_DOB_MONTH),
                self::PARAM_DOB_DAY => $request->getQuery(self::PARAM_DOB_DAY),
            ]
        );
    }

    /**
     * @return string
     */
    private function getDobSearchCriteria()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        return implode(
            '-',
            array_filter(
                [
                    $request->getQuery(self::PARAM_DOB_YEAR),
                    $request->getQuery(self::PARAM_DOB_MONTH),
                    $request->getQuery(self::PARAM_DOB_DAY),
                ],
                'strlen'
            )
        );
    }

    private function isSearchDataValid()
    {
        $valid = true;

        if (count($this->getFilteredSearchCriteria()) === 0) {
            $this->addErrorMessages(self::EXCEPTION_VALIDATION_NO_CRITERIA);
            $valid = false;
        } elseif ('' !== $this->getDobSearchCriteria()) {
            try {
                $date = DateUtils::toDate($this->getDobSearchCriteria());
                if (DateUtils::isDateInFuture($date)) {
                    $this->addErrorMessages(self::EXCEPTION_VALIDATION_DOB_DATE_IN_FUTURE);
                    $valid = false;
                }
            } catch (NonexistentDateException $e) {
                $this->addErrorMessages(self::EXCEPTION_VALIDATION_DOB_INVALID_DATE);
                $valid = false;
            } catch (IncorrectDateFormatException $e) {
                $this->addErrorMessages(self::EXCEPTION_VALIDATION_DOB_INCORRECT_FORMAT);
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * @param ValidationException $e
     * @return \Zend\Http\Response
     */
    private function handleValidationException(ValidationException $e)
    {
        $errorCode = (int)$e->getErrors()[0]['code'];

        $viewModel = new UserSearchViewModel(
            [],
            $this->getFullSearchCriteria()
        );

        if ($errorCode === self::ERROR_CODE_TOO_MANY_RESULTS) {
            $message = 'Your search for ' . $viewModel->displaySearchCriteria() . '
            returned too many results. Add more details and try again.';
        } elseif ($errorCode === self::ERROR_CODE_TOO_FEW_RESULTS) {
            $message = 'Your search for ' . $viewModel->displaySearchCriteria() . '
            returned no results. Check what you have entered or add more details and try again.';
        }

        $this->addInfoMessages($message);

        return $this->redirectToSearchWithQuery();
    }
}
