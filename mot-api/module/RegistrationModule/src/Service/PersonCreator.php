<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionFirstInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionSecondInputFilter;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Entity\Gender;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Title;
use DvsaEntities\Repository\AuthenticationMethodRepository;
use DvsaEntities\Repository\GenderRepository;
use DvsaEntities\Repository\TitleRepository;

/**
 * Class PersonCreator.
 */
class PersonCreator extends AbstractPersistableService
{
    const AUTH_METHOD_CODE = 'PIN';
    const TITLE_UNKNOWN_CODE = 'UNKN';
    const GENDER_UNKNOWN_CODE = 'UNKN';

    /**
     * @var UsernameGenerator
     */
    private $usernameGenerator;

    /**
     * @var AuthenticationMethodRepository
     */
    private $authenticationMethodRepository;

    /**
     * @var TitleRepository
     */
    private $titleRepository;

    /**
     * @var GenderRepository
     */
    private $genderRepository;

    /**
     * @var PersonSecurityAnswerRecorder
     */
    private $personSecurityAnswerRecorder;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var array
     */
    private $data;

    public function __construct(
        UsernameGenerator $usernameGenerator,
        EntityManager $entityManager,
        AuthenticationMethodRepository $authenticationMethodRepository,
        TitleRepository $titleRepository,
        GenderRepository $genderRepository,
        PersonSecurityAnswerRecorder $personSecurityAnswerRecorder
    ) {
        parent::__construct($entityManager);

        $this->usernameGenerator = $usernameGenerator;
        $this->authenticationMethodRepository = $authenticationMethodRepository;
        $this->titleRepository = $titleRepository;
        $this->genderRepository = $genderRepository;
        $this->personSecurityAnswerRecorder = $personSecurityAnswerRecorder;
    }

    /**
     * Create, record a person in the table and return its entity.
     *
     * @param array $data
     *
     * @return Person
     */
    public function create($data)
    {
        $this->data = $data;

        $this->person = new Person();

        $this->populateCompulsoryFields();
        $this->populateOptionalFields();
        $this->populateDefaultFields();

        $this->save($this->person);

        return $this->person;
    }

    /**
     * populated fields which application expect them to be supplied by the request.
     */
    private function populateCompulsoryFields()
    {
        $this->person
            ->setFirstName($this->data[$this->getDetailsStepName()][DetailsInputFilter::FIELD_FIRST_NAME])
            ->setFamilyName($this->data[$this->getDetailsStepName()][DetailsInputFilter::FIELD_LAST_NAME])
            ->addSecurityAnswer(
                $this->personSecurityAnswerRecorder->create(
                    $this->person,
                    $this->data[$this->getSecurityQuestionFirstStepName()][SecurityQuestionFirstInputFilter::FIELD_QUESTION],
                    $this->data[$this->getSecurityQuestionFirstStepName()][SecurityQuestionFirstInputFilter::FIELD_ANSWER]
                )
            )->addSecurityAnswer(
                $this->personSecurityAnswerRecorder->create(
                    $this->person,
                    $this->data[$this->getSecurityQuestionSecondStepName()][SecurityQuestionSecondInputFilter::FIELD_QUESTION],
                    $this->data[$this->getSecurityQuestionSecondStepName()][SecurityQuestionSecondInputFilter::FIELD_ANSWER]
                )
            );
        $date = new \DateTime($this->formatDateOfBirth());
        $this->person->setDateOfBirth($date);
    }

    /**
     * populated optional fields which might be supplied by the request and if not its safe to ignore them.
     */
    private function populateOptionalFields()
    {
        if (isset($this->data[$this->getDetailsStepName()][DetailsInputFilter::FIELD_MIDDLE_NAME])) {
            $this->person
                ->setMiddleName($this->data[$this->getDetailsStepName()][DetailsInputFilter::FIELD_MIDDLE_NAME]);
        }
    }

    /**
     * populated fields are not supplied by the request but we need to set them.
     */
    private function populateDefaultFields()
    {
        /** @var AuthenticationMethod $authenticationMethod */
        $authenticationMethod = $this->authenticationMethodRepository->findOneBy(['code' => self::AUTH_METHOD_CODE]);

        /** @var Title $title */
        $title = $this->titleRepository->findOneBy(['code' => self::TITLE_UNKNOWN_CODE]);

        /** @var Gender $gender */
        $gender = $this->genderRepository->findOneBy(['code' => self::GENDER_UNKNOWN_CODE]);

        $this->person
            ->setAccountClaimRequired(false)
            ->setPasswordChangeRequired(false)
            ->setAuthenticationMethod($authenticationMethod)
            ->setTitle($title)
            ->setGender($gender)
            ->setUsername(
                $this->getUsernameFromGenerator()
            );
    }

    /**Retrieve the username from the username generator
     * @return string
     */
    private function getUsernameFromGenerator()
    {
        return $this->usernameGenerator->generateUsername(
            $this->data[$this->getDetailsStepName()][DetailsInputFilter::FIELD_FIRST_NAME],
            $this->data[$this->getDetailsStepName()][DetailsInputFilter::FIELD_LAST_NAME],
            $this->data[$this->getPasswordStepName()][PasswordInputFilter::FIELD_PASSWORD]
        );
    }

    private function formatDateOfBirth()
    {
        $date = $this->data[$this->getDetailsStepName()][DetailsInputFilter::FIELD_DATE];
        $day = $date[DetailsInputFilter::FIELD_DAY];
        $month = $date[DetailsInputFilter::FIELD_MONTH];
        $year = $date[DetailsInputFilter::FIELD_YEAR];

        return implode('-', array_filter([$year, $month, $day]));
    }
}
