<?php

namespace OrganisationApiTest\Service;

use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Constants\PersonContactType;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine1Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine2Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine3Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\CountryInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\DateOfBirthInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\FamilyNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\FirstNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\MiddleNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\PostcodeInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\TownInput;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\AuthorisedExaminerPrincipal;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\AuthorisedExaminerPrincipalRepository;
use OrganisationApi\Service\AuthorisedExaminerPrincipalService;
use DvsaEventApi\Service\EventService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use OrganisationApi\Service\Validator\AuthorisedExaminerPrincipalValidator;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Constants\EventDescription;

/**
 * Unit tests for AuthorisedExaminerPrincipalService.
 */
class AuthorisedExaminerPrincipalServiceTest extends AbstractServiceTestCase
{
    const AUTH_FOR_AE_ID = 1;
    const AE_ID = 1;
    const AE_NAME = 'AEname';
    const AE_NUMBER = '1333';

    /** @var AuthorisedExaminerPrincipalService */
    private $authorisedExaminerPrincipalService;
    /** @var OrganisationRepository */
    private $organisationRepository;
    /** @var AuthorisedExaminerPrincipalRepository */
    private $authorisedExaminerPrincipalRepository;
    /** @var Organisation */
    private $authorisedExaminer;
    /** @var MotIdentityProviderInterface */
    private $identityProvider;
    /** @var MotAuthorisationServiceInterface */
    private $authorisationService;
    /** @var EventService */
    private $eventService;

    public function setUp()
    {
        $this->authorisedExaminer = $this->buildAuthorisedExaminer();

        $this->organisationRepository = XMock::of(OrganisationRepository::class);
        $this
            ->organisationRepository
            ->expects($this->any())
            ->method('getAuthorisedExaminer')
            ->willReturn($this->authorisedExaminer);

        $identity = XMock::of(MotIdentityInterface::class);
        $identity
            ->expects($this->any())
            ->method('getUsername')
            ->willReturn('johnRambo');

        $this->authorisedExaminerPrincipalRepository = XMock::of(AuthorisedExaminerPrincipalRepository::class);
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $this
            ->identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        /* @var AuthorisationService $authorisationService */
        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->eventService = XMock::of(EventService::class);

        $this->authorisedExaminerPrincipalService = new AuthorisedExaminerPrincipalService(
            $this->organisationRepository,
            $this->authorisationService,
            $this->eventService,
            $this->authorisedExaminerPrincipalRepository,
            $this->identityProvider,
            new AuthorisedExaminerPrincipalValidator()
        );
    }

    public function testGetAuthorisedExaminerPrincipalsReturnsArrayOfAeps()
    {
        $this
            ->authorisedExaminerPrincipalRepository
            ->expects($this->any())
            ->method('findAllByAuthForAe')
            ->willReturn([$this->createAep(1), $this->createAep(2)]);

        $aeps = $this->authorisedExaminerPrincipalService->getForAuthorisedExaminer(self::AE_ID);

        $this->assertCount(2, $aeps);
    }

    public function testCreateForAuthorisedExaminerReturnsAepId()
    {
        $aepId = 23;

        $data = [
            AddressLine1Input::FIELD => 'address 1',
            AddressLine2Input::FIELD => 'address 2',
            AddressLine3Input::FIELD => 'address 3',
            PostcodeInput::FIELD => 'postcode',
            FirstNameInput::FIELD => 'first name',
            MiddleNameInput::FIELD => 'middle name',
            FamilyNameInput::FIELD => 'family name',
            DateOfBirthInput::FIELD => '2001-12-12',
            TownInput::FIELD => 'town',
            CountryInput::FIELD => 'country',
        ];

        $this
            ->eventService
            ->expects($eventServiceSpy = $this->once())
            ->method('addOrganisationEvent');

        $this
            ->authorisedExaminerPrincipalRepository
            ->expects($aepRepositorySpy = $this->once())
            ->method('persist')
            ->willReturnCallback(function (AuthorisedExaminerPrincipal $aep) use ($aepId) {
                $aep->setId($aepId);

                return $aep;
            });

        $response = $this->authorisedExaminerPrincipalService->createForAuthorisedExaminer(self::AE_ID, $data);

        $expected = ['authorisedExaminerPrincipalId' => $aepId];

        $this->assertEquals($expected, $response);

        $eventDescription = $this->getParamFromSpy($eventServiceSpy, 2);
        /** @var $aep AuthorisedExaminerPrincipal */
        $aep = $this->getParamFromSpy($aepRepositorySpy, 0);

        $expectedDescription = $description = sprintf(
            EventDescription::AEP_ADDED_TO_AE,
            $aep->getDisplayName(),
            DateTimeDisplayFormat::date($aep->getDateOfBirth()),
            self::AE_NUMBER,
            self::AE_NAME,
            $this->identityProvider->getIdentity()->getUsername(),
            DateTimeDisplayFormat::dateTime(new \DateTime())
        );

        $this->assertEquals($expectedDescription, $eventDescription);
    }

    public function testDeletePrincipalForAuthorisedExaminer()
    {
        $aepId = 23;
        $aep = $this->createAep($aepId);

        $this
            ->eventService
            ->expects($eventServiceSpy = $this->once())
            ->method('addOrganisationEvent');

        $this
            ->authorisedExaminerPrincipalRepository
            ->expects($aepRepositorySpy = $this->once())
            ->method('findByIdAndAuthForAe')
            ->willReturn($aep);

        $this->authorisedExaminerPrincipalService->deletePrincipalForAuthorisedExaminer(self::AE_ID, $aepId);

        /** @var $aep AuthorisedExaminerPrincipal */
        $eventDescription = $this->getParamFromSpy($eventServiceSpy, 2);

        $expectedDescription = sprintf(
            EventDescription::AEP_REMOVED_TO_AE,
            $aep->getDisplayName(),
            DateTimeDisplayFormat::date($aep->getDateOfBirth()),
            self::AE_NUMBER,
            self::AE_NAME,
            $this->identityProvider->getIdentity()->getUsername(),
            DateTimeDisplayFormat::dateTime(new \DateTime())
        );

        $this->assertEquals($expectedDescription, $eventDescription);
    }

    private function buildAuthorisedExaminer()
    {
        $address = new Address();

        $contactDetail = new ContactDetail();
        $contactDetail->setAddress($address);

        $personalContactType = new \DvsaEntities\Entity\PersonContactType();
        $personalContactType->setName(PersonContactType::PERSONAL);

        $workContactType = new \DvsaEntities\Entity\PersonContactType();
        $workContactType->setName(PersonContactType::WORK);

        $AuthorisationForAuthorisedExaminer = new AuthorisationForAuthorisedExaminer();
        $AuthorisationForAuthorisedExaminer->setId(self::AUTH_FOR_AE_ID);
        $AuthorisationForAuthorisedExaminer->setNumber(self::AE_NUMBER);

        $organisation = new Organisation();
        $organisation->setAuthorisedExaminer($AuthorisationForAuthorisedExaminer);
        $organisation->setId(self::AE_ID);
        $organisation->setName(self::AE_NAME);

        $AuthorisationForAuthorisedExaminer->setOrganisation($organisation);

        return $organisation;
    }

    private function createAep($id, array $data = [])
    {
        $address = new Address();
        $address
            ->setAddressLine1(ArrayUtils::tryGet($data, AddressLine1Input::FIELD, 'address line 1'))
            ->setAddressLine2(ArrayUtils::tryGet($data, AddressLine2Input::FIELD, 'address line 2'))
            ->setAddressLine3(ArrayUtils::tryGet($data, AddressLine3Input::FIELD, 'address line 3'))
            ->setPostcode(ArrayUtils::tryGet($data, PostcodeInput::FIELD, 'postcode'))
            ->setTown(ArrayUtils::tryGet($data, PostcodeInput::FIELD, 'toen'))
            ->setCountry(ArrayUtils::tryGet($data, CountryInput::FIELD, 'country'))
            ;

        $contactDetails = new ContactDetail();
        $contactDetails->setAddress($address);

        $aep = new AuthorisedExaminerPrincipal();
        $aep
            ->setId($id)
            ->setFirstName(ArrayUtils::tryGet($data, FirstNameInput::FIELD, 'first name'))
            ->setFirstName(ArrayUtils::tryGet($data, MiddleNameInput::FIELD, 'middle name'))
            ->setFirstName(ArrayUtils::tryGet($data, FamilyNameInput::FIELD, 'family name'))
            ->setDateOfBirth(new \DateTime(ArrayUtils::tryGet($data, DateOfBirthInput::FIELD, '2002-11-11')))
            ->setContactDetails($contactDetails)
        ;

        return $aep;
    }

    private function getParamFromSpy($spy, $paramIndex)
    {
        $spyInvocations = $spy->getInvocations();
        $lastInvocation = end($spyInvocations);

        return $lastInvocation->parameters[$paramIndex];
    }
}
