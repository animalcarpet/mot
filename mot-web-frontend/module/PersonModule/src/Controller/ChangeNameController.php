<?php

namespace Dvsa\Mot\Frontend\PersonModule\Controller;

use Application\Data\ApiPersonalDetails;
use Core\Controller\AbstractAuthActionController;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\MapperFactory;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;
use DvsaCommon\Validator\PersonNameValidator;
use Exception;
use UserAdmin\Service\HelpdeskAccountAdminService;
use Zend\View\Model\ViewModel;

class ChangeNameController extends AbstractAuthActionController
{
    const PAGE_SUBTITLE = 'User profile';
    const PAGE_TITLE = 'Change name';

    const MSG_NAME_CHANGED_SUCCESSFULLY = 'Name has been changed successfully.';
    const MSG_NAME_CHANGED_FAILURE = 'Name could not be changed. Please try again.';

    /**
     * @var PersonProfileGuardBuilder
     */
    private $personProfileGuardBuilder;

    /**
     * @var HelpdeskAccountAdminService
     */
    private $accountAdminService;

    /**
     * @var null|array
     */
    private $validationErrors;

    /**
     * @var PersonProfileUrlGenerator
     */
    private $personProfileUrl;

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    /**
     * @var ApiPersonalDetails
     */
    private $personalDetailsService;

    /**
     * @var MapperFactory
     */
    private $mapperFactory;

    /**
     * ChangeNameController constructor.
     *
     * @param PersonProfileGuardBuilder   $personProfileGuardBuilder
     * @param HelpdeskAccountAdminService $accountAdminService
     * @param PersonProfileUrlGenerator   $personProfileUrlGenerator
     * @param ContextProvider             $contextProvider
     * @param ApiPersonalDetails          $personalDetailsService
     * @param MapperFactory               $mapperFactory
     */
    public function __construct(
        PersonProfileGuardBuilder $personProfileGuardBuilder,
        HelpdeskAccountAdminService $accountAdminService,
        PersonProfileUrlGenerator $personProfileUrlGenerator,
        ContextProvider $contextProvider,
        ApiPersonalDetails $personalDetailsService,
        MapperFactory $mapperFactory
    ) {
        $this->personProfileGuardBuilder = $personProfileGuardBuilder;
        $this->accountAdminService = $accountAdminService;
        $this->personProfileUrl = $personProfileUrlGenerator;
        $this->contextProvider = $contextProvider;
        $this->personalDetailsService = $personalDetailsService;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * @throws UnauthorisedException
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        //TODO: Bread crumbs
        $personId = $this->getPersonIdFromRequest();
        $profile = $this->accountAdminService->getUserProfile($personId);
        $context = $this->contextProvider->getContext();
        $personalDetails = new PersonalDetails($this
            ->personalDetailsService
            ->getPersonalDetailsData($personId));

        $personProfileGuard = $this->personProfileGuardBuilder->createPersonProfileGuard(
            $personalDetails,
            $context
        );

        $breadcrumbs = $this->generateBreadcrumbsFromRequest($personId, $personalDetails);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        if (!$personProfileGuard->canEditName()) {
            throw new UnauthorisedException('');
        }

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE);
        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE);

        $firstName = $profile->getFirstName();
        $middleName = $profile->getMiddleName();
        $lastName = $profile->getLastName();

        if ($this->getRequest()->isPost()) {
            $params = [
                'firstName' => trim($this->getRequest()->getPost('firstName')),
                'middleName' => trim($this->getRequest()->getPost('middleName')),
                'lastName' => trim($this->getRequest()->getPost('lastName')),
            ];

            if ($this->validate($params)) {
                try {
                    $this->accountAdminService->updateName(
                        $personId,
                        $params['firstName'],
                        $params['middleName'],
                        $params['lastName']
                    );
                    $this->flashMessenger()->addSuccessMessage(self::MSG_NAME_CHANGED_SUCCESSFULLY);
                } catch (Exception $e) {
                    $this->flashMessenger()->addErrorMessage(self::MSG_NAME_CHANGED_FAILURE);
                }

                return $this->redirect()->toUrl($this->personProfileUrl->toPersonProfile());
            } else {
                $firstName = $this->getRequest()->getPost('firstName');
                $middleName = $this->getRequest()->getPost(('middleName'));
                $lastName = $this->getRequest()->getPost('lastName');
            }
        }

        return $this->createViewModel('profile/name/index.phtml', [
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName,
            'errors' => $this->validationErrors,
        ]);
    }

    /**
     * @return int
     */
    private function getPersonIdFromRequest()
    {
        $personId = (int) $this->params()->fromRoute('id', null);
        $identity = $this->getIdentity();

        if ($personId == 0) {
            $personId = $identity->getUserId();
        }

        return $personId;
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    private function validate(array $params)
    {
        $validator = new PersonNameValidator();
        if (!$validator->isValid($params)) {
            $this->validationErrors = $validator->getMessages();
            foreach ($this->validationErrors as $field => $errorMessage) {
                $message = $validator->getFieldLabel($field).' - '.$errorMessage;
                $this->flashMessenger()->addErrorMessage([$message]);
            }

            return false;
        }

        return true;
    }

    /**
     * @param string $template
     * @param array  $variables
     *
     * @return ViewModel
     */
    private function createViewModel($template, array $variables)
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate($template);
        $viewModel->setVariables($variables);

        return $viewModel;
    }

    /**
     * Get the breadcrumbs given the context of the url.
     *
     * @param int             $personId
     * @param PersonalDetails $personalDetails
     * @param bool            $isProfile
     *
     * @return array
     */
    private function generateBreadcrumbsFromRequest($personId, $personalDetails, $isProfile = false)
    {
        $breadcrumbs = [];
        $personName = $personalDetails->getFullName();
        $context = $this->contextProvider->getContext();

        if (ContextProvider::YOUR_PROFILE_CONTEXT === $context) {
            /*
             * Your Profile context.
             */
            $profileUrl = $isProfile === false ? $this->url()->fromRoute('newProfile', ['id' => $personId]) : '';
            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__YOUR_PROFILE => $profileUrl];
        } elseif (ContextProvider::USER_SEARCH_CONTEXT === $context) {
            /*
                 * User search context.
                 */
                $userSearchUrl = $this->url()->fromRoute('user_admin/user-search');

            $profileUrl = $isProfile === false
                    ? $this->url()->fromRoute('newProfileUserAdmin', ['id' => $personId]) : '';

            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__USER_SEARCH => $userSearchUrl];
            $breadcrumbs += [$personName => $profileUrl];
        } elseif (ContextProvider::AE_CONTEXT === $context) {
            /*
             * AE context.
             */
            $aeId = $this->params()->fromRoute('authorisedExaminerId');
            $ae = $this->mapperFactory->Organisation->getAuthorisedExaminer($aeId);
            $aeUrl = $this->url()->fromRoute('authorised-examiner', ['id' => $aeId]);
            $breadcrumbs += [$ae->getName() => $aeUrl];

            $profileUrl = $isProfile === false ? $this->url()->fromRoute(ContextProvider::AE_PARENT_ROUTE, [
                'authorisedExaminerId' => $aeId, 'id' => $personId, ]) : '';
            $breadcrumbs += [$personName => $profileUrl];
        } elseif (ContextProvider::VTS_CONTEXT === $context) {
            /*
             * VTS context.
             */
            $vtsId = $this->params()->fromRoute('vehicleTestingStationId');
            $vts = $this->mapperFactory->Site->getById($vtsId);
            $ae = $vts->getOrganisation();

            if ($ae) {
                $aeUrl = $this->url()->fromRoute('authorised-examiner', ['id' => $ae->getId()]);
                $breadcrumbs += [$ae->getName() => $aeUrl];
            }

            $vtsUrl = $this->url()->fromRoute('vehicle-testing-station', ['id' => $vtsId]);
            $breadcrumbs += [$vts->getName() => $vtsUrl];
            $profileUrl = $isProfile === false ? $this->url()->fromRoute(ContextProvider::VTS_PARENT_ROUTE, [
                'vehicleTestingStationId' => $vtsId, 'id' => $personId, ]) : '';
            $breadcrumbs += [$personName => $profileUrl];
        } else {
            $userSearchUrl = $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->userSearch());
            $profileUrl = $isProfile === false
                ? $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->UserProfile($personId)) : '';
            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__USER_SEARCH => $userSearchUrl, $personName => $profileUrl];
        }

        $breadcrumbs += ['Change name' => ''];

        return $breadcrumbs;
    }

    /**
     * Build a url with the query params.
     *
     * @param string $url
     *
     * @return string
     */
    private function buildUrlWithCurrentSearchQuery($url)
    {
        $params = $this->getRequest()->getQuery()->toArray();
        if (empty($params)) {
            return $url;
        }

        return $url.'?'.http_build_query($params);
    }
}
