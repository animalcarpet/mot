<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
namespace Account\AbstractClass;

use Account\Service\SecurityQuestionService;
use Application\Helper\PrgHelper;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use DvsaCommon\UrlBuilder\UrlBuilder;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * Abstract SecurityQuestion Controller.
 */
abstract class AbstractSecurityQuestionController extends AbstractDvsaMotTestController
{
    /**
     * @var SecurityQuestionService
     */
    protected $service;

    /**
     * @var AbstractSecurityQuestionViewModel
     */
    protected $viewModel;

    /** @var UserAdminSessionManager $userAdminSessionManager */
    protected $userAdminSessionManager;

    /**
     * AbstractSecurityQuestionController constructor.
     *
     * @param SecurityQuestionService $securityQuestionService
     */
    public function __construct(SecurityQuestionService $securityQuestionService,
                                UserAdminSessionManager $userAdminSessionManager)
    {
        $this->service = $securityQuestionService;
        $this->userAdminSessionManager = $userAdminSessionManager;
    }

    /**
     * This action is the end point to enter the question answer for the help desk.
     *
     * @param int $personId
     * @param int $questionNumber
     *
     * @return Response|ViewModel
     */
    public function index($personId, $questionNumber, $viewModel)
    {
        /* @var Request $request */
        $request = $this->getRequest();

        $prgHelper = new PrgHelper($request);
        if ($prgHelper->isRepeatPost()) {
            return $this->redirect()->toUrl($prgHelper->getRedirectUrl());
        }

        $this->service->setUserAndQuestion($personId, $questionNumber);

        $this->viewModel = $viewModel;

        if ($this->service->manageSessionQuestion($request, $this->flashMessenger()) === true) {
            $urlNext = $this->viewModel->getNextPageLink($this->flashMessenger());
            $prgHelper->setRedirectUrl($urlNext instanceof UrlBuilder ? $urlNext->toString() : $urlNext);

            return $this->redirect()->toUrl($urlNext);
        } elseif ($request->isPost()) {
            $urlCurrent = (string) $this->viewModel->getCurrentLink();
            $prgHelper->setRedirectUrl($urlCurrent);

            return $this->redirect()->toUrl($urlCurrent);
        }

        return $this->initViewModelInformation($prgHelper);
    }

    /**
     * This function initialise the view model.
     *
     * @param PrgHelper $prgHelper
     *
     * @return ViewModel
     */
    private function initViewModelInformation($prgHelper)
    {
        $this->layout('layout/layout-govuk.phtml');

        try {
            $question = $this->viewModel->getQuestion();
        } catch (NotFoundException $e) {
            return $this->redirect()->toUrl(AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated());
        }

        return new ViewModel(
            [
                'viewModel' => $this->viewModel,
                'question' => $question,
                'numberOfAttemptMessage' => $this->service->getNumberOfAttemptMessage(),
                'prgHelper' => $prgHelper,
            ]
        );
    }
}
