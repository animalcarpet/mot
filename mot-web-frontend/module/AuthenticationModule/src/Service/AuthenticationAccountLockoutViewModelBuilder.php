<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Service;

use Dvsa\Mot\Frontend\AuthenticationModule\Controller\SecurityController;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\WebLoginResult;
use Dvsa\OpenAM\OpenAMAuthProperties;
use DvsaCommon\Authn\AuthenticationResultCode;
use Zend\View\Model\ViewModel;

class AuthenticationAccountLockoutViewModelBuilder
{
    /**
     * @var array
     */
    private $helpdeskConfig;

    /**
     * @param array $helpdeskConfig
     */
    public function __construct(array $helpdeskConfig)
    {
        $this->helpdeskConfig = $helpdeskConfig;
    }

    /**
     * @param WebLoginResult $response
     *
     * @return ViewModel
     */
    public function createFromAuthenticationResponse(WebLoginResult $response)
    {
        $code = $response->getCode();
        if ($code === AuthenticationResultCode::ACCOUNT_LOCKED) {
            $template = 'locked';
            $vars = [];
        } elseif ($code === AuthenticationResultCode::LOCKOUT_WARNING) {
            $template = OpenAMAuthProperties::TEMPLATE_LOCKOUT_WARNING;
            $vars = [
                'helpdesk' => $this->helpdeskConfig,
                'pageSubTitle' => SecurityController::PAGE_TITLE,
                'pageTitle' => 'Authentication failed',
            ];
        }

        $viewModel = new ViewModel($vars);
        $viewModel->setTemplate('authentication/failed/'.$template);

        return $viewModel;
    }
}
