<?php

namespace AccountTest\ViewModel;

use Account\ViewModel\PasswordResetFormModel;

class PasswordResetFormModelTest extends \PHPUnit_Framework_TestCase
{
    const USERNAME = 'USERNAME';
    const EXPIRY_TIME = 900;
    const EMAIL = 'myemail_address@test-domain.com';

    public function testGetterSetter()
    {
        $model = new PasswordResetFormModel();

        $this->assertInstanceOf(PasswordResetFormModel::class, $model->setUsername(self::USERNAME));
        $this->assertInstanceOf(PasswordResetFormModel::class, $model->setCfgExpireTime(self::EXPIRY_TIME));
        $this->assertInstanceOf(PasswordResetFormModel::class, $model->setEmail(self::EMAIL));
        $this->assertSame(self::USERNAME, $model->getUsername());
        $this->assertSame(self::EXPIRY_TIME, $model->getCfgExpireTime());
        $this->assertSame(self::EMAIL, $model->getEmail());

        $this->assertTrue($model->isValid());
        $model->populateFromPost([]);
        $this->assertFalse($model->isValid());

        $this->assertSame('/forgotten-password', $model->getCurrentPage()->toString());
    }

    /**
     * This is the data provided for the test.
     *
     * @dataProvider testGetObscuredEmailDataProvider
     *
     * @param $email
     * @param $expected
     */
    public function testGetObscuredEmail($email, $expected)
    {
        $model = new PasswordResetFormModel();
        $model->setEmail($email);

        $this->assertEquals($expected, $model->getObscuredEmailAddress());
    }

    public static function testGetObscuredEmailDataProvider()
    {
        return [
            [
                'email' => self::EMAIL,
                'expected' => 'mye••••••••••••@test-domain.com',
            ],
            [
                'email' => '',
                'expected' => '',
            ],
            [
                'email' => null,
                'expected' => '',
            ],
            [
                'email' => '123456789_123456789_123456789_123456789_1234567890@gmail.com',
                'expected' => '123•••••••••••••••••••••••••••••••••••••••••••••••@gmail.com',
            ],
            [
                'email' => 'abcdefghij@123456789_123456789_123456789_123456789.com',
                'expected' => 'abc•••••••@123456789_123456789_123456789_123456789.com',
            ],
            [
                'email' => 'abc@123.com',
                'expected' => 'abc@123.com',
            ],
            [
                'email' => 'a@123.com',
                'expected' => 'a@123.com',
            ],
            [
                'email' => '123"@"123@123.com',
                'expected' => '123••••••@123.com',
            ],
            [
                'email' => '1"@"123@123.com',
                'expected' => '1"@••••@123.com',
            ],
        ];
    }
}
