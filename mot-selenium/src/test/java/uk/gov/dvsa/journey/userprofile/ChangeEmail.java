package uk.gov.dvsa.journey.userprofile;

import uk.gov.dvsa.ui.pages.ChangeEmailDetailsPage;
import uk.gov.dvsa.ui.pages.profile.NewPersonProfilePage;
import uk.gov.dvsa.ui.pages.profile.NewUserProfilePage;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;

public class ChangeEmail {
    private ProfilePage profilePage;

    public ChangeEmail(ProfilePage profilePage) {
        this.profilePage = profilePage;
    }

    public ProfilePage changeYourEmailTo(String email) {
        return profilePage.clickChangeEmailLink()
                .fillEmail(email)
                .fillEmailConfirmation(email)
                .clickSubmitButton(NewPersonProfilePage.class);
    }

    public ProfilePage changeUserEmailAsDvsaTo(String email) {
        return profilePage.clickChangeEmailLink()
                .fillEmail(email)
                .fillEmailConfirmation(email)
                .clickSubmitButton(NewUserProfilePage.class);
    }

    public String changeEmailWithInvalidInputs(String email, String confirmationEmail) {
        return profilePage.clickChangeEmailLink()
                .fillEmail(email)
                .fillEmailConfirmation(confirmationEmail)
                .clickSubmitButton(ChangeEmailDetailsPage.class).getValidationMessage();
    }

}
