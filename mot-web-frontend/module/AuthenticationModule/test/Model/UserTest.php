<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Model;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\User;
use PHPUnit_Framework_TestCase;

/**
 * Class UserTest.
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    public function testUserInitialState()
    {
        $user = new User();

        $this->assertNull($user->username);
        $this->assertNull($user->password);
    }

    public function testExchangeArraySetsPropertiesCorrectly()
    {
        $user = new User();
        $data = ['username' => 'test_username',
                       'password' => 'test_p4ssw0rd', ];

        $user->exchangeArray($data);

        $this->assertSame($data['username'], $user->username);
        $this->assertSame($data['password'], $user->password);
    }

    public function testExchangeArraySetsPropertiesToNullIfKeysAreNotPresent()
    {
        $user = new User();
        $data = ['username' => 'test_username',
                       'password' => 'test_p4ssw0rd', ];

        $user->exchangeArray($data);
        $user->exchangeArray([]);

        $this->assertNull($user->username);
        $this->assertNull($user->password);
    }
}
