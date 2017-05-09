<?php

namespace OrganisationTest\Controller;

use Core\Routing\AeRoutes;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaClient\Mapper\AuthorisedExaminerPrincipalMapper;
use DvsaClient\Mapper\OrganisationMapper;
use DvsaClient\Mapper\PersonMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Dto\AuthorisedExaminerPrincipal\AuthorisedExaminerPrincipalDto;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Organisation\Controller\AuthorisedExaminerPrincipalController;
use Organisation\UpdateAeProperty\UpdateAePropertyProcessBuilder;
use Organisation\ViewModel\AuthorisedExaminer\AeRemovePrincipalViewModel;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\View\Model\ViewModel;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;
use Organisation\UpdateAeProperty\UpdateAePropertyReviewAction;
use Zend\View\Helper\Url;

/**
 * Class AuthorisedExaminerPrincipalControllerTest.
 */
class AuthorisedExaminerPrincipalControllerTest extends AbstractFrontendControllerTestCase
{
    const AE_ID = 1;
    const LINK = 'http://link';

    /**
     * @var MotFrontendAuthorisationServiceInterface|MockObj
     */
    private $mockAuth;
    /**
     * @var MapperFactory|MockObj
     */
    private $mockMapperFactory;
    /**
     * @var PersonMapper|MockObj
     */
    private $mockPersonMapper;
    /**
     * @var AuthorisedExaminerPrincipalMapper|MockObj
     */
    private $mockAEPrincipalMapper;
    /**
     * @var OrganisationMapper|MockObj
     */
    private $mockOrgMapper;
    private $url;

    public function setUp()
    {
        $url = XMock::of(Url::class);
        $url
            ->expects($this->any())
            ->method('__invoke')
            ->willReturn(self::LINK);

        $this->url = $url;

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $this->setServiceManager($serviceManager);

        $this->mockAuth = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $this->mockMapperFactory = $this->getMapperFactory();

        $this->setController(new AuthorisedExaminerPrincipalController(
            $this->mockAuth,
            $this->mockMapperFactory,
            XMock::of(UpdateAePropertyAction::class),
            XMock::of(UpdateAePropertyReviewAction::class),
            XMock::of(UpdateAePropertyProcessBuilder::class)
    )
        );

        $this->getController()->setServiceLocator($this->serviceManager);

        parent::setUp();
    }

    /**
     * @dataProvider dataProviderTestActionsResult
     */
    public function testActionsResult($method, $action, $params, $mocks, $expect)
    {
        $result = null;

        //  mocking methods
        if ($mocks !== null) {
            foreach ($mocks as $mock) {
                $invocation = (isset($mock['call']) ? $mock['call'] : $this->once());
                $mockParams = (isset($mock['params']) ? $mock['params'] : null);

                $this->mockMethod($this->{$mock['class']}, $mock['method'], $invocation, $mock['result'], $mockParams);
            }
        }

        // logical block :: call
        $result = $this->getResultForAction2(
            $method,
            $action,
            ArrayUtils::tryGet($params, 'route'),
            ArrayUtils::tryGet($params, 'get')
        );

        // logical block :: check
        if (!empty($expect['viewModel'])) {
            $this->assertEquals($expect['viewModel'], $result);
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }

        if (!empty($expect['aepViewModel'])) {
            $model = $result->getVariable('viewModel');
            $this->assertEquals($expect['aepViewModel'], $model);
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }

        if (!empty($expect['url'])) {
            $this->assertRedirectLocation2($expect['url']);
        }
    }

    public function dataProviderTestActionsResult()
    {
        return [

            // remove Confirmation has access
            [
                'method' => 'get',
                'action' => 'removeConfirmation',
                'params' => [
                    'route' => [
                        'id' => self::AE_ID,
                        'principalId' => self::AE_ID,
                    ],
                ],
                'mocks' => [],
                'expect' => [
                    'aepViewModel' => $this->getAeRemovePrincipalModel(),
                ],
            ],
            // remove Confirmation post
            [
                'method' => 'post',
                'action' => 'removeConfirmation',
                'params' => [
                    'route' => [
                        'id' => self::AE_ID,
                        'principalId' => self::AE_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockAEPrincipalMapper',
                        'method' => 'removePrincipalsForOrganisation',
                        'params' => [self::AE_ID, self::AE_ID],
                        'result' => self::AE_ID,
                    ],
                ],
                'expect' => [
                    'url' => AuthorisedExaminerUrlBuilderWeb::of(self::AE_ID),
                ],
            ],
        ];
    }

    private function getMapperFactory()
    {
        $mapperFactory = XMock::of(MapperFactory::class);

        $this->mockPersonMapper = XMock::of(PersonMapper::class);
        $this->mockAEPrincipalMapper = XMock::of(AuthorisedExaminerPrincipalMapper::class);
        $this->mockOrgMapper = XMock::of(OrganisationMapper::class);

        $this->mockOrgMapper->expects($this->any())
            ->method('getAuthorisedExaminer')
            ->with(self::AE_ID)
            ->willReturn($this->getOrganisation());

        $this->mockPersonMapper->expects($this->any())
            ->method('getById')
            ->with(self::AE_ID)
            ->willReturn($this->getPerson());

        $this->mockAEPrincipalMapper->expects($this->any())
            ->method('getByIdentifier')
            ->with(self::AE_ID)
            ->willReturn($this->getAEP());

        $map = [
            [MapperFactory::PERSON, $this->mockPersonMapper],
            [MapperFactory::AUTHORISED_EXAMINER_PRINCIPAL, $this->mockAEPrincipalMapper],
            [MapperFactory::ORGANISATION, $this->mockOrgMapper],
        ];

        $mapperFactory->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mapperFactory;
    }

    private function getOrganisation()
    {
        $orgDto = new OrganisationDto();
        $orgDto->setId(self::AE_ID);

        $orgContactDto = new OrganisationContactDto();
        $orgContactDto->setType(OrganisationContactTypeCode::REGISTERED_COMPANY);

        $orgAddressDto = new AddressDto();
        $orgAddressDto->setAddressLine1('test')
            ->setAddressLine2('test')
            ->setAddressLine3('test')
            ->setPostcode('test')
            ->setTown('test');

        $orgContactDto->setAddress($orgAddressDto);

        $orgDto->setContacts([$orgContactDto]);
        $orgDto->setAuthorisedExaminerAuthorisation(new AuthorisedExaminerAuthorisationDto());

        return $orgDto;
    }

    private function getPerson()
    {
        $dto = new PersonDto();
        $dto->setId(self::AE_ID);

        return $dto;
    }

    private function getAEP()
    {
        $dto = new AuthorisedExaminerPrincipalDto();
        $dto->setId(self::AE_ID)
            ->setDisplayName('display name test')
            ->setDateOfBirth('2010-11-12');

        $orgContactDto = new ContactDto();
        $orgContactDto->setType(OrganisationContactTypeCode::REGISTERED_COMPANY);

        $orgAddressDto = new AddressDto();
        $orgAddressDto->setAddressLine1('test')
            ->setAddressLine2('test')
            ->setAddressLine3('test')
            ->setPostcode('test')
            ->setTown('test');

        $orgContactDto->setAddress($orgAddressDto);

        $dto->setContactDetails($orgContactDto);

        return $dto;
    }

    private function getAeRemovePrincipalModel()
    {
        $newAEP = $this->getAEP();
        $aeRemovePrincipalModel = new AeRemovePrincipalViewModel();
        $aeRemovePrincipalModel->setAuthorisedExaminer($this->getOrganisation()->getName())
            ->setPrincipalName($newAEP->getDisplayName())
            ->setDateOfBirth($newAEP->displayDateOfBirth())
            ->setAddress($newAEP->getContactDetails()->getAddress())
            ->setCancelUrl(AuthorisedExaminerUrlBuilderWeb::of(self::AE_ID));
//            ->setCancelUrl(AeRoutes::of($this->url)->ae($this->getOrganisation()->getId()));

        return $aeRemovePrincipalModel;
    }
}
