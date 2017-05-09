<?php

namespace DvsaAuthenticationTest\Identity\OpenAM;

use Dvsa\OpenAM\Exception\OpenAMClientException;
use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use DvsaAuthentication\Identity;
use DvsaAuthentication\Identity\OpenAM\OpenAMIdentityByTokenResolver;
use DvsaAuthentication\IdentityFactory;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use Zend\Log\LoggerInterface;

class OpenAMIdentityByTokenResolverTest extends \PHPUnit_Framework_TestCase
{
    const ATTR_USERNAME = 'uid';
    const ATTR_UUID = 'entryuuid';
    const ATTR_EXPIRY_DATE = 'ds-pwp-password-expiration-time';

    private $openAMClient;

    /** @var OpenAMClientOptions */
    private $openAMClientOptions;

    private $logger;

    private $identityFactory;

    public function setUp()
    {
        $this->openAMClient = XMock::of(OpenAMClientInterface::class);
        $this->openAMClientOptions = new OpenAMClientOptions();
        $this->logger = XMock::of(LoggerInterface::class);
        $this->identityFactory = XMock::of(IdentityFactory::class);

        $this->openAMClientOptions->setIdentityAttributeUsername(self::ATTR_USERNAME);
        $this->openAMClientOptions->setIdentityAttributeUuid(self::ATTR_UUID);
        $this->openAMClientOptions->setIdentityAttributePasswordExpiryTime(self::ATTR_EXPIRY_DATE);
    }

    public function testResolve_givenIdentityRetrievalFailed_shouldReturnNull()
    {
        $inputToken = 'abcde';

        $this->openAMClient
            ->expects($this->once())
            ->method('getIdentityAttributes')
            ->with($inputToken)
            ->willThrowException(new OpenAMClientException('anyMessage'));

        $this->assertNull($this->create()->resolve($inputToken));
    }

    public function dataProvider_incompleteIdentityAttributes()
    {
        return [
            [
                $this->removeKey(
                    self::ATTR_USERNAME,
                    $this->validIdentityAttributes()
                ),
            ],
            [
                $this->removeKey(
                    self::ATTR_UUID,
                    $this->validIdentityAttributes()
                ),
            ],
            [$this->removeKey(
                self::ATTR_EXPIRY_DATE,
                $this->validIdentityAttributes()
            )],
        ];
    }

    /**
     * @dataProvider dataProvider_incompleteIdentityAttributes
     */
    public function testResolve_givenAttributeDoesNotExistInIdentityAttributes_shouldReturnNull($identityAttributes)
    {
        $inputToken = 'abcde';

        $this->openAMClient
            ->expects($this->once())
            ->method('getIdentityAttributes')
            ->with($inputToken)
            ->willReturn($identityAttributes);

        $this->assertNull($this->create()->resolve($inputToken));
    }

    public static function dataProvider_differentLetterCase()
    {
        return [[CASE_LOWER], [CASE_UPPER]];
    }

    /**
     * @dataProvider dataProvider_differentLetterCase
     */
    public function testResolve_givenRequiredIdentityAttributesInDifferentCaseShouldStillReturnIdentity($case)
    {
        $inputToken = 'abcde';
        $identity = new Identity((new Person()));

        $identityAttributes = $this->validIdentityAttributes();
        array_change_key_case($identityAttributes, $case);

        $this->openAMClient
            ->expects($this->once())
            ->method('getIdentityAttributes')
            ->with($inputToken)
            ->willReturn($identityAttributes);

        $this->identityFactory->expects($this->once())->method('create')
            ->with('usernameValue', $inputToken, 'uuidValue', new \DateTime('20001112121212'))
            ->willReturn($identity);

        $this->create()->resolve($inputToken);
    }

    private function removeKey($key, $array)
    {
        unset($array[$key]);

        return $array;
    }

    private function create()
    {
        return new OpenAMIdentityByTokenResolver(
            $this->openAMClient,
            $this->openAMClientOptions,
            $this->logger,
            $this->identityFactory
        );
    }

    private function validIdentityAttributes()
    {
        return [
            self::ATTR_USERNAME => 'usernameValue',
            self::ATTR_UUID => 'uuidValue',
            self::ATTR_EXPIRY_DATE => '20001112121212',
        ];
    }
}
