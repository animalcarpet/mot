package uk.gov.dvsa.journey.userprofile;

import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.profile.ChangeNamePage;
import uk.gov.dvsa.ui.pages.profile.NewUserProfilePage;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;

public class ChangeName {

    private PageNavigator pageNavigator;
    private ProfilePage profilePage;

    private static final String FIRST_NAME_ERROR_MESSAGE = "First name - you must enter a first name";
    private static final String LAST_NAME_ERROR_MESSAGE = "Last name - you must enter a last name";

    public ChangeName(PageNavigator pageNavigator, ProfilePage profilePage) {
        this.pageNavigator = pageNavigator;
        this.profilePage = profilePage;
    }

    public <T extends Page> T changePersonName(String firstName, String lastName, boolean isInputValid) {
        profilePage.clickChangeNameLink().fillFirstName(firstName).fillLastName(lastName);
        if (!isInputValid) {
            return (T)getChangeNamePage().clickSubmitButton(ChangeNamePage.class);
        }
        return (T)getChangeNamePage().clickSubmitButton(NewUserProfilePage.class);
    }

    public boolean isValidationMessageOnChangeNamePageDisplayed(String warningMessage) {
        switch (warningMessage) {
            case "FIRST_NAME":
                return getChangeNamePage().getValidationMessage().equals(FIRST_NAME_ERROR_MESSAGE);
            case "LAST_NAME":
                return getChangeNamePage().getValidationMessage().equals(LAST_NAME_ERROR_MESSAGE);
            default:
                return false;
        }
    }

    private ChangeNamePage getChangeNamePage() {
        return new ChangeNamePage(pageNavigator.getDriver());
    }
}