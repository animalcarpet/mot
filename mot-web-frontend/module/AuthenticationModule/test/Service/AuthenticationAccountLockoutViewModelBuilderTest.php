<?php

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Service;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\WebLoginResult;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\AuthenticationAccountLockoutViewModelBuilder;
use Dvsa\OpenAM\OpenAMAuthProperties;
use DvsaCommon\Authn\AuthenticationResultCode;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\NonPersistent;

class AuthenticationAccountLockoutViewModelBuilderTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateFromAuthenticationResponse_givenAccountLocked_shouldReturnAppropriateModel()
    {
        $dto = (new WebLoginResult())->setCode(AuthenticationResultCode::ACCOUNT_LOCKED);
        $vm = $this->create()->createFromAuthenticationResponse($dto);
        $temp = $vm->getTemplate();

        $this->assertEquals($temp, 'authentication/failed/locked');
    }

    public function dataProvider_authnCodes() {
        return [
            [AuthenticationResultCode::LOCKOUT_WARNING, 'authentication/failed/' . OpenAMAuthProperties::TEMPLATE_LOCKOUT_WARNING],
            [AuthenticationResultCode::ACCOUNT_LOCKED, 'authentication/failed/locked']
        ];
    }

    /** @dataProvider dataProvider_authnCodes */
    public function testCreateFromAuthenticationResponse_givenAuthnCode_shouldReturnAppropriateModel($authnCode, $template)
    {
        $dto = (new WebLoginResult)->setCode($authnCode);
        $vm = $this->create()->createFromAuthenticationResponse($dto);

        $this->assertEquals($template, $vm->getTemplate());
    }


    private function create()
    {
        return new AuthenticationAccountLockoutViewModelBuilder(['helpdeskConfig']);
    }

    private function getHelpdeskConfig() {
        return ['helpdeskConfig'];
    }

}