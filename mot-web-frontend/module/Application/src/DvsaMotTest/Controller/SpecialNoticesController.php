<?php

namespace DvsaMotTest\Controller;

use Application\Data\ApiPersonalDetails;
use Core\Authorisation\Assertion\WebAcknowledgeSpecialNoticeAssertion;
use Core\Controller\AbstractAuthActionController;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\Frontend\Traits\ReportControllerTrait;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\SpecialNoticeAudience;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\DateException;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Utility\ArrayUtils;
use DvsaMotTest\Model\SpecialNotice;
use MaglMarkdown\Service\Markdown;
use Zend\Form\FormInterface;
use Zend\View\Model\ViewModel;

/**
 * Class SpecialNoticesController.
 */
class SpecialNoticesController extends AbstractAuthActionController
{
    use ReportControllerTrait;

    const FORM_ERROR_TARGET_ROLES = 'You must select recipients from the target groups';
    const ROUTE_PRINT_SPECIAL_NOTICE = 'dvsa-mot-test/special-notices/print-special-notice';
    const ROUTE_SPECIAL_NOTICES_PREVIEW = 'special-notices/preview';
    const ROUTE_SPECIAL_NOTICES = 'special-notices';
    const ERROR_DATE_IN_PAST = 'The %s cannot be in the past';

    /**
     * @var \MaglMarkdown\Service\Markdown
     */
    protected $markdown;

    /**
     * @var array
     */
    private $testClasses
        = [
            SpecialNoticeAudience::TESTER_CLASS_1,
            SpecialNoticeAudience::TESTER_CLASS_2,
            SpecialNoticeAudience::TESTER_CLASS_3,
            SpecialNoticeAudience::TESTER_CLASS_4,
            SpecialNoticeAudience::TESTER_CLASS_5,
            SpecialNoticeAudience::TESTER_CLASS_7,
            SpecialNoticeAudience::DVSA,
            SpecialNoticeAudience::VTS,
        ];

    /**
     * @var WebAcknowledgeSpecialNoticeAssertion
     */
    private $acknowledgeSpecialNoticeAssertion;

    /**
     * @var ApiPersonalDetails
     */
    private $apiPersonalDetails;

    /**
     * @param Markdown                             $markdown
     * @param WebAcknowledgeSpecialNoticeAssertion $acknowledgeSpecialNoticeAssertion
     */
    public function __construct(
        Markdown $markdown,
        WebAcknowledgeSpecialNoticeAssertion $acknowledgeSpecialNoticeAssertion,
        ApiPersonalDetails $apiPersonalDetails
    ) {
        $this->markdown = $markdown;
        $this->acknowledgeSpecialNoticeAssertion = $acknowledgeSpecialNoticeAssertion;
        $this->apiPersonalDetails = $apiPersonalDetails;
    }

    /**
     * @return ViewModel
     */
    public function displaySpecialNoticesAction()
    {
        $this->assertGranted(PermissionInSystem::SPECIAL_NOTICE_READ_CURRENT);

        $userId = $this->getIdentity()->getUserId();
        $specialNoticesData = [
            'overdue' => null,
            'unread' => null,
            'currentNotices' => null,
        ];
        $specialNotices = [];

        try {
            $specialNoticesApiPath = (new UrlBuilder())
                ->specialNotice()
                ->routeParam('id', $userId)
                ->toString();
            $result = $this->getRestClient()
                ->get($specialNoticesApiPath);
            $specialNotices = $result['data'];
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        foreach ($specialNotices as $specialNotice) {
            $targetRoles = isset($specialNotice['content']['targetRoles']) ? $specialNotice['content']['targetRoles']
                : null;
            $specialNotice['content']['targetRoles'] = $this->getTargetRoles($targetRoles);

            if (!$specialNotice['isAcknowledged'] && $specialNotice['isExpired']) {
                $specialNoticesData['overdue'][] = $specialNotice;
            } elseif (!$specialNotice['isAcknowledged'] && !$specialNotice['isExpired']) {
                $specialNotice['daysLeftToView'] = $this->getExpiringInDays($specialNotice['content']['expiryDate']);
                $specialNoticesData['unread'][] = $specialNotice;
            } else {
                $specialNoticesData['currentNotices'][] = $specialNotice;
            }
        }

        $unreadExpiringNext = $specialNoticesData['unread'] ? $this->getNoticeWithLeastExpiryTime(
            $specialNoticesData['unread']
        ) : null;
        $daysLeftToViewUnread = $unreadExpiringNext['content']['expiryDate'] ? $this->getExpiringInDays(
            $unreadExpiringNext['content']['expiryDate']
        ) : null;

        return new ViewModel(
            [
                'specialNotices' => $specialNoticesData,
                'daysLeftToViewUnread' => $daysLeftToViewUnread,
                'config' => $this->getConfig(),
                'canAcknowledge' => $this->acknowledgeSpecialNoticeAssertion->isGranted(),
                'canRemove' => $this->getAuthorizationService()->isGranted(PermissionInSystem::SPECIAL_NOTICE_REMOVE),
                'canUpdate' => $this->getAuthorizationService()->isGranted(PermissionInSystem::SPECIAL_NOTICE_UPDATE),
                'isTester' => $this->checkIfUserIsTester(),
            ]
        );
    }

    /**
     * @return \DvsaCommon\Traits\Response|\DvsaCommon\Traits\ViewModel
     */
    public function printSpecialNoticeAction()
    {
        $id = $this->params()->fromRoute('id');

        $specialNoticeContentApiPath = (new UrlBuilder())
            ->specialNoticeContent()
            ->routeParam('id', $id)
            ->toString();

        $data = $this->getRestClient()->get($specialNoticeContentApiPath);
        $data['data']['targetRoles'] = $this->getTargetRoles($data['data']['targetRoles']);

        /*
         * And now some Markdown to HTML conversion.
         *
         * This early conversion will assure all content reaching renderReport() will be HTML-only. The HTML is
         * properly filtered and validated in the view using the HTMLPurifier library.
         *
         * From the ZF2 documentation:
         *  "Currently the best available library for filtering and validating (x)HTML data in PHP is HTMLPurifier and,
         *  as such, is the recommended tool for this task. HTMLPurifier works by filtering out all (x)HTML from the
         *  data, except for the tags and attributes specifically allowed in a whitelist, and by checking and fixing
         *  nesting of tags, ensuring a standards-compliant output."
         *
         * IMPORTANT: Please ensure $this->escapeHtml() is not used in the view as otherwise it will break the PDF
         * output.
         */
        $data['data']['noticeText'] = isset($data['data']['noticeText']) ?
            $this->markdown->render($data['data']['noticeText']) : '';

        return $this->renderReport(
            [
                'specialNotice' => $data['data'],
                'config' => $this->getConfig(),
            ],
            self::ROUTE_PRINT_SPECIAL_NOTICE,
            'Special Notice',
            false
        );
    }

    /**
     * @return \Zend\Http\Response
     */
    public function acknowledgeSpecialNoticeAction()
    {
        $this->acknowledgeSpecialNoticeAssertion->assertGranted();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $userId = $this->getIdentity()->getUserId();
            $specialNoticeId = (int) $this->params()->fromRoute('id', null);

            $specialNoticesAcknowledgeApiPath = (new UrlBuilder())
                ->specialNotice()
                ->routeParam('id', $userId)
                ->routeParam('snId', $specialNoticeId)
                ->toString();
            $specialNoticeData = [
                'isAcknowledged' => true,
            ];

            $this
                ->getRestClient()
                ->postJson($specialNoticesAcknowledgeApiPath, $specialNoticeData);
            $this->addInfoMessages('Special notice acknowledged');

            $this->getAuthorizationRefresher()->refreshAuthorization();
        }

        return $this->redirect()->toRoute(self::ROUTE_SPECIAL_NOTICES);
    }

    public function displayAllSpecialNoticesAction()
    {
        $this->assertGranted(PermissionInSystem::SPECIAL_NOTICE_READ);

        return $this->displaySpecialNotices(false);
    }

    /**
     * @return ViewModel
     */
    public function displayRemovedSpecialNoticesAction()
    {
        $this->assertGranted(PermissionInSystem::SPECIAL_NOTICE_READ_REMOVED);

        return $this->displaySpecialNotices(true);
    }

    /**
     * @param $removed
     *
     * @return ViewModel
     */
    protected function displaySpecialNotices($removed)
    {
        $specialNotices = [];

        try {
            $specialNoticesApiPath = (new UrlBuilder())
                ->specialNoticeContent()
                ->queryParam('removed', $removed)
                ->queryParam('listAll', $this->getAuthorizationService()->isGranted(PermissionInSystem::SPECIAL_NOTICE_CREATE))
                ->toString();
            $result = $this->getRestClient()->get($specialNoticesApiPath);
            $specialNotices = $result['data'];
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        $specialNoticesData = [];

        foreach ($specialNotices as $specialNotice) {
            $specialNotice['content'] = $specialNotice;
            $targetRoles = isset($specialNotice['content']['targetRoles']) ? $specialNotice['content']['targetRoles']
                : null;
            $specialNotice['content']['targetRoles'] = $this->getTargetRoles($targetRoles);
            $specialNotice['isAcknowledged'] = true;
            $specialNotice['isExpired'] = true;

            $specialNoticesData[] = $specialNotice;
        }

        $viewModel = new ViewModel(
            [
                'specialNotices' => $specialNoticesData,
                'removed' => $removed,
                'config' => $this->getConfig(),
                'canAcknowledge' => $this->acknowledgeSpecialNoticeAssertion->isGranted(),
                'canRemove' => $this->getAuthorizationService()->isGranted(PermissionInSystem::SPECIAL_NOTICE_REMOVE),
                'canUpdate' => $this->getAuthorizationService()->isGranted(PermissionInSystem::SPECIAL_NOTICE_UPDATE),
            ]
        );
        $viewModel->setTemplate('dvsa-mot-test/special-notices/display-all-special-notices');

        return $viewModel;
    }

    /**
     * @return ViewModel
     */
    public function createSpecialNoticeAction()
    {
        $this->assertGranted(PermissionInSystem::SPECIAL_NOTICE_CREATE);

        $form = $this->getForm(new SpecialNotice())
            ->setAttribute('action', $this->url()->fromRoute('special-notices/create'));

        return $this->createOrEdit(
            (new UrlBuilder())->specialNoticeContent()->toString(),
            $form,
            false
        );
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function editAction()
    {
        $this->assertGranted(PermissionInSystem::SPECIAL_NOTICE_UPDATE);

        $id = $this->params()->fromRoute('id');

        $vm = new SpecialNotice();
        if ($this->request->isGet()) {
            $specialNoticeData = $this
                ->getRestClient()
                ->get((new UrlBuilder())->specialNoticeContent()->routeParam('id', $id)->toString());

            $vm->exchangeArray($specialNoticeData['data']);
        }

        $form = $this->getForm($vm)
            ->bind($vm)
            ->setAttribute('action', $this->url()->fromRoute('special-notices/edit', ['id' => $id]));

        return $this->createOrEdit(
            (new UrlBuilder())->specialNoticeContent()->routeParam('id', $id)->toString(),
            $form,
            true
        );
    }

    /**
     * @param $url
     * @param $form
     * @param $isEdit
     *
     * @return \Zend\Http\Response|ViewModel
     */
    private function createOrEdit($url, $form, $isEdit)
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                try {
                    //Get the data out of the form, so you use the form's validation.
                    $data = $form->getData(FormInterface::VALUES_AS_ARRAY);
                    $data['internalPublishDate'] = DateUtils::changeFormat($data['internalPublishDate'], 'Y-n-d', 'Y-m-d');
                    $data['externalPublishDate'] = DateUtils::changeFormat($data['externalPublishDate'], 'Y-n-d', 'Y-m-d');

                    if ($isEdit) {
                        $result = $this->getRestClient()->putJson($url, $data);
                    } else {
                        $result = $this->getRestClient()->postJson($url, $data);
                    }

                    return $this->redirect()->toRoute(
                        self::ROUTE_SPECIAL_NOTICES_PREVIEW,
                        ['id' => $result['data']['id']]
                    );
                } catch (RestApplicationException $e) {
                    $this->addErrorMessages($e->getDisplayMessages());
                } catch (DateException $e) {
                    $this->addErrorMessages($e->getMessage());
                }
            } else {
                $messages = $form->getMessages();
                foreach ($messages as $element => $message) {
                    $element === 'targetRoles' ? $this->addErrorMessages(self::FORM_ERROR_TARGET_ROLES)
                        : $this->addErrorMessages($message);
                }
            }
        }

        return (new ViewModel(
            [
                'userDetails' => $this->getUserDisplayDetails(),
                'form' => $form,
            ]
        ))->setTemplate('dvsa-mot-test/special-notices/create-or-edit');
    }

    /**
     * @throws UnauthorisedException
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function previewSpecialNoticeAction()
    {
        $canCreate = $this->getAuthorizationService()->isGranted(PermissionInSystem::SPECIAL_NOTICE_CREATE);
        $canEdit = $this->getAuthorizationService()->isGranted(PermissionInSystem::SPECIAL_NOTICE_UPDATE);
        if (!$canCreate && !$canEdit) {
            throw new UnauthorisedException('Preview special notice assertion failed');
        }

        $specialNoticeId = (int) $this->params()->fromRoute('id', null);
        $specialNotice = null;

        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $url = (new UrlBuilder())
                    ->specialNoticeContentPublish()
                    ->routeParam('id', $specialNoticeId)
                    ->toString();

                $this->getRestClient()->putJson($url, []);
                $this->addInfoMessages('Special notice created');

                return $this->redirect()->toRoute('special-notices/all');
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }
        }

        try {
            $specialNoticesContentApiPath = (new UrlBuilder())
                ->specialNoticeContent()
                ->routeParam('id', $specialNoticeId)
                ->toString();
            $result = $this->getRestClient()
                ->get($specialNoticesContentApiPath);
            $specialNotice = $result['data'];

            $targetRoles = ArrayUtils::tryGet($specialNotice, 'targetRoles', 0);
            $specialNotice['targetRoles'] = $this->getTargetRoles($targetRoles);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return new ViewModel(
            [
                'userDetails' => $this->getUserDisplayDetails(),
                'specialNotice' => $specialNotice,
            ]
        );
    }

    /**
     * @return \Zend\Http\Response
     */
    public function removeSpecialNoticeAction()
    {
        $this->assertGranted(PermissionInSystem::SPECIAL_NOTICE_REMOVE);

        $specialNoticeId = (int) $this->params()->fromRoute('id', null);

        try {
            $specialNoticeApiPath = (new UrlBuilder())
                ->specialNoticeContent()
                ->routeParam('id', $specialNoticeId)
                ->toString();

            $this->getRestClient()
                ->delete($specialNoticeApiPath);

            $this->addInfoMessages('Special notice removed');
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return $this->redirect()->toRoute('special-notices/all');
    }

    /**
     * @param array $targetRoles
     *
     * @return null|string
     */
    private function getTargetRoles($targetRoles = [])
    {
        $roleMessage = null;
        $targetTestClasses = array_intersect($this->testClasses, $targetRoles);

        if ($targetTestClasses) {
            foreach ($targetTestClasses as $targetClass) {
                switch ($targetClass) {
                    case 'DVSA':
                        $classNumber = 'D';
                        break;
                    case 'VTS':
                        $classNumber = 'V';
                        break;
                    default:
                        $classNumber = substr($targetClass, -1);
                }

                $roleMessage = $roleMessage.$classNumber.' ';
            }
        } else {
            $roleMessage = 'None specified';
        }

        return $roleMessage;
    }

    /**
     * @param $specialNotices
     *
     * @return mixed
     */
    private function getNoticeWithLeastExpiryTime($specialNotices)
    {
        foreach ($specialNotices as $key => $row) {
            $date[$key] = $row['content']['expiryDate'];
        }

        array_multisort($date, SORT_ASC, $specialNotices);

        return $specialNotices[0];
    }

    /**
     * @param $specialNoticeExpiryDate
     *
     * @throws \DvsaCommon\Date\Exception\IncorrectDateFormatException
     *
     * @return string
     */
    private function getExpiringInDays($specialNoticeExpiryDate)
    {
        $currDateWithoutTimeStr = DateUtils::today();
        $expiryDate = DateUtils::toDate($specialNoticeExpiryDate);

        return DateUtils::getDaysDifference($currDateWithoutTimeStr, $expiryDate);
    }

    /**
     * @return MotFrontendAuthorisationServiceInterface
     */
    public function getAuthorizationService()
    {
        return $this->serviceLocator->get('AuthorisationService');
    }

    /**
     * @return \Core\Service\MotAuthorizationRefresherInterface
     */
    public function getAuthorizationRefresher()
    {
        return $this->getAuthorizationService();
    }

    /**
     * @param string $date
     * @param $field
     *
     * @throws DateException
     *
     * @return bool
     */
    protected function validatePublishDate($date, $field)
    {
        if (DateUtils::isDateInPast(new \DateTime($date))) {
            throw new DateException(sprintf(self::ERROR_DATE_IN_PAST, $field, $date));
        }

        return true;
    }

    /**
     * A person is has at least one authorisation in any status is considered to be a "tester".
     *
     * @return bool
     */
    protected function checkIfUserIsTester()
    {
        $authorisationClasses = $this->apiPersonalDetails->getPersonalAuthorisationForMotTesting($this->getIdentity()->getUserId());
        if (!is_array($authorisationClasses)) {
            return false;
        }

        foreach ($authorisationClasses as $class) {
            if (!is_null($class)) {
                return true;
            }
        }

        return false;
    }
}
