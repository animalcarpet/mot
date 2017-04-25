<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModuleTest\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Dvsa\Mot\Api\RegistrationModule\Service\PersonCreator;
use Dvsa\Mot\Api\RegistrationModule\Service\PersonSecurityAnswerRecorder;
use Dvsa\Mot\Api\RegistrationModule\Service\UsernameGenerator;
use Dvsa\Mot\Api\RegistrationModule\Service\ValidatorKeyConverter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionsInputFilter;
use DvsaCommonApiTest\Transaction\TestTransactionExecutor;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Entity\Gender;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonSecurityAnswer;
use DvsaEntities\Entity\Title;
use DvsaEntities\Repository\AuthenticationMethodRepository;
use DvsaEntities\Repository\GenderRepository;
use DvsaEntities\Repository\TitleRepository;

/**
 * Class PersonCreatorTest.
 */
class PersonCreatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var PersonCreator $subject */
    private $subject;

    public function setUp()
    {
        /** @var UsernameGenerator $mockUsernameGenerator */
        $mockUsernameGenerator = XMock::of(UsernameGenerator::class);

        /** @var EntityManager $mockEntityManager */
        $mockEntityManager = XMock::of(EntityManager::class);
        $mockEntityManager
            ->expects($this->any())
            ->method('getRepository')
            ->willReturnCallback(
                function ($entity) {
                    switch ($entity) {
                        case AuthenticationMethod::class:
                            return XMock::of(AuthenticationMethodRepository::class);
                            break;
                        case Title::class:
                            return XMock::of(TitleRepository::class);
                            break;
                        case Gender::class:
                            return XMock::of(GenderRepository::class);
                            break;
                    }

                    return false;
                }
            );

        $mockConnection = XMock::of(Connection::class);
        $mockEntityManager
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($mockConnection);

        /** @var PersonSecurityAnswerRecorder $mockPersonSecurityAnswerRecorder */
        $mockPersonSecurityAnswerRecorder = XMock::of(PersonSecurityAnswerRecorder::class);
        $mockPersonSecurityAnswerRecorder->expects($this->any())
            ->method('create')
            ->willReturn(XMock::of(PersonSecurityAnswer::class));

        /* @var TitleRepository $mockTitleRepository */
        $authenticationMethodRepository = $mockEntityManager->getRepository(AuthenticationMethod::class);

        /** @var TitleRepository $mockTitleRepository */
        $mockTitleRepository = $mockEntityManager->getRepository(Title::class);

        /** @var GenderRepository $mockGenderRepository */
        $mockGenderRepository = $mockEntityManager->getRepository(Gender::class);

        $this->subject = new PersonCreator(
            $mockUsernameGenerator,
            $mockEntityManager,
            $authenticationMethodRepository,
            $mockTitleRepository,
            $mockGenderRepository,
            $mockPersonSecurityAnswerRecorder
        );

        $this->subject->setTransactionExecutor(new TestTransactionExecutor());
    }

    /**
     * @dataProvider dpStepDetails
     *
     * @param array $data
     */
    public function testCreate($data)
    {
        $this->assertInstanceOf(
            Person::class,
            $this->subject->create($data)
        );
    }

    /**
     * @return array
     */
    public function dpStepDetails()
    {
        return [
            [
                [
                    ValidatorKeyConverter::inputFilterToStep(DetailsInputFilter::class) => [
                        DetailsInputFilter::FIELD_FIRST_NAME  => 'x',
                        DetailsInputFilter::FIELD_MIDDLE_NAME => 'y',
                        DetailsInputFilter::FIELD_LAST_NAME   => 'o',
                        DetailsInputFilter::FIELD_DATE => [
                            DetailsInputFilter::FIELD_DAY => '01',
                            DetailsInputFilter::FIELD_MONTH => '02',
                            DetailsInputFilter::FIELD_YEAR => '1990',
                        ],
                    ],
                    ValidatorKeyConverter::inputFilterToStep(SecurityQuestionsInputFilter::class) => [
                        SecurityQuestionsInputFilter::FIELD_QUESTION_1 => 1,
                        SecurityQuestionsInputFilter::FIELD_ANSWER_1   => 'Something',
                        SecurityQuestionsInputFilter::FIELD_QUESTION_2 => 2,
                        SecurityQuestionsInputFilter::FIELD_ANSWER_2   => 'Something else',
                    ],
                    ValidatorKeyConverter::inputFilterToStep(PasswordInputFilter::class) => [
                        PasswordInputFilter::FIELD_PASSWORD => 'password',
                    ],
                ],
            ],
        ];
    }
}
