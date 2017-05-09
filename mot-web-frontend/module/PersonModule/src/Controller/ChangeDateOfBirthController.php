<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModule\Controller;

use Dashboard\Model\PersonalDetails;
use Application\Data\ApiPersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\MapperFactory;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Validator\DateOfBirthValidator;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use UserAdmin\Service\HelpdeskAccountAdminService;
use Zend\View\Model\ViewModel;

/**
 * Controller for changing day of birth for new person profile page.
 */
class ChangeDateOfBirthController extends AbstractDvsaMotTestController
{
    const PAGE_TITLE = 'Change date of birth';
    const PAGE_SUBTITLE = 'User profile';
    const FIELD_NAME = 'dateOfBirth';

    const SUCCESS_MSG = 'Date of birth has been changed successfully.';
    const FAILURE_MSG = 'Date of birth could not be changed. Please try again.';

    /**
     * @var PersonProfileGuardBuilder
     */
    private $personProfileGuardBuilder;
    /**
     * @var HelpdeskAccountAdminService
     */
    private $accountAdminService;
    /**
     * @var ApiPersonalDetails
     */
    private $personalDetailsService;
    /**
     * @var MapperFactory
     */
    private $mapperFactory;
    /**
     * @var DateOfBirthValidator
     */
    private $dayOfBirthValidator;
    /**
     * @var PersonProfileUrlGenerator
     */
    private $personProfileUrlGenerator;
    /**
     * @var ContextProvider
     */
    private $contextProvider;

    private $validationErrors;

    public function __construct(
        PersonProfileGuardBuilder $personProfileGuardBuilder,
        HelpdeskAccountAdminService $accountAdminService,
        PersonProfileUrlGenerator $personProfileUrlGenerator,
        ContextProvider $contextProvider,
        ApiPersonalDetails $personalDetailsService,
        MapperFactory $mapperFactory,
        DateOfBirthValidator $dayOfBirthValidator
    ) {
        $this->personProfileGuardBuilder = $personProfileGuardBuilder;
        $this->accountAdminService = $accountAdminService;
        $this->personalDetailsService = $personalDetailsService;
        $this->mapperFactory = $mapperFactory;
        $this->dayOfBirthValidator = $dayOfBirthValidator;
        $this->personProfileUrlGenerator = $personProfileUrlGenerator;
        $this->contextProvider = $contextProvider;
    }

    public function indexAction()
    {
        $personId = $this->getPersonId();
        /** @var PersonHelpDeskProfileDto $profile */
        $profile = $this->accountAdminService->getUserProfile($personId);
        $context = $this->contextProvider->getContext();
        $personalDetails = new PersonalDetails($this
            ->personalDetailsService
            ->getPersonalDetailsData($personId));

        $personProfileGuard = $this->personProfileGuardBuilder->createPersonProfileGuard(
            $personalDetails,
            $context
        );

        if (!$personProfileGuard->canChangeDateOfBirth()) {
            throw new UnauthorisedException('Permission denied for editing date of birth');
        }

        $breadcrumbs = $this->generateBreadcrumbsFromRequest($context, $personalDetails);
        $this->setLayout($breadcrumbs);

        $dobDate = $this->getDateOfBirth($profile);
        $params = [
            'dobDay' => $dobDate instanceof \DateTime ? $dobDate->format('d') : '',
            'dobMonth' => $dobDate instanceof \DateTime ? $dobDate->format('m') : '',
            'dobYear' => $dobDate instanceof \DateTime ? $dobDate->format('Y') : '',
        ];

        if ($this->getRequest()->isPost()) {
            $postData = [
                DateOfBirthValidator::FIELD_DAY => $this->getRequest()->getPost('dobDay'),
                DateOfBirthValidator::FIELD_MONTH => $this->getRequest()->getPost('dobMonth'),
                DateOfBirthValidator::FIELD_YEAR => $this->getRequest()->getPost('dobYear'),
            ];

            try {
                if ($this->validate($postData)) {
                    $this->accountAdminService->updateDateOfBirth(
                        $personId,
                        $postData
                    );

                    $this->flashMessenger()->addSuccessMessage(self::SUCCESS_MSG);

                    return $this->redirect()->toUrl($this->personProfileUrlGenerator->toPersonProfile());
                } else {
                    $params = [
                        'dobDay' => $this->getRequest()->getPost('dobDay'),
                        'dobMonth' => $this->getRequest()->getPost('dobMonth'),
                        'dobYear' => $this->getRequest()->getPost('dobYear'),
                    ];
                }
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage(self::FAILURE_MSG);
            }
        }

        return $this->createViewModel('profile/edit-date-of-birth.phtml', $params);
    }

    private function getPersonId()
    {
        $personId = (int) $this->params()->fromRoute('id', null);
        $identity = $this->getIdentity();

        if ($personId == 0) {
            $personId = $identity->getUserId();
        }

        return $personId;
    }

    private function setLayout(array $breadcrumbs)
    {
        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE);
        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
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
        $variables += ['errors' => $this->validationErrors];
        $viewModel->setVariables($variables);

        return $viewModel;
    }

    /**
     * @param $context
     * @param PersonalDetails $personalDetails
     *
     * @return array
     */
    private function generateBreadcrumbsFromRequest($context, PersonalDetails $personalDetails)
    {
        $breadcrumbs = [];

        $personProfileUrl = $this->personProfileUrlGenerator->toPersonProfile();

        if (ContextProvider::AE_CONTEXT === $context) {
            /*
             * AE context.
             */
            $aeId = $this->params()->fromRoute('authorisedExaminerId');
            $ae = $this->mapperFactory->Organisation->getAuthorisedExaminer($aeId);
            $aeUrl = $this->url()->fromRoute('authorised-examiner', ['id' => $ae->getId()]);
            $breadcrumbs += [$ae->getName() => $aeUrl];
            $breadcrumbs += [$personalDetails->getFullName() => $personProfileUrl];
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
            $breadcrumbs += [$personalDetails->getFullName() => $personProfileUrl];
        } elseif (ContextProvider::USER_SEARCH_CONTEXT === $context) {
            /*
             * User search context.
             */
            $userSearchUrl = $this->url()->fromRoute('user_admin/user-search');
            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__USER_SEARCH => $userSearchUrl];
            $breadcrumbs += [$personalDetails->getFullName() => $personProfileUrl];
        } elseif (ContextProvider::YOUR_PROFILE_CONTEXT === $context) {
            /*
             * Your Profile context.
             */
            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__YOUR_PROFILE => ''];
        } else {
            /*
             * Undefined context.
             */
            $breadcrumbs += [$personalDetails->getFullName() => ''];
        }

        $breadcrumbs += ['Change date of birth' => ''];

        return $breadcrumbs;
    }

    private function validate($postData)
    {
        if ($this->dayOfBirthValidator->isValid($postData)) {
            return true;
        } else {
            foreach ($this->dayOfBirthValidator->getMessages() as $type => $msg) {
                $this->validationErrors[self::FIELD_NAME] = $msg;
            }

            return false;
        }
    }

    /**
     * @param PersonHelpDeskProfileDto $profile
     *
     * @return \DateTime|null
     *
     * @throws \DvsaCommon\Date\Exception\IncorrectDateFormatException
     */
    private function getDateOfBirth(PersonHelpDeskProfileDto $profile)
    {
        $dobStr = $profile->getDateOfBirth();
        $dobDate = null;
        try {
            $dobDate = DateUtils::toDate($dobStr);

            return $dobDate;
        } catch (\Exception $ex) {
        }

        return $dobDate;
    }
}
