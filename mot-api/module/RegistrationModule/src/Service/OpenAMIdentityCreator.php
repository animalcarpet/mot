<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Service;

use Dvsa\OpenAM\Model\OpenAMLoginDetails;
use Dvsa\OpenAM\Model\OpenAMNewIdentity;
use Dvsa\OpenAM\OpenAMClient;
use Dvsa\OpenAM\Options\OpenAMClientOptions;

/**
 * Class OpenAMIdentityCreator.
 */
class OpenAMIdentityCreator
{
    const DEFAULT_OBJECT_CLASS = 'motUser';

    const EXP_EMPTY_ARG = 'Argument "%s" must be pass to %s';
    const EXP_NON_STRING_ARG = 'Argument "%s" pass to %s must be string';

    /**
     * @var OpenAMClient
     */
    private $openAMClient;

    /**
     * @var OpenAMClientOptions
     */
    private $openAMClientOptions;

    public function __construct(
        OpenAMClient $openAMClient,
        OpenAMClientOptions $openAMClientOptions
    ) {
        $this->openAMClient = $openAMClient;
        $this->openAMClientOptions = $openAMClientOptions;
    }

    /**
     * Create and identity with the same recently registered person's credential to allow them to login.
     *
     * @param string      $username
     * @param string      $password
     * @param string      $firstName
     * @param string      $lastName
     * @param string|null $objectClass Default to 'motUser @see self:DEFAULT_OBJECT_CLASS
     *
     * @return OpenAMNewIdentity
     */
    public function createIdentity($username, $password, $firstName, $lastName, $objectClass = null)
    {
        $this->validateArguments(
            [
                '$username' => $username,
                '$password' => $password,
                '$firstName' => $firstName,
                '$lastName' => $lastName,
            ]
        );

        $attributes = [
            'sn' => $lastName,
            'cn' => sprintf('%s %s', $firstName, $lastName),
            'objectclass' => is_null($objectClass) ? self::DEFAULT_OBJECT_CLASS : $objectClass,
        ];

        $motIdentity = new OpenAMNewIdentity(
            new OpenAMLoginDetails(
                $username,
                $password,
                $this->openAMClientOptions->getRealm()
            ),
            $attributes
        );

        $this->openAMClient->createIdentity($motIdentity);

        return $motIdentity;
    }

    /**
     * @param array $args
     *
     * @throws InvalidArgumentException
     */
    private function validateArguments($args)
    {
        foreach ($args as $name => $value) {
            if (!is_string($value)) {
                throw new \InvalidArgumentException(
                    sprintf(self::EXP_NON_STRING_ARG, $name, debug_backtrace()[1]['function'])
                );
            } elseif (empty($value)) {
                throw new \InvalidArgumentException(
                    sprintf(self::EXP_EMPTY_ARG, $name, debug_backtrace()[1]['function'])
                );
            }
        }
    }
}
