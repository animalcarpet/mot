<?php

namespace DvsaCommonApi\Service;

use Doctrine\ORM\EntityManagerInterface;
use DvsaCommon\Auth\MotIdentity;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaEntities\Entity\Session;
use Zend\Http\Request;

/**
 * Implementation of MotIdentityProviderInterface that retrieves the identity only when necessary.
 */
class LazyIdentityProvider implements MotIdentityProviderInterface
{
    /** @var Request $request */
    private $request;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var MotIdentityInterface $cachedIdentity */
    private $cachedIdentity;

    /**
     * Since $cachedIdentity can be null, we need a separate flag to state that it has been set.
     */
    private $identityIsCached = false;

    public function __construct(Request $request, EntityManagerInterface $entityManager)
    {
        $this->request = $request;
        $this->entityManager = $entityManager;
    }

    /** @return MotIdentityInterface */
    public function getIdentity()
    {
        if (!$this->identityIsCached) {
            $this->cachedIdentity = $this->getIdentityFromHeader();
            $this->identityIsCached = true;

            if (is_null($this->cachedIdentity)) {
                //                die ("identity is null");
                // TODO - blow up. No identity = should have been prevented from getting here.
            }
        }

        return $this->cachedIdentity;
    }

    /**
     * Sets the identity. Should only by used by framework code.
     */
    public function setIdentity(MotIdentity $identity)
    {
        $this->cachedIdentity = $identity;
        $this->identityIsCached = true;
    }

    /**
     * @return MotIdentity|null
     */
    private function getIdentityFromHeader()
    {
        $authHeader = $this->request->getHeaders('Authorization');

        if (!$authHeader) {
            return null;
        }
        $accessToken = str_replace('Bearer ', '', $authHeader->getFieldValue());

        /**
         * @var Session
         */
        $session = $this->getValidSession($accessToken);

        if (!$session) {
            return null;
        }

        return new MotIdentity($session->getUserId(), $session->getUsername());
    }

    /**
     * @param $accessToken
     *
     * @return Session
     */
    private function getValidSession($accessToken)
    {
        // TODO check session is valid
        return $this->entityManager->find(Session::class, $accessToken);
    }
}
