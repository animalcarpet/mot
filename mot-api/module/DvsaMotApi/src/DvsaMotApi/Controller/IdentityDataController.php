<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaAuthentication\Identity;

/**
 * That controller should be deleted once OpenAM session and PHP session timeouts are realigned.
 */
class IdentityDataController extends AbstractDvsaRestfulController
{
    public function getList()
    {
        /** @var ServiceLocatorInterface $sm */
        $sm = $this->getServiceLocator();
        /** @var AuthenticationService $motIdentityProvider */
        $motIdentityProvider = $sm->get('DvsaAuthenticationService');
        /** @var Identity $identity */
        $identity = $motIdentityProvider->getIdentity();

        $personData = [
            'userId' => $identity->getUserId(),
            'username' => $identity->getUsername(),
            'displayName' => $identity->getDisplayName(),
            'role' => '',
            'isAccountClaimRequired' => $identity->isAccountClaimRequired(),
            'isPasswordChangeRequired' => $identity->isPasswordChangeRequired(),
            'isSecondFactorRequired' => $identity->isSecondFactorRequired(),
            'passwordExpiryDate' => DateTimeApiFormat::dateTime($identity->getPasswordExpiryDate()),
        ];
        $returnStruct['user'] = $personData;
        $returnStruct['identity'] = $identity->getUsername();

        return ApiResponse::jsonOk($returnStruct);
    }
}
