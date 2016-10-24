<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Controller;

use Core\Service\StepService;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegisterUserService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\AccountSummaryStep;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\EmailInputFilter;
use Zend\View\Model\ViewModel;

/**
 * Summary Controller.
 */
class SummaryController extends RegistrationBaseController
{
    const PAGE_TITLE = 'Account summary';

    /**
     * @var array
     */
    private $helpDeskConfig;

    /**
     * @param StepService $stepService
     * @param RegisterUserService $registerUserService
     * @param array $helpDeskConfig
     */
    public function __construct(
        StepService $stepService,
        $helpDeskConfig
    )
    {
        parent::__construct($stepService);
        $this->helpDeskConfig = $helpDeskConfig;
    }
    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $viewModel = $this->doStepLogic(AccountSummaryStep::STEP_ID, self::PAGE_TITLE, self::DEFAULT_SUB_TITLE);

        if ($viewModel instanceof ViewModel) {
            $viewModel->setVariable('helpDesk', $this->helpDeskConfig);
        }

        return $viewModel;
    }
}
