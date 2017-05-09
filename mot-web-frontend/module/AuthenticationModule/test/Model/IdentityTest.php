<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Model;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use PHPUnit_Framework_TestCase;

/**
 * Class IdentityTest.
 */
class IdentityTest extends PHPUnit_Framework_TestCase
{
    public function testFluentInterface()
    {
        $data = $this->getIdentityData();
        $identity = new Identity();
        $val = $identity->setUsername($data['username'])
            ->setDisplayName($data['displayName'])
            ->setDisplayRole($data['displayRole'])
            ->setAccessToken($data['accessToken']);
        $this->assertEquals($data['username'], $identity->getUsername());
        $this->assertEquals($data['displayName'], $identity->getDisplayName());
        $this->assertEquals($data['displayRole'], $identity->getDisplayRole());
        $this->assertEquals($data['accessToken'], $identity->getAccessToken());
        $this->assertInstanceOf(\Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity::class, $val);
    }

    protected function getIdentityData()
    {
        return [
            'username' => 'test-name-1',
            'displayName' => 'displayNameTest',
            'displayRole' => 'Test Role',
            'testerIsActive' => true,
            'accessToken' => 'abcd1234',
        ];
    }
}
