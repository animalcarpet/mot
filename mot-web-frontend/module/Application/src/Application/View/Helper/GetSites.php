<?php

namespace Application\View\Helper;

use Application\Service\LoggedInUserManager;
use Zend\View\Helper\AbstractHelper;

/**
 * Class GetSites.
 */
class GetSites extends AbstractHelper
{
    /**
     * @var LoggedInUserManager
     */
    protected $loggedInUserManager;

    public function __construct(LoggedInUserManager $loggedInUserManager)
    {
        $this->loggedInUserManager = $loggedInUserManager;
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        /** @var LoggedInUserManager $manager */
        $vtsList = [];
        if ($this->loggedInUserManager) {
            $vtsList = $this->loggedInUserManager->getAllVts();
        }

        return $vtsList;
    }
}
