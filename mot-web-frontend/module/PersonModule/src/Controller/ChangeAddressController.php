<?php

namespace Dvsa\Mot\Frontend\PersonModule\Controller;

use Application\Data\ApiPersonalDetails;
use Core\Service\SessionService;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\MapperFactory;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;
use DvsaCommon\Validator\AddressValidator;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use UserAdmin\Service\HelpdeskAccountAdminService;
use Zend\View\Model\ViewModel;

class ChangeAddressController extends AbstractDvsaMotTestController
{
    const PAGE_TITLE = 'Change address';

    const MSG_ADDRESS_CHANGED_SUCCESSFULLY = 'Address has been changed successfully.';
    const MSG_ADDRESS_CHANGED_FAILURE = 'Address could not be changed. Please try again.';

    const SESSION_STORAGE_KEY_PREFIX = 'CHANGE_PERSON_ADDRESS';

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
     * @var SessionService
     */
    private $sessionService;

    /**
     * ChangeAddressController constructor.
     *
     * @param PersonProfileGuardBuilder   $personProfileGuardBuilder
     * @param HelpdeskAccountAdminService $accountAdminService
     * @param PersonProfileUrlGenerator   $personProfileUrlGenerator
     * @param ContextProvider             $contextProvider
     * @param ApiPersonalDetails          $personalDetailsService
     * @param MapperFactory               $mapperFactory
     * @param SessionService              $sessionService
     */
    public function __construct(
        PersonProfileGuardBuilder $personProfileGuardBuilder,
        HelpdeskAccountAdminService $accountAdminService,
        PersonProfileUrlGenerator $personProfileUrlGenerator,
        ContextProvider $contextProvider,
        ApiPersonalDetails $personalDetailsService,
        MapperFactory $mapperFactory,
        SessionService $sessionService
    ) {
        $this->personProfileGuardBuilder = $personProfileGuardBuilder;
        $this->accountAdminService = $accountAdminService;
        $this->personProfileUrl = $personProfileUrlGenerator;
        $this->contextProvider = $contextProvider;
        $this->personalDetailsService = $personalDetailsService;
        $this->mapperFactory = $mapperFactory;
        $this->sessionService = $sessionService;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     *
     * @throws UnauthorisedException
     */
    public function indexAction()
    {
        $personId = $this->getPersonId();
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

        if (!$personProfileGuard->canChangeEmailAddress()) {
            throw new UnauthorisedException('');
        }

        $this->layout('layout/layout-govuk.phtml');
        $this->setHeadTitle('Change address');

        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE);
        $this->layout()->setVariable('pageSubTitle',
            $personProfileGuard->isViewingOwnProfile()
                ? 'Your profile'
                : 'User profile');

        $data = $this->sessionService->load(self::SESSION_STORAGE_KEY_PREFIX.$personId);
        if (!empty($data)) {
            $firstLine = $data['firstLine'];
            $secondLine = $data['secondLine'];
            $thirdLine = $data['thirdLine'];
            $townOrCity = $data['townOrCity'];
            $country = $data['country'];
            $postcode = $data['postcode'];
        } else {
            $address = $profile->getAddress();
            $firstLine = $address->getAddressLine1();
            $secondLine = $address->getAddressLine2();
            $thirdLine = $address->getAddressLine3();
            $townOrCity = $address->getTown();
            $country = $address->getCountry();
            $postcode = $address->getPostcode();
        }

        if ($this->getRequest()->isPost()) {
            $params = [
                'firstLine' => trim($this->getRequest()->getPost('firstLine')),
                'secondLine' => trim($this->getRequest()->getPost('secondLine')),
                'thirdLine' => trim($this->getRequest()->getPost('thirdLine')),
                'townOrCity' => trim($this->getRequest()->getPost('townOrCity')),
                'country' => trim($this->getRequest()->getPost('country')),
                'postcode' => strtoupper(preg_replace('/ \s+/', ' ', trim($this->getRequest()->getPost('postcode')))),
            ];

            if ($this->validate($params) && $this->saveToSession($personId, $params)) {
                $summaryRoute = '';
                switch ($this->contextProvider->getContext()) {
                    case ContextProvider::AE_CONTEXT:
                        $summaryRoute = ContextProvider::AE_PARENT_ROUTE.'/address/review-address';
                        break;
                    case ContextProvider::VTS_CONTEXT:
                        $summaryRoute = ContextProvider::VTS_PARENT_ROUTE.'/address/review-address';
                        break;
                    case ContextProvider::USER_SEARCH_CONTEXT:
                        $summaryRoute = ContextProvider::USER_SEARCH_PARENT_ROUTE.'/address/review-address';
                        break;
                    case ContextProvider::YOUR_PROFILE_CONTEXT:
                        $summaryRoute = ContextProvider::YOUR_PROFILE_PARENT_ROUTE.'/address/review-address';
                }

                return $this->redirect()->toRoute($summaryRoute, ['id' => $personId]);
            } else {
                $firstLine = $this->getRequest()->getPost('firstLine');
                $secondLine = $this->getRequest()->getPost('secondLine');
                $thirdLine = $this->getRequest()->getPost('thirdLine');
                $townOrCity = $this->getRequest()->getPost('townOrCity');
                $country = $this->getRequest()->getPost('country');
                $postcode = $this->getRequest()->getPost('postcode');
            }
        }

        return $this->createViewModel('profile/address/index.phtml', [
            'firstLine' => $firstLine,
            'secondLine' => $secondLine,
            'thirdLine' => $thirdLine,
            'townOrCity' => $townOrCity,
            'country' => $country,
            'postcode' => $postcode,
            'errors' => $this->validationErrors,
            'viewingOwnProfile' => $personProfileGuard->isViewingOwnProfile(),
        ]);
    }

    public function reviewAction()
    {
        $personId = $this->getPersonId();
        $request = $this->getRequest();
        $personalDetails = new PersonalDetails($this
            ->personalDetailsService
            ->getPersonalDetailsData($personId));
        $context = $this->contextProvider->getContext();
        $personProfileGuard = $this->personProfileGuardBuilder->createPersonProfileGuard(
            $personalDetails,
            $context
        );

        if (!$personProfileGuard->canChangeEmailAddress()) {
            throw new UnauthorisedException('');
        }

        $breadcrumbs = $this->generateBreadcrumbsFromRequest($personId, $personalDetails);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        if (!$data = $this->sessionService->load(self::SESSION_STORAGE_KEY_PREFIX.$personId)) {
            return $this->redirect()->toUrl($this->personProfileUrl->toPersonProfile());
        }

        if ($request->isPost()) {
            if ($this->validate($data)) {
                try {
                    $this->accountAdminService->updateAddress(
                        $personId,
                        $data['firstLine'],
                        $data['secondLine'],
                        $data['thirdLine'],
                        $data['townOrCity'],
                        $data['country'],
                        $data['postcode']
                    );
                    $this->flashMessenger()->addSuccessMessage(self::MSG_ADDRESS_CHANGED_SUCCESSFULLY);
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage(self::MSG_ADDRESS_CHANGED_FAILURE);
                }
            }

            return $this->redirect()->toUrl($this->personProfileUrl->toPersonProfile());
        }
        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageTitle', 'Review address change');
        $this->setHeadTitle('Review address change');
        $this->layout()->setVariable('pageSubTitle',
            $personProfileGuard->isViewingOwnProfile()
                ? 'Your profile'
                : 'User profile');

        switch ($this->contextProvider->getContext()) {
            case ContextProvider::AE_CONTEXT:
                $backButtonUrl = $this->url()->fromRoute(ContextProvider::AE_PARENT_ROUTE.'/address/change-address', ['id' => $personId]);
                break;
            case ContextProvider::VTS_CONTEXT:
                $backButtonUrl = $this->url()->fromRoute(ContextProvider::VTS_PARENT_ROUTE.'/address/change-address', ['id' => $personId]);
                break;
            case ContextProvider::USER_SEARCH_CONTEXT:
                $backButtonUrl = $this->url()->fromRoute(ContextProvider::USER_SEARCH_PARENT_ROUTE.'/address/change-address', ['id' => $personId]);
                break;
            case ContextProvider::YOUR_PROFILE_CONTEXT:
                $backButtonUrl = $this->url()->fromRoute(ContextProvider::YOUR_PROFILE_PARENT_ROUTE.'/address/change-address', ['id' => $personId]);
                break;
            default:
                $backButtonUrl = '';
        }

        return $this->createViewModel('profile/address/review.phtml', [
            'backButtonUrl' => $backButtonUrl,
            'personName' => $personalDetails->getFullName(),
            'firstLine' => $data['firstLine'],
            'secondLine' => $data['secondLine'],
            'thirdLine' => $data['thirdLine'],
            'townOrCity' => $data['townOrCity'],
            'country' => $data['country'],
            'postcode' => $data['postcode'],
        ]);
    }

    /**
     * @return int
     */
    private function getPersonId()
    {
        $context = $this->contextProvider->getContext();

        return $context === ContextProvider::YOUR_PROFILE_CONTEXT ?
            $this->getIdentity()->getUserId() : (int) $this->params()->fromRoute('id', null);
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    private function validate(array $params)
    {
        $validator = new AddressValidator();
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
                ? $this->url()->fromRoute(ContextProvider::USER_SEARCH_PARENT_ROUTE, ['id' => $personId]) : '';

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

        $breadcrumbs += ['Change address' => ''];

        return $breadcrumbs;
    }

    /**
     * Save the driving licence number and region to the session.
     *
     * @param int   $personId
     * @param array $params
     *
     * @return bool
     */
    private function saveToSession($personId, array $params)
    {
        $this->sessionService->save(self::SESSION_STORAGE_KEY_PREFIX.$personId, $params);

        return true;
    }
}
