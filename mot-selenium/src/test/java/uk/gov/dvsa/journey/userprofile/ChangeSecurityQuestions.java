package uk.gov.dvsa.journey.userprofile;

import uk.gov.dvsa.ui.pages.profile.ProfilePage;
import uk.gov.dvsa.ui.pages.profile.security.ChangeSecurityQuestionTwoPage;

public class ChangeSecurityQuestions {
    private ProfilePage profilePage;

    public ChangeSecurityQuestions(ProfilePage profilePage) {
        this.profilePage = profilePage;
    }

    public String change(String value) {
        ChangeSecurityQuestionTwoPage pageTwo = profilePage.clickChangeSecurityQuestionsLink().submitPassword(value).
        chooseQuestionAndAnswer().clickContinue();
        return pageTwo.chooseQuestionAndAnswer().clickContinue().saveChanges().successMessage();
    }
}
