<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Account\AbstractClass;

use Account\Service\SecurityQuestionService;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\Entity\Person;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\UrlBuilder\UrlBuilderWeb;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\View\Model\ViewModel;

/**
 * AbstractSecurityQuestion ViewModel.
 */
abstract class AbstractSecurityQuestionViewModel
{
    /**
     * @var \Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator
     */
    protected $personProfileUrlGenerator;

    /**
     * @var SecurityQuestionService
     */
    protected $service;

    /**
     * @param SecurityQuestionService   $service
     * @param PersonProfileUrlGenerator $personProfileUrlGenerator
     */
    public function __construct($service, PersonProfileUrlGenerator $personProfileUrlGenerator)
    {
        $this->service = $service;
        $this->personProfileUrlGenerator = $personProfileUrlGenerator;
    }

    /**
     * @param FlashMessenger $flashMessenger
     *
     * @return UrlBuilderWeb
     */
    abstract public function getNextPageLink(FlashMessenger $flashMessenger);

    /**
     * @return UrlBuilderWeb|string
     */
    abstract public function getCurrentLink();

    /**
     * @return SecurityQuestionDto
     */
    public function getQuestion()
    {
        return $this->service->getQuestion();
    }

    /**
     * @return int
     */
    public function getQuestionNumber()
    {
        return $this->service->getQuestionNumber();
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->service->getUserId();
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->service->getPerson();
    }

    /**
     * @return string
     */
    public function getSearchParams()
    {
        return $this->service->getSearchParams();
    }

    /**
     * @param int $questionNumber
     *
     * @return string
     */
    protected function generateSecurityQuestionsUrlForNewProfile($questionNumber)
    {
        return $this->personProfileUrlGenerator->fromPersonProfile('security-questions', [
            'questionNumber' => $questionNumber,
        ]);
    }

    /**
     * @return string
     */
    protected function generateSecuritySettingsUrlForNewProfile()
    {
        return $this->personProfileUrlGenerator->fromPersonProfile('security-settings');
    }
}
