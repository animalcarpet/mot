<?php

namespace SiteApiTest\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\Assertion\UpdateVtsAssertion;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Utility\Hydrator;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\AddressService;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaCommonApi\Service\Validator\AddressValidator;
use DvsaCommonApi\Service\Validator\ContactDetailsValidator;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Entity\PhoneContactType;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteContact;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Mapper\AddressMapper;
use DvsaEntities\Repository\PhoneContactTypeRepository;
use DvsaEntities\Repository\SiteContactRepository;
use DvsaEntities\Repository\SiteContactTypeRepository;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use SiteApi\Service\SiteContactService;

/**
 * Class SiteContactServiceTest.
 */
class SiteContactServiceTest extends AbstractServiceTestCase
{
    const SITE_ID = 99999;
    const CONTACT_ID = 88888;

    /** @var SiteContactService */
    private $siteContactSrv;
    /** @var SiteContactRepository|MockObj */
    private $mockSiteContactRepo;
    /** @var SiteContactTypeRepository|MockObj */
    private $mockSiteContactTypeRepo;
    /** @var AuthorisationServiceInterface|MockObj */
    private $mockAuthService;
    /** @var UpdateVtsAssertion */
    private $updateVtsAssertion;
    /** @var UpdateVtsAssertion */
    private $mockUpdateVtsAssertion;

    public function setup()
    {
        $this->mockSiteContactRepo = XMock::of(SiteContactRepository::class);
        $this->mockSiteContactTypeRepo = XMock::of(SiteContactTypeRepository::class);
        $this->mockAuthService = $this->getMockAuthorizationService();

        $this->updateVtsAssertion = new UpdateVtsAssertion($this->mockAuthService);
        $this->mockUpdateVtsAssertion = Xmock::of(UpdateVtsAssertion::class);

        $this->siteContactSrv = new SiteContactService(
            $this->getMockEntityManager(),
            $this->createContactDetailsService(),
            $this->createXssFilterMock(),
            $this->updateVtsAssertion
        );

        XMock::mockClassField($this->siteContactSrv, 'siteContactRepo', $this->mockSiteContactRepo);
        XMock::mockClassField($this->siteContactSrv, 'siteContactTypeRepo', $this->mockSiteContactTypeRepo);
    }

    /**
     * @return ContactDetailsService
     */
    private function createContactDetailsService()
    {
        $entityManager = $this->getMockEntityManager();

        /** @var PhoneContactTypeRepository|MockObj $phoneContactTypeRepository */
        $phoneContactTypeRepository = XMock::of(PhoneContactTypeRepository::class);
        $phoneContactTypeRepository
            ->expects($this->any())->method('getByCode')
            ->willReturn(new PhoneContactType());

        $addressService = new AddressService(
            $entityManager,
            new Hydrator(),
            new AddressValidator(),
            new AddressMapper()
        );

        $contactDetailsService = new ContactDetailsService(
            $entityManager,
            $addressService,
            $phoneContactTypeRepository,
            new ContactDetailsValidator(new AddressValidator())
        );

        return $contactDetailsService;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createXssFilterMock()
    {
        $xssFilterMock = $this
            ->getMockBuilder(XssFilter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $xssFilterMock
            ->method('filter')
            ->will($this->returnArgument(0));

        return $xssFilterMock;
    }

    private function createMockSiteContactServiceWithMockedUpdateVtsAssertion()
    {
        $siteContactService = new SiteContactService(
            $this->getMockEntityManager(),
            $this->createContactDetailsService(),
            $this->createXssFilterMock(),
            $this->mockUpdateVtsAssertion
        );
        XMock::mockClassField($siteContactService, 'siteContactRepo', $this->mockSiteContactRepo);
        XMock::mockClassField($siteContactService, 'siteContactTypeRepo', $this->mockSiteContactTypeRepo);

        return $siteContactService;
    }

    private function createSiteContact()
    {
        $site = new Site();

        $contactDetails = new ContactDetail();
        $contactDetails->addEmail(
            (new Email())
                ->setIsPrimary(true)
        );
        $contactDetails->addPhone(
            (new Phone())
                ->setIsPrimary(true)
                ->setContactType(
                    (new PhoneContactType())
                        ->setCode(PhoneContactTypeCode::BUSINESS)
                )
        );

        $siteContactType = new SiteContactType();
        $siteContactType->setCode(SiteContactTypeCode::BUSINESS);

        return new SiteContact($contactDetails, $siteContactType, $site);
    }

    /**
     * @dataProvider dataProvideTestPatchContactFromJson
     */
    public function testPatchContactFromJson($data, $expectedAsserts = null, $expectedException = null)
    {
        $this->siteContactSrv = $this->createMockSiteContactServiceWithMockedUpdateVtsAssertion();
        $siteContact = $this->createSiteContact();
        $this->mockSiteContactRepo->expects($this->any())->method('getHydratedByTypeCode')
            ->willReturn($siteContact);

        if (!empty($expectedException)) {
            $this->setExpectedException($expectedException);
        } else {
            $siteContactRepoSpy = new MethodSpy($this->mockSiteContactRepo, 'save');
        }

        if (is_array($expectedAsserts)) {
            $spies = [];
            foreach ($expectedAsserts as $expectedAssert) {
                $spies[] = new MethodSpy($this->mockUpdateVtsAssertion, $expectedAssert);
            }
        }

        $this->siteContactSrv->patchContactFromJson(1, $data);

        if (isset($spies) && is_array($spies)) {
            /** @var MethodSpy $spy */
            foreach ($spies as $spy) {
                $this->assertEquals(1, $spy->invocationCount());
            }
        }

        if (isset($siteContactRepoSpy)) {
            /** @var ContactDetail $savedContact */
            $savedContact = $siteContactRepoSpy->paramsForLastInvocation()[0];

            if (array_key_exists('email', $data)) {
                $this->assertEquals($data['email'], $savedContact->getPrimaryEmail()->getEmail());
                $this->assertCount(1, $savedContact->getEmails());
            }

            if (array_key_exists('phone', $data)) {
                $this->assertEquals($data['phone'], $savedContact->getPrimaryPhone()->getNumber());
                $this->assertCount(1, $savedContact->getPhones());
            }

            if (array_key_exists('address', $data)) {
                $address = $data['address'];
                $addressFields = [
                    'addressLine1' => 'getAddressLine1',
                    'addressLine2' => 'getAddressLine2',
                    'addressLine3' => 'getAddressLine3',
                    'postcode' => 'getPostcode',
                    'town' => 'getTown',
                ];

                foreach ($addressFields as $field => $getter) {
                    if (array_key_exists($field, $address)) {
                        $this->assertEquals($address[$field], $savedContact->getAddress()->$getter());
                    }
                }
            }
        }
    }

    public function dataProvideTestPatchContactFromJson()
    {
        return [
            //Check if correct asserts are runned depending on data
            [
                'data' => [
                    'email' => 'sitecontactservicetest@'.EmailAddressValidator::TEST_DOMAIN,
                ],
                'expectedAsserts' => [
                    'assertUpdateEmail',
                ],
            ],
            [
                'data' => [
                    'phone' => '100200300',
                ],
                'expectedAsserts' => [
                    'assertUpdatePhone',
                ],
            ],
            [
                'data' => [
                    'address' => [
                        'town' => 'New Town',
                        'postcode' => '10-200',
                        'addressLine1' => 'Streeet',
                    ],
                ],
                'expectedAsserts' => [
                    'assertUpdateAddress',
                ],
            ],
            //Check asserts runned for full dataset
            [
                'data' => [
                    'email' => 'sitecontactservicetest@'.EmailAddressValidator::TEST_DOMAIN,
                    'phone' => '100200300',
                    'address' => [
                        'town' => 'New Town',
                        'postcode' => '10-200',
                        'addressLine1' => 'Streeet',
                    ],
                ],
                'expectedAsserts' => [
                    'assertUpdateEmail',
                    'assertUpdatePhone',
                    'assertUpdateAddress',
                ],
            ],
            //Check if exception is thrown if address is incomplete
            [
                'data' => [
                    'address' => [
                        'town' => 'New Town',
                        'postcode' => '10-200',
                    ],
                ],
                'expectedAsserts' => [],
                'expectedException' => 'DvsaCommonApi\Service\Exception\RequiredFieldException',
            ],
        ];
    }
}
