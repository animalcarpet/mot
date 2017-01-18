<?php

namespace DvsaMotApiTest\Controller;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonApiTest\Transaction\TestTransactionExecutor;
use DvsaCommonTest\TestUtils\ArgCapture;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Controller\ReplacementCertificateDraftController;
use DvsaMotApi\Dto\ReplacementCertificateDraftChangeDTO;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateService;
use DvsaMotApiTest\Factory\ReplacementCertificateObjectsFactory;
use Zend\Http\Header\Authorization;

/**
 * Class ReplacementCertificateDraftControllerTest
 */
class ReplacementCertificateDraftControllerTest extends AbstractMotApiControllerTestCase
{

    /** @var ReplacementCertificateService */
    private $replacementCertificateService;

    /** @var AuthorisationServiceInterface */
    private $authorisationService;

    /** @var CertificateCreationService */
    private $certificateCreationService;

    /** @var MotTestService */
    private $motTestService;

    protected function setUp()
    {
        $this->replacementCertificateService = XMock::of(ReplacementCertificateService::class);
        $this->authorisationService = XMock::of(AuthorisationServiceInterface::class);
        $this->certificateCreationService = XMock::of(CertificateCreationService::class);
        $this->motTestService = XMock::of(MotTestService::class);

        /** @var ReplacementCertificateDraftController $controller */
        $this->controller = new ReplacementCertificateDraftController(
            $this->replacementCertificateService,
            $this->authorisationService,
            $this->certificateCreationService,
            $this->motTestService
        );

        TestTransactionExecutor::inject($this->controller);
        parent::setUp();

        $this->mockValidAuthorization();
    }

    public function testCreate_givenMotTestIdAsInput_shouldReturnIdOfTheCreatedDraft()
    {
        // given
        $motTestNumber = 4;
        $createdDraft = ReplacementCertificateObjectsFactory::replacementCertificateDraft();
        $this->request->setMethod('post');
        $this->setJsonRequestContent(['motTestNumber' => $motTestNumber]);

        $this->mockMethod(
            $this->replacementCertificateService, 'createDraft', $this->any(), $createdDraft, [$motTestNumber]
        );

        // when
        $result = $this->getResultForAction(
            'post', null, ['motTestNumber' => $motTestNumber], null
        )->data;

        //then
        $this->assertEquals(
            ['id' => $createdDraft->getId()], $result, "Returned draft id is incorrect"
        );
    }

    public function testUpdate_givenDraftChangeAsInput_shouldCallUpdateService()
    {
        // given
        $draftChangeCapture = ArgCapture::create();
        $draftIdCapture = ArgCapture::create();
        $draftId = 4;
        $this->routeMatch->setParam('id', $draftId);
        $this->request->setMethod('put');
        $this->setJsonRequestContent(['primaryColour' => "Y"]);

        $this->mockMethod(
            $this->replacementCertificateService, 'updateDraft', $this->any(), null, [$draftIdCapture(), $draftChangeCapture()]
        );

        // when
        $result = $this->getResultForAction(
            'put', null, ['id' => $draftId], ['primaryColour' => "Y"]
        );

        // then
        $this->assertEquals($draftId, $draftIdCapture->get(), "Passed in draftId is not correct");
        $this->assertThat(
            $draftChangeCapture->get(), $this->isInstanceOf(ReplacementCertificateDraftChangeDTO::class),
            "Wrong parameter type is passed in to the service"
        );
    }

    public function testGet_givenDraftIdAsInput_shouldCallServiceMethod()
    {
        // given
        $returnedDraft = ReplacementCertificateObjectsFactory::replacementCertificateDraft();
        $draftIdCapture = ArgCapture::create();
        $draftId = 4;

        $this->mockMethod(
            $this->authorisationService, 'isGranted', $this->any(), true
        );

        $this->mockMethod(
            $this->replacementCertificateService, 'getDraft', $this->any(), $returnedDraft, [ $draftIdCapture() ]
        );

        // when
        $result = $this->getResultForAction(
            'get', null, ['id' => $draftId]
        )->data;

        // then
        $this->assertEquals($draftId, $draftIdCapture->get(), "Passed in draftId is not correct");
        $this->assertEquals(
            $returnedDraft->getPrimaryColour()->getCode(), $result['primaryColour']['code'],
            "Invalid data returned from the controller"
        );
    }

    public function testDiffAction_givenDraftIdAsInput_shouldReturnDraftDiff()
    {
        // given
        $returnedDraft = ReplacementCertificateObjectsFactory::replacementCertificateDraft();
        $returnedDraft->getPrimaryColour()->setCode("Y")->setId(1);
        $returnedDraft->getMotTest()->getPrimaryColour()->setCode("Z")->setId(2);
        $draftIdCapture = ArgCapture::create();
        $draftId = 4;

        $this->mockMethod(
            $this->replacementCertificateService, 'getDraft', $this->any(), $returnedDraft, [ $draftIdCapture() ]
        );

        // when
        $result = $this->getResultForAction(
            'get', 'diff', ['id' => $draftId]
        )->data;

        // then
        $this->assertEquals($draftId, $draftIdCapture->get(), "Passed in draftId is not correct");
        $this->assertEquals(
            $returnedDraft->getPrimaryColour()->getId(), in_array('primaryColour', $result),
            "Though primary colour different, it isnt included in the diff"
        );
    }

    public function testApplyAction_givenDraftIdAsInput_shouldCallApplyService()
    {
        // given
        $draftIdCapture = ArgCapture::create();
        $draftId = 4;
        $this->routeMatch->setParam('id', $draftId)
            ->setParam('action', 'apply');
        $this->request->setMethod('post');

        $mockEntity = $this->getMock(\stdClass::class, ['getNumber']);
        $mockEntity->expects($this->once())
            ->method('getNumber')
            ->will($this->returnValue(123));

        $this->mockMethod(
            $this->replacementCertificateService, 'applyDraft', $this->any(), $mockEntity, [ $draftIdCapture() ]
        );

        $motData = new MotTestDto();

        $this->mockMethod(
            $this->motTestService, 'getMotTestData', $this->any(), $motData, [ 123 ]
        );

        $this->mockMethod(
            $this->certificateCreationService, 'create', $this->any(), null, [ 123, $motData, AbstractRestfulControllerTestCase::MOCK_USER_ID ]
        );

        // when
        $result = $this->getResultForAction(
            'post', 'apply', ['id' => $draftId]
        )->data;

        // then
        $this->assertEquals($draftId, $draftIdCapture->get(), "Passed in draftId is not correct");
    }

}
