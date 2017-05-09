<?php

namespace Application\View\Helper;

use Application\Data\ApiCurrentMotTest;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\View\Helper\AbstractHelper;

/**
 * Class CurrentMotTest.
 */
class CurrentMotTest extends AbstractHelper
{
    /**
     * @var MotIdentityProviderInterface
     */
    protected $identityProvider;

    /**
     * @var ApiCurrentMotTest
     */
    protected $apiService;

    /**
     * @param MotIdentityProviderInterface $identityProvider
     */
    public function __construct(MotIdentityProviderInterface $identityProvider, ApiCurrentMotTest $apiService)
    {
        $this->identityProvider = $identityProvider;
        $this->apiService = $apiService;
    }

    /**
     * @return string|null
     */
    public function __invoke()
    {
        $identity = $this->identityProvider->getIdentity();
        if ($identity) {
            $data = $this->apiService->getCurrentMotTest($identity->getUserId());
            if (isset($data['inProgressTestNumber'])) {
                return $data['inProgressTestNumber'];
            }
        }

        return null;
    }
}
