<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Account\ViewModel;

use Account\AbstractClass\AbstractSecurityQuestionViewModel;
use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
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
     * @return UserAdminUrlBuilderWeb
     */
    public function getNextPageLink(FlashMessenger $flashMessenger)
    {
        if ($this->service->getQuestionNumber() == UserAdminSessionManager::FIRST_QUESTION) {
            if ($this->service->getQuestionSuccess() === true) {
                return AccountUrlBuilderWeb::forgottenPasswordSecurityQuestion(
                    $this->getPersonId(),
                    UserAdminSessionManager::SECOND_QUESTION
                );
            }
            $flashMessenger->clearCurrentMessagesFromNamespace(FlashMessenger::NAMESPACE_ERROR);

            return AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated();
        }
        if ($this->service->getQuestionSuccess() === true) {
            return AccountUrlBuilderWeb::forgottenPasswordAuthenticated();
        }

        return AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated();
    }

    /**
     * This function return the skip question link.
     *
     * @return UserAdminUrlBuilderWeb
     */
    public function getCurrentLink()
    {
        return AccountUrlBuilderWeb::forgottenPasswordSecurityQuestion(
            $this->getPersonId(),
            $this->getQuestionNumber()
        );
    }
}
