<?php

namespace Account\Service;

use Account\Controller\ClaimController;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaClient\MapperFactory;
use DvsaCommon\Obfuscate\ParamObfuscator;
use Zend\Http\Response;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Session\Container;

/**
 * Class ClaimAccountService
 *
 * @package Account\Service
 */
class ClaimAccountService
{
    const KEY_NAME_USER_ID = 'user_id';
    const KEY_NAME_IS_TESTER = 'is_tester';
    const KEY_NAME_USERNAME = 'username';
    const KEY_NAME_PIN = 'pin';

    /** @var \Zend\Session\Container */
    protected $sessionContainer;

    /** @var  \Core\Service\LazyMotFrontendAuthorisationService */
    private $authService;
    /** @var MapperFactory $mapper */
    private $mapper;
    /** @var  ParamObfuscator */
    private $obfuscator;
    /** @var  \Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity */
    private $identity;


    /**
     * @param MotFrontendAuthorisationServiceInterface $authService
     * @param MotFrontendIdentityInterface $identity
     * @param MapperFactory $mapper
     * @param ParamObfuscator $obfuscator
     */
    public function __construct(
        MotFrontendAuthorisationServiceInterface $authService,
        MotFrontendIdentityInterface $identity,
        MapperFactory $mapper,
        ParamObfuscator $obfuscator
    ) {
        $this->authService = $authService;
        $this->identity = $identity;
        $this->mapper = $mapper;
        $this->obfuscator = $obfuscator;
    }


    /**
     * @return Container Zend\Session\Container
     */
    public function getSession()
    {
        if (is_null($this->sessionContainer)) {
            $this->initSession();
        }

        return $this->sessionContainer;
    }

    private function initSession()
    {
        $this->sessionContainer = new Container(self::class);

        if (!$this->sessionContainer->offsetExists(self::KEY_NAME_PIN)) {
            $this->initSessionData();
        }
    }

    private function initSessionData()
    {
        $claimData = $this->fetchClaimData();

        $this
            ->saveOnSession(self::KEY_NAME_USER_ID, $this->identity->getUserId())
            ->saveOnSession(self::KEY_NAME_IS_TESTER, $this->authService->isTester())
            ->saveOnSession(self::KEY_NAME_USERNAME, $this->identity->getUsername())
            ->saveOnSession(self::KEY_NAME_PIN, $claimData->getPin());
    }

    public function saveOnSession($key, $value, $overwrite = true)
    {
        if ($overwrite
            || !$this->getSession()->offsetExists($key)
        ) {
            $this->getSession()->$key = $value;
        }
        return $this;
    }

    public function markClaimedSuccessfully()
    {
        $this->identity->setAccountClaimRequired(false);
        $this->identity->setPasswordChangeRequired(false);
    }

    public function getFromSession($key)
    {
        return $this->getSession()->$key;
    }

    public function sessionToArray()
    {
        return $this->getSession()->getArrayCopy();
    }

    /**
     * @param \Zend\Stdlib\Parameters $post
     */
    public function captureStep($post)
    {
        $submittingStep = $post['submitted_step'];
        $this->saveOnSession(
            $submittingStep,
            $post->getArrayCopy() + [
                'username' => $this->identity->getUsername()
            ]
        );
    }

    public function clearSession()
    {
        foreach ($this->getSession()->getIterator() as $k => $value) {
            if (is_null($value)) {
                $this->getSession()->$k = '';
            }

            unset($this->getSession()->$k);
        }
    }

    /**
     * @param string $step
     */
    public function isStepRecorded($step)
    {
        return ($this->getSession()->offsetExists($step));
    }

    /**
     * @return array
     */
    public function getSecurityQuestions()
    {
        $questionSet = $this->mapper->SecurityQuestion->fetchAllGroupedAndOrdered();

        return [
            'groupA' => $this->getQuestionForGroup($questionSet->getGroupOne()),
            'groupB' => $this->getQuestionForGroup($questionSet->getGroupTwo()),
        ];
    }

    private function getQuestionForGroup($questions)
    {
        $result = [];
        /** @var \DvsaCommon\Dto\Security\SecurityQuestionDto $question */
        foreach ($questions as $question) {
            $result[$question->getId()] = $question->getText();
        }

        return $result;
    }

    public function sendToApi($data)
    {
        $result = $this->mapper->Account->claimUpdate(
            $this->identity->getUserId(),
            $this->prepareDataForApi($data)
        );

        return ($result === true);
    }

    private function prepareDataForApi($data)
    {
        $step1Data = $data[ClaimController::STEP_1_NAME];
        $step2Data = $data[ClaimController::STEP_2_NAME];

        return [
            'personId'              => $data['user_id'],

            'password'              => $step1Data['password'],
            'passwordConfirmation'  => $step1Data['confirm_password'],

            'securityQuestionOneId' => $step2Data['question_a'],
            'securityAnswerOne'     => $step2Data['answer_a'],

            'securityQuestionTwoId' => $step2Data['question_b'],
            'securityAnswerTwo'     => $step2Data['answer_b'],
        ];
    }

    private function fetchClaimData()
    {
        return $this->mapper->Account->getClaimData($this->identity->getUserId());
    }
}
