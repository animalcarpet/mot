<?php

namespace UserAdmin\ViewModel;

use Account\AbstractClass\AbstractSecurityQuestionViewModel;
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
            return UserAdminUrlBuilderWeb::userProfileSecurityQuestion(
                $this->getUserId(),
                UserAdminSessionManager::SECOND_QUESTION
            );
        }
        $flashMessenger->clearCurrentMessagesFromNamespace(FlashMessenger::NAMESPACE_ERROR);

        return UserAdminUrlBuilderWeb::userProfile($this->getUserId())->toString().'?'.$this->getSearchParams();
    }

    /**
     * This function return the skip question link.
     *
     * @return UserAdminUrlBuilderWeb
     */
    public function getCurrentLink()
    {
        return UserAdminUrlBuilderWeb::userProfileSecurityQuestion($this->getUserId(), $this->getQuestionNumber());
    }
}
