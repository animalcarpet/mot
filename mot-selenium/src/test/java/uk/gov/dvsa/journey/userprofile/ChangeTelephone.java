package uk.gov.dvsa.journey.userprofile;

import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.profile.UserProfilePage;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;
import uk.gov.dvsa.ui.pages.profile.ChangeTelephonePage;

public class ChangeTelephone {
    private PageNavigator pageNavigator;
    private ProfilePage profilePage;

    private static final String TELEPHONE_TOO_LARGE_MESSAGE = "Phone number - must be 24 characters or less";

    public ChangeTelephone(PageNavigator pageNavigator, ProfilePage profilePage) {
        this.pageNavigator = pageNavigator;
        this.profilePage = profilePage;
    }

    public ProfilePage changeYourTelephoneTo(String phoneNumber) {
        return profilePage.clickChangeTelephoneLink()
                .fillTel(phoneNumber)
                .submitAndReturnToProfilePage(profilePage);
    }

    public ProfilePage changeUserTelephoneAsDvsaTo(String phoneNumber) {
        return profilePage.clickChangeTelephoneLink()
                .fillTel(phoneNumber)
                .clickSubmitButton(UserProfilePage.class);
    }

    public boolean isValidationMessageOnChangeTelephonePageDisplayed() {
        return getChangTelephonePage().getValidationMessage().equals(TELEPHONE_TOO_LARGE_MESSAGE);
    }

    private ChangeTelephonePage getChangTelephonePage() {
        return new ChangeTelephonePage(pageNavigator.getDriver());
    }
}
