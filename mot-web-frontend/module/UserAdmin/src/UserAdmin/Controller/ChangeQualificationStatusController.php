<?php

namespace UserAdmin\Controller;

use Application\Data\ApiPersonalDetails;
use Dashboard\Model\PersonalDetails;
use DvsaClient\Mapper\PersonMapper;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;

class ChangeQualificationStatusController extends AbstractDvsaMotTestController
{
    const SESSION_CONTAINER_NAME = 'ChangeQualificationStatusContainer';

    private $sessionContainer;

    private $authorisationService;

    private $personMapper;

    private $groupIdLookup = ['A' => 1, 'B' => 2];

    private $authStatusLookup = [
        'UNKN' => 'Unknown',
        'SPND' => 'Suspended',
        'QLFD' => 'Qualified',
        'DMTN' => 'Demo test needed',
        'ITRN' => 'Initial training needed',
    ];

    private $testerGroupAuthorisationMapper;

    private $personalDetails;

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    public function __construct(
        MotAuthorisationServiceInterface $authorisationServiceInterface,
        Container $container,
        PersonMapper $personMapper,
        HttpRestJsonClient $client,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        ContextProvider $contextProvider
    ) {
        $this->sessionContainer = $container;
        $this->authorisationService = $authorisationServiceInterface;
        $this->personMapper = $personMapper;
        $this->client = $client;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->personalDetails = new ApiPersonalDetails($client);
        $this->contextProvider = $contextProvider;
    }

    public function indexAction()
    {
        // N.B. order of this array determines output of options in index.phtml
        $authDescriptionLookup = [
            'ITRN' => 'Initial Training Needed',
            'DMTN' => 'Demo test needed',
            'QLFD' => 'Qualified',
            'SPND' => 'Suspended',
        ];

        $this->authorisationService->assertGranted(PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS);

        $personId = $this->getPersonId();

        $tester = new PersonalDetails($this->personalDetails->getPersonalDetailsData($personId));

        $testerId = $tester->getId();

        $vehicleClassGroup = $this->params()->fromRoute('vehicleClassGroup');

        $testerAuthorisation = $this->testerGroupAuthorisationMapper->getAuthorisation($testerId);

        $status = '';
        if ($vehicleClassGroup === 'A') {
            $status = $testerAuthorisation->getGroupAStatus()->getCode();
        } elseif ($vehicleClassGroup === 'B') {
            $status = $testerAuthorisation->getGroupBStatus()->getCode();
        }

        if (null === $status) {
            $status = 'ITRN';
        }

        if ($this->getRequest()->isPost()) {
            $this->sessionContainer->offsetSet('vehicleClassGroup', $vehicleClassGroup);
            $this->sessionContainer->offsetSet('status', $this->getRequest()->getPost('qualificationStatus'));

            return $this->redirect()->toRoute(
                'newProfileUserAdmin/change-qualification-status/confirmation',
                ['id' => $testerId, 'vehicleClassGroup' => $vehicleClassGroup]
            );
        }
        $this->sessionContainer->getManager()->getStorage()->clear(self::SESSION_CONTAINER_NAME);

        $params = $this->getRequest()->getQuery()->toArray();
        $this->layout()->setVariable('pageSubTitle', 'User profile');
        $this->layout()->setVariable('pageTitle', 'Change qualification status');

        $userProfileUrl = $this->url()->fromRoute('newProfileUserAdmin', ['id' => $testerId]);

        $breadcrumbs = [
            'User search' => $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->userSearch()),
            $tester->getFullName() => $userProfileUrl,
            'Change qualification status' => '',
        ];
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $this->layout('layout/layout-govuk.phtml');

        return new ViewModel(
            [
                'testerId' => $testerId,
                'searchQueryParams' => $params,
                'group' => $vehicleClassGroup,
                'status' => $status,
                'statusLookup' => $authDescriptionLookup,
            ]
        );
    }

    public function confirmationAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS);

        $personId = $this->params()->fromRoute('id');

        $tester = new PersonalDetails($this->personalDetails->getPersonalDetailsData($personId));
        $testerId = $tester->getId();

        if ($this->getRequest()->isPost()) {
            $vehicleClassGroup = $this->sessionContainer->offsetGet('vehicleClassGroup');
            $status = $this->sessionContainer->offsetGet('status');
            $url = PersonUrlBuilder::motTesting($tester->getId())->toString();
            try {
                $this->client->put($url, ['group' => $this->groupIdLookup[$vehicleClassGroup], 'result' => $status]);

                $this->addSuccessMessage(
                    'Group ' . $vehicleClassGroup .
                    ' tester qualification status has been changed to ' .
                    $this->authStatusLookup[$status]
                );

                //Add web message for success
                return $this->redirect()->toRoute('newProfileUserAdmin', ['id' => $testerId]);
            } catch (\Exception $e) {
                //Error on post validation... oops...
                $this->addErrorMessage($e->getMessage());

                return $this->redirect()->toRoute(
                    'newProfileUserAdmin/change-qualification-status/confirmation',
                    ['id' => $testerId, 'vehicleClassGroup' => $vehicleClassGroup]
                );
            }
        }
        $this->layout()->setVariable('pageSubTitle', 'User profile');
        $this->layout()->setVariable('pageTitle', 'Summary and confirmation');

        $userProfileUrl = $this->url()->fromRoute('newProfileUserAdmin', ['id' => $testerId]);

        $breadcrumbs = [
            'User search' => $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->userSearch()),
            $tester->getFullName() => $userProfileUrl,
            'Change qualification status' => '',
        ];
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $this->layout('layout/layout-govuk.phtml');

        return new ViewModel(
            [
                'group' => $this->sessionContainer->offsetGet('vehicleClassGroup'),
                'status' => $this->authStatusLookup[$this->sessionContainer->offsetGet('status')],
                'testerId' => $testerId,
                'testerName' => $tester->getFullName(),
            ]
        );
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

        return $url . '?' . http_build_query($params);
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
}
