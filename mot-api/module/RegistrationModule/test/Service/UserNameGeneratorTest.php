<?php

use Dvsa\Mot\Api\RegistrationModule\Service\UsernameGenerator;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\PersonRepository;

/**
 * Class UsernameGeneratorTest.
 */
class UserNameGeneratorTest extends AbstractServiceTestCase
{
    /**
     * @var UsernameGenerator
     */
    private $userNameGenerationService;

    private $personRepo;

    public function setUp()
    {
        $this->personRepo = XMock::of(PersonRepository::class);
        $this->userNameGenerationService = new UsernameGenerator($this->personRepo);
    }

    public function testPasswordSameAsUsernameSame()
    {
        $expected = 'MARI0002';
        $actual = $this->userNameGenerationService->generateUsername('test', 'mari', 'MARI0001');
        $this->assertSame($expected, $actual);
    }

    public function testPasswordSameAsUsernameSameLowerCase()
    {
        $expected = 'MARI0002';
        $actual = $this->userNameGenerationService->generateUsername('test', 'mari', 'MarI0001');
        $this->assertSame($expected, $actual);
    }

    public function testPasswordSameAsUsernameNotSame()
    {
        $expected = 'MARI0001';
        $actual = $this->userNameGenerationService->generateUsername('test', 'mari', 'testpassword');
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider generateUsernameDataProvider
     * @group username
     */
    public function testGenerateUsernamePart($forename, $surname, $expected)
    {
        $actual = $this->userNameGenerationService->generateUsername($forename, $surname, 'password');
        $this->assertSame($expected, substr($actual, 0, 4));
    }

    public function generateUsernameDataProvider()
    {
        return [
            ['x', 'o', 'OXOX'],
            ['xo', 's', 'SXOS'],
            ['s', 'xo', 'XOSX'],
            ['s', 'xox', 'XOXS'],
            ['xox', 's', 'SXOX'],
            ['s', 'xoxo', 'XOXO'],
            ['xoxo', 's', 'SXOX'],
            ['test', 'áöß', 'AOSS'], // Test special characters are properly converted
            ['XOXO', 'XOXO', 'XOXO'], // Testing uppercase stays uppercase
            ['this', 'te-st', 'TEST'], // Testing removal of - character
            ['Mr', "O'Shea", 'OSHE'], // Testing removal of apostrophe
            ['test', '!@£$%^&*()test', 'TEST'],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Forename or Surname is empty
     * @dataProvider invalidInputDataProvider
     */
    public function testGenerateUserNamePartWithInvalidInput($forename, $surname)
    {
        $this->userNameGenerationService->generateUsername($forename, $surname, 'password');
    }

    /**
     * Data provider to test the possible invalid inputs for the generateusernamepart.
     *
     * @return array
     */
    public function invalidInputDataProvider()
    {
        return [
            ['', ''], // Both empty
            ['s', ''], // forename empty
            ['', 's'], // surname empty
            [null, null], // test null inputs
            ['!@£$%^&*()', '!@£$%^&*()'], // test if all characters are special characters throw exception
        ];
    }

    /**
     * @dataProvider generateUsernameNumberDataProvider
     */
    public function testGenerateUsernameNumber($usernamePart, $lastUsername, $expected)
    {
        $this->personRepo->expects($this->once())
            ->method('getLastUsername')
            ->willReturn($lastUsername);

        $actual = $this->userNameGenerationService->generateUsername('test', $usernamePart, 'password');
        $this->assertSame($expected, $actual);
    }

    /**
     * Data provider for testing username number generation from a usernamepart.
     *
     * @return array
     */
    public function generateUsernameNumberDataProvider()
    {
        return [
            ['mari', 'mari0001', 'MARI0002'], //If a user already exists with usernamepart mari
            ['mari', 'mari3456', 'MARI3457'], //If a user already exists with usernamepart mari
            ['mari', 'mari9999', 'MARI10000'], //does the system break with over 5 digits
            ['mari', null, 'MARI0001'], // Test case if no users with that usernamepart exist
        ];
    }

    /**
     * @expectedException \Dvsa\Mot\Api\RegistrationModule\Service\Exception\UserLimitReachedException
     * @expectedExceptionMessage Upper limit for usernames reached
     */
    public function testUserLimitReached()
    {
        $this->personRepo->expects($this->once())
            ->method('getLastUsername')
            ->willReturn('MARI'.UsernameGenerator::USERNAME_UPPER_LIMIT);

        $this->userNameGenerationService->generateUsername('test', 'MARI', 'password');
    }

    public function testGenerateUsername()
    {
        $actual = $this->userNameGenerationService->generateUsername('Maria', 'Schoon', 'password');
        $this->assertSame('SCHO0001', $actual);
    }
}
