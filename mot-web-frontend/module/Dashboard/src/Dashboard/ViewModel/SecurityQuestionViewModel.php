<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dashboard\ViewModel;

use Account\AbstractClass\AbstractSecurityQuestionViewModel;
use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

/**
 * Class SecurityQuestionViewModel.
 */
class SecurityQuestionViewModel extends AbstractSecurityQuestionViewModel
{
    /**
     * This function return the skip question link.
     *
     * @param FlashMessenger $flashMessenger
     *
     * @return UserAdminUrlBuilderWeb|string
     */
    public function getNextPageLink(FlashMessenger $flashMessenger)
    {
        if ($this->service->getQuestionNumber() == UserAdminSessionManager::FIRST_QUESTION) {
            if ($this->service->getQuestionSuccess() === true) {
                $questionNumber = UserAdminSessionManager::SECOND_QUESTION;

                return $this->generateSecurityQuestionsUrlForNewProfile($questionNumber);
            }

            $flashMessenger->clearCurrentMessagesFromNamespace(FlashMessenger::NAMESPACE_ERROR);

            return AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated();
        }

        if ($this->service->getQuestionSuccess() === true) {
            return $this->generateSecuritySettingsUrlForNewProfile();
        }

        return AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated();
    }

    /**
     * This function returns the skip question link.
     *
     * @return UserAdminUrlBuilderWeb|string
     */
    public function getCurrentLink()
    {
        $questionNumber = $this->getQuestionNumber();

        return $this->generateSecurityQuestionsUrlForNewProfile($questionNumber);
    }

    /**
     * @param int $questionNumber
     *
     * @return string
     */
    public function getFormActionUrl($questionNumber)
    {
        return $this->generateSecurityQuestionsUrlForNewProfile($questionNumber);
    }
}
