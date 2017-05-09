<?php

namespace SiteApiTest\Service\Validator;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Dto\Site\EnforcementSiteAssessmentDto;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\SiteRepository;
use SiteApi\Service\Validator\EnforcementSiteAssessmentValidator;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class EnforcementSiteAssessmentValidatorTest extends AbstractServiceTestCase
{
    use TestCasePermissionTrait;

    const SITE_ID = 1;
    const AE_ORGANIZATION_ID = 2;
    const TESTER_ID = 3;
    const EXAMINER_ID = 4;
    const AE_REPRESENTATIVE_ID = 5;
    const TESTER_NAME = 'Tester';
    const TESTER_USERNAME = 'tester';
    const EXAMINER_NAME = 'Examiner';
    const EXAMINER_USERNAME = 'examiner';
    const AE_REPRESENTATIVE_NAME = 'AE Representative';
    const AE_REPRESENTATIVE_USERNAME = 'ae_representative';
    const AE_REPRESENTATIVE_ROLE = 'AE Rep';
    const DATE_OF_ASSESSMENT = '2015-01-01';

    /** @var EnforcementSiteAssessmentDto */
    private $dto;
    /** @var EnforcementSiteAssessmentValidator */
    private $validator;
    /** @var EntityManager|MockObj */
    private $entityManager;

    private $entityManagerIndex;
    private $personRepositoryIndex;
    private $siteRepositoryIndex;

    private $personRepositoryMock;
    private $siteRepositoryMock;
    private $config;

    protected function setUp()
    {
        parent::setUp();

        $this->entityManager = XMock::of(EntityManager::class);
        $this->mockConfig();

        $this->dto = new EnforcementSiteAssessmentDto();

        // Mocking helpers
        $this->entityManagerIndex = 0;
        $this->personRepositoryIndex = 0;
        $this->siteRepositoryIndex = 0;

        $this->personRepositoryMock = XMock::of(PersonRepository::class);
        $this->siteRepositoryMock = XMock::of(SiteRepository::class);

        $this->createValidator();
    }

    public function testValidationWithCorrectFullData()
    {
        $dto = $this->createDto(0, true);

        $this->prepareAllMocks();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    public function testValidationWithCorrectDateWithoutExaminersId()
    {
        $dto = $this->createDto(0, false);

        $this->prepareMocksWithoutExaminer();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    public function testValidationWithCorrectDataWithoutAeRepresentativeId()
    {
        $dto = $this->createDto(0, true)
                ->setAeRepresentativesUserId(null)
        ;

        $this->prepareMocksWithoutAeRepresentative();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testValidationWithInvalidDataNoSiteId()
    {
        $dto = $this->createDto(0, true)
            ->setSiteId(null);

        $this->prepareMocksWithoutSite();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testValidationWithInvalidDataInvalidSiteId()
    {
        $dto = $this->createDto(0, true)
            ->setSiteId('NaN')
        ;

        $this->prepareMocksWithoutSite();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testValidationWithInvalidDataInvalidNoSiteFound()
    {
        $dto = $this->createDto(0, true);

        $this->prepareAllMocksReturnNoSite();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testValidationWithInvalidDataNoTesterId()
    {
        $dto = $this->createDto(0, true)
            ->setTesterUserId(null);

        $this->prepareMocksWithoutTester();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testValidationWithInvalidDataTesterNotFound()
    {
        $dto = $this->createDto(0, true);

        $this->prepareAllMocksReturnNoTester();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testValidationWithInvalidDataExaminerNotFound()
    {
        $dto = $this->createDto(0, true);

        $this->prepareAllMocksReturnNoExaminer();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testValidationWithInvalidDataExaminerNotProvided()
    {
        $dto = $this->createDto(0, true)
            ->setDvsaExaminersUserId(null)
        ;

        $this->prepareMocksWithoutExaminer();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testValidationWithInvalidDataAeRepresentativeNotFound()
    {
        $dto = $this->createDto(0, true);

        $this->prepareAllMocksReturnNoAeRepresentative();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testValidationWithInvalidAssessmentDateNotProvided()
    {
        $dto = $this->createDto(0, true)
            ->setDateOfAssessment('');

        $this->prepareAllMocks();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testValidationWithInvalidFutureAssessmentDateProvided()
    {
        $dto = $this->createDto(0, true)
            ->setDateOfAssessment('2030-01-01');

        $this->prepareAllMocks();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testValidationWithInvalidWrongAssessmentDateProvided()
    {
        $dto = $this->createDto(0, true)
            ->setDateOfAssessment('2015-02-31');

        $this->prepareAllMocks();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    /**
     * @dataProvider invalidScoreDataProvider
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testValidationWithInvalidWrongAssessmentScoreProvided($score)
    {
        $dto = $this->createDto($score, true)
            ->setDateOfAssessment('2015-02-31');

        $this->prepareAllMocks();
        $this->createValidator();
        $this->validator->validate($dto);
    }

    public function invalidScoreDataProvider()
    {
        return [
            ['score' => -1],
            ['score' => 1000],
            ['score' => 'NaN'],
            ['score' => 'wrongScore'],
            ['score' => '23.22test234'],
        ];
    }

    /**
     * @param int  $score
     * @param bool $userIsNotAssessor
     *
     * @return EnforcementSiteAssessmentDto
     */
    private function createDto($score = 0, $userIsNotAssessor = false)
    {
        $dto = new EnforcementSiteAssessmentDto();
        $dto
            ->setSiteAssessmentScore($score)
            ->setDateOfAssessment(self::DATE_OF_ASSESSMENT)
            ->setSiteId(self::SITE_ID)
            ->setAeOrganisationId(self::AE_ORGANIZATION_ID)
            ->setUserIsNotAssessor($userIsNotAssessor)

            ->setDvsaExaminersFullName(self::EXAMINER_NAME)
            ->setDvsaExaminersUserId(self::EXAMINER_USERNAME)

            ->setAeRepresentativesRole(self::AE_REPRESENTATIVE_ROLE)
            ->setAeRepresentativesFullName(self::AE_REPRESENTATIVE_NAME)
            ->setAeRepresentativesUserId(self::AE_REPRESENTATIVE_USERNAME)

            ->setTesterFullName(self::TESTER_NAME)
            ->setTesterUserId(self::TESTER_USERNAME)
        ;

        return $dto;
    }

    // Mocking repositories - with or without specific repo mocked
    private function prepareAllMocks()
    {
        $this->mockSiteRepository();
        $this->mockPersonRepositoryForTester();
        $this->mockPersonRepositoryForExaminer();
        $this->mockPersonRepositoryForAeRepresentative();
    }

    private function prepareMocksWithoutTester()
    {
        $this->mockSiteRepository();
        $this->mockPersonRepositoryForExaminer();
        $this->mockPersonRepositoryForAeRepresentative();
    }

    private function prepareMocksWithoutExaminer()
    {
        $this->mockSiteRepository();
        $this->mockPersonRepositoryForTester();
        $this->mockPersonRepositoryForAeRepresentative();
    }

    private function prepareMocksWithoutAeRepresentative()
    {
        $this->mockSiteRepository();
        $this->mockPersonRepositoryForTester();
        $this->mockPersonRepositoryForExaminer();
    }

    private function prepareMocksWithoutSite()
    {
        $this->mockPersonRepositoryForTester();
        $this->mockPersonRepositoryForExaminer();
        $this->mockPersonRepositoryForAeRepresentative();
    }

    // Mocking repositories - entities found or not cases:
    private function prepareAllMocksReturnNoSite()
    {
        $this->mockSiteRepository(true);
        $this->mockPersonRepositoryForTester();
        $this->mockPersonRepositoryForExaminer();
        $this->mockPersonRepositoryForAeRepresentative();
    }

    private function prepareAllMocksReturnNoTester()
    {
        $this->mockSiteRepository();
        $this->mockPersonRepositoryForTester(true);
        $this->mockPersonRepositoryForExaminer();
        $this->mockPersonRepositoryForAeRepresentative();
    }

    private function prepareAllMocksReturnNoExaminer()
    {
        $this->mockSiteRepository();
        $this->mockPersonRepositoryForTester();
        $this->mockPersonRepositoryForExaminer(true);
        $this->mockPersonRepositoryForAeRepresentative();
    }

    private function prepareAllMocksReturnNoAeRepresentative()
    {
        $this->mockSiteRepository();
        $this->mockPersonRepositoryForTester();
        $this->mockPersonRepositoryForExaminer();
        $this->mockPersonRepositoryForAeRepresentative(true);
    }

    /**
     * @return Person
     */
    private function createTesterPersonEntity()
    {
        return (new Person())
            ->setId(self::TESTER_ID)
            ->setFirstName(self::TESTER_NAME)
            ->setUsername(self::TESTER_USERNAME)
        ;
    }

    /**
     * @return Person
     */
    private function createAeRepresentativePersonEntity()
    {
        return (new Person())
            ->setId(self::AE_REPRESENTATIVE_ID)
            ->setFirstName(self::AE_REPRESENTATIVE_NAME)
            ->setUsername(self::AE_REPRESENTATIVE_USERNAME)
        ;
    }

    /**
     * @return Person
     */
    private function createExaminerPersonEntity()
    {
        return (new Person())
            ->setId(self::EXAMINER_ID)
            ->setFirstName(self::EXAMINER_NAME)
            ->setUsername(self::EXAMINER_USERNAME)
        ;
    }

    /**
     * @return Site
     */
    private function createSiteEntity()
    {
        return (new Site())
            ->setId(self::SITE_ID)
            ->setOrganisation(
                (new Organisation())
                    ->setId(self::AE_ORGANIZATION_ID)
            )
        ;
    }

    private function mockSiteRepository($returnNull = false)
    {
        $this->mockMethod(
            $this->siteRepositoryMock,
            'findOneBy',
            $this->at($this->siteRepositoryIndex++),
            $returnNull ? null : $this->createSiteEntity(),
            [['id' => self::SITE_ID]]
        );

        $this->mockMethod(
            $this->entityManager,
            'getRepository',
            $this->at($this->entityManagerIndex++),
            $this->siteRepositoryMock,
            [Site::class]
        );
    }

    /**
     * @return EnforcementSiteAssessmentValidator
     */
    protected function createValidator()
    {
        $this->validator = new EnforcementSiteAssessmentValidator(
            $this->config,
            $this->entityManager
        );

        return $this->validator;
    }

    private function mockPersonRepositoryForTester($returnNull = false)
    {
        $this->mockMethod(
            $this->personRepositoryMock,
            'findOneBy',
            $this->at($this->personRepositoryIndex++),
            $returnNull ? null : $this->createTesterPersonEntity(),
            [['username' => self::TESTER_USERNAME]]
        );

        $this->mockMethod(
            $this->entityManager,
            'getRepository',
            $this->at($this->entityManagerIndex++),
            $this->personRepositoryMock,
            [Person::class]
        );
    }

    private function mockPersonRepositoryForExaminer($returnNull = false)
    {
        $this->mockMethod(
            $this->personRepositoryMock,
            'findOneBy',
            $this->at($this->personRepositoryIndex++),
            $returnNull ? null : $this->createExaminerPersonEntity(),
            [['username' => self::EXAMINER_USERNAME]]
        );

        $this->mockMethod(
            $this->entityManager,
            'getRepository',
            $this->at($this->entityManagerIndex++),
            $this->personRepositoryMock,
            [Person::class]
        );
    }

    private function mockPersonRepositoryForAeRepresentative($returnNull = false)
    {
        $this->mockMethod(
            $this->personRepositoryMock,
            'findOneBy',
            $this->at($this->personRepositoryIndex++),
            $returnNull ? null : $this->createAeRepresentativePersonEntity(),
            [['username' => self::AE_REPRESENTATIVE_USERNAME]]
        );
        $this->mockMethod(
            $this->entityManager,
            'getRepository',
            $this->at($this->entityManagerIndex++),
            $this->personRepositoryMock,
            [Person::class]
        );
    }

    protected function mockConfig()
    {
        $this->config = XMock::of(MotConfig::class);
        $this->mockMethod(
            $this->config,
            'get',
            $this->at(0),
            0,
            ['site_assessment', 'green', 'start']
        );
        $this->mockMethod(
            $this->config,
            'get',
            $this->at(1),
            999.99,
            ['site_assessment', 'red', 'end']
        );
    }
}
