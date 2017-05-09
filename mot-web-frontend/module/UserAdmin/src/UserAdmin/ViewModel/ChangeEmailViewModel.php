<?php

namespace UserAdmin\ViewModel;

class ChangeEmailViewModel
{
    private $form;

    private $email;

    private $emailConfirm;

    private $isViewingOwnProfile;

    public function setIsViewingOwnProfile($isViewingOwnProfile)
    {
        $this->isViewingOwnProfile = $isViewingOwnProfile;
    }

    public function getIsViewingOwnProfile()
    {
        return $this->isViewingOwnProfile;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function setForm($form)
    {
        $this->form = $form;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmailConfirm()
    {
        return $this->emailConfirm;
    }

    public function setEmailConfirm($emailConfirm)
    {
        $this->emailConfirm = $emailConfirm;
    }
}
