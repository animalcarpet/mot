<?php

namespace Application\View\Helper;

use Application\Service\CanTestWithoutOtpService;
use Zend\View\Helper\AbstractHelper;

class CanTestWithoutOtp extends AbstractHelper
{
    /**
     * @var CanTestWithoutOtpService
     */
    private $canTestWithoutOtpService;

    /**
     * CanTestWithoutOtp constructor.
     *
     * @param CanTestWithoutOtpService $canTestWithoutOtpService
     */
    public function __construct(CanTestWithoutOtpService $canTestWithoutOtpService)
    {
        $this->canTestWithoutOtpService = $canTestWithoutOtpService;
    }

    /**
     * @return bool
     */
    public function __invoke()
    {
        return $this->canTestWithoutOtpService->canTestWithoutOtp();
    }
}
