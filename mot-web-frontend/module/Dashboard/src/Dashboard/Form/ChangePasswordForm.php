<?php

namespace Dashboard\Form;

use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\OpenAM\Exception\OpenAMClientException;
use Dvsa\OpenAM\Model\OpenAMLoginDetails;
use Dvsa\OpenAM\OpenAMClientInterface;
use DvsaCommon\InputFilter\Account\ChangePasswordInputFilter;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\StringUtils;

class ChangePasswordForm extends PasswordForm
{
    const FIELD_OLD_PASSWORD = ChangePasswordInputFilter::FIELD_OLD_PASSWORD;
    const LABEL_OLD_PASSWORD = 'Current password';

    private $identityProvider;
    private $client;
    private $realm;

    /** @var \Zend\Form\Element\Password */
    private $oldPasswordElement;

    public function __construct(
        MotFrontendIdentityProviderInterface $identityProvider,
        OpenAMClientInterface $client,
        $realm,
        $options = [])
    {
        parent::__construct($identityProvider, $options);

        $this->identityProvider = $identityProvider;
        $this->client = $client;
        $this->realm = $realm;

        $this->add(
            [
                'name' => self::FIELD_OLD_PASSWORD,
                'type' => 'password',
                'options' => [
                    'label' => self::LABEL_OLD_PASSWORD,
                ],
                'attributes' => [
                    'id' => self::FIELD_OLD_PASSWORD,
                    'maxlength' => self::PASSWORD_MAX_LENGTH,
                    'group' => true,
                ],
            ],
            [
                'priority' => 3,
            ]
        );

        $this->oldPasswordElement = $this->get(self::FIELD_OLD_PASSWORD);

        $this->setInputFilter($this->createInputFilter());
    }

    public function setData($data)
    {
        $data = $this->deObfuscatedDate($data);

        return parent::setData($data);
    }

    private function deObfuscatedDate($data)
    {
        $data = ArrayUtils::mapWithKeys($data,
            function ($key, $value) {
                $newKey = StringUtils::startsWith($key, self::FIELD_OLD_PASSWORD)
                    ? self::FIELD_OLD_PASSWORD
                    : $key;

                return $newKey;
            },
            function ($key, $value) {
                return $value;
            });

        return $data;
    }

    public function obfuscateOldPasswordElementName()
    {
        $timestamp = (new \DateTime('now'))->getTimestamp();

        $obfuscatedName = self::FIELD_OLD_PASSWORD.'-'.$timestamp;

        $this->getOldPasswordElement()->setName($obfuscatedName);
    }

    public function getOldPasswordElement()
    {
        return $this->oldPasswordElement;
    }

    public function isValid()
    {
        $currentPasswordValid = $this->isCurrentPasswordValid();
        $newPasswordValid = parent::isValid();

        return $newPasswordValid && $currentPasswordValid;
    }

    public function getElements()
    {
        return parent::getIterator();
    }

    private function isCurrentPasswordValid()
    {
        $username = $this->identityProvider->getIdentity()->getUsername();
        $password = $this->get(ChangePasswordInputFilter::FIELD_OLD_PASSWORD)->getValue();
        $loginDetails = new OpenAMLoginDetails($username, $password, $this->realm);

        $currentPasswordValid = true;
        try {
            $this->client->validateCredentials($loginDetails);
        } catch (OpenAMClientException $e) {
            $currentPasswordValid = false;
            $this->get(self::FIELD_OLD_PASSWORD)->setMessages([ChangePasswordInputFilter::MSG_PASSWORD_INVALID]);
        }

        return $currentPasswordValid;
    }

    private function createInputFilter()
    {
        $inputFilter = new ChangePasswordInputFilter($this->identityProvider);
        $inputFilter->init();

        return $inputFilter;
    }
}
