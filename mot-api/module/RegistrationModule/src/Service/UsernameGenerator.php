<?php

namespace Dvsa\Mot\Api\RegistrationModule\Service;

use Dvsa\Mot\Api\RegistrationModule\Service\Exception\UserLimitReachedException;
use DvsaCommonApi\Service\AbstractService;
use DvsaEntities\Repository\PersonRepository;
use Stringy\Stringy;

/**
 * Class RegistrationService.
 */
class UsernameGenerator extends AbstractService
{
    const MAX_USERNAME_PART_LENGTH = 4;
    const USERNAME_LOWER_LIMIT = '0000';
    const USERNAME_UPPER_LIMIT = '99999';
    const USERNAME_REGEX_FILTER = '[^A-Z]';

    /**
     * @var PersonRepository
     */
    private $personRepo;

    /**
     * @param PersonRepository $personRepo
     */
    public function __construct(PersonRepository $personRepo)
    {
        $this->personRepo = $personRepo;
    }

    /**
     * generates a username based on the user's name.
     *
     * @param string $forename
     * @param string $surname
     *
     * @return string
     */
    public function generateUsername($forename, $surname, $password)
    {
        $userNamePart = $this->generateUserNamePart($forename, $surname);
        $numberPart = $this->generateUserNameNumber($userNamePart);
        $username = $userNamePart.$numberPart;

        return $this->checkUsernameDifferentFromPassword($username, $password);
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return string
     */
    private function checkUsernameDifferentFromPassword($username, $password)
    {
        $password = strtoupper($password);
        if ($username === $password) {
            $usernameLetters = mb_substr($username, 0, self::MAX_USERNAME_PART_LENGTH);

            return $usernameLetters.$this->getNextUsernameNumber($username);
        }

        return $username;
    }

    /**
     * generates a username based on the user's name.
     *
     * @param string $forename
     * @param string $surname
     *
     * @throws \Exception
     *
     * @return string
     */
    private function generateUsernamePart($forename, $surname)
    {
        $forename = Stringy::create($forename)->toAscii()->toUpperCase()->regexReplace(self::USERNAME_REGEX_FILTER, '');
        $surname = Stringy::create($surname)->toAscii()->toUpperCase()->regexReplace(self::USERNAME_REGEX_FILTER, '');

        if ($forename->length() === 0 || $surname->length() === 0) {
            throw new \InvalidArgumentException('Forename or Surname is empty');
        }

        $usernamePart = $surname->substr(0, self::MAX_USERNAME_PART_LENGTH);

        if ($usernamePart->length() < self::MAX_USERNAME_PART_LENGTH) {
            $usernamePart = $usernamePart->append(
                $forename->substr(0, self::MAX_USERNAME_PART_LENGTH - $usernamePart->length())
            );
        }

        return str_pad($usernamePart, self::MAX_USERNAME_PART_LENGTH, $usernamePart);
    }

    /**
     * @param string $usernamePart
     *
     * @throws \Exception
     *
     * @return string
     */
    private function generateUserNameNumber($usernamePart)
    {
        $lastUsername = $this->getLastUsername($usernamePart);
        $this->assertUsernameUpperLimitNotReached($lastUsername);

        return $this->getNextUsernameNumber($lastUsername);
    }

    /**
     * Change made as a result of DBA comments.  If Username limit reached throw an exception.
     *
     * @param string $lastUsername
     *
     * @throws \Exception
     */
    private function assertUsernameUpperLimitNotReached($lastUsername)
    {
        if (null != $lastUsername) {
            $number = mb_substr($lastUsername, self::MAX_USERNAME_PART_LENGTH);
            if ($number === self::USERNAME_UPPER_LIMIT) {
                throw new UserLimitReachedException();
            }
        }
    }

    /**
     * gets the last username from the DB, returns null if no users found.
     *
     * @param $username
     *
     * @return string|null
     */
    private function getLastUsername($username)
    {
        return $this->personRepo->getLastUsername($username, self::USERNAME_LOWER_LIMIT, self::USERNAME_UPPER_LIMIT);
    }

    /**
     * @param string $username
     *
     * @return string
     */
    private function getNextUsernameNumber($username)
    {
        // if Username is null intval will return 0 (no current user's with that username in DB)
        $number = intval(mb_substr($username, self::MAX_USERNAME_PART_LENGTH)) + 1;

        return sprintf('%04d', $number);
    }
}
