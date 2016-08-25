package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.helper.ConfigHelper;
import uk.gov.dvsa.ui.pages.helpdesk.HelpDeskProfilePage;
import uk.gov.dvsa.ui.pages.helpdesk.HelpDeskUserProfilePage;
import uk.gov.dvsa.ui.pages.helpdesk.NewHelpDeskUserProfilePage;

import java.io.IOException;

public class HelpDesk {

    private final PageNavigator navigator;
    private HelpDeskProfilePage profilePage;

    public HelpDesk(PageNavigator pageNavigator) {
        navigator = pageNavigator;
    }

    public void viewUserProfile(User userViewingProfile, String profileIdToView) throws IOException {
        String path = String.format(NewHelpDeskUserProfilePage.PATH, profileIdToView);
        String legacyPath = String.format(HelpDeskUserProfilePage.PATH, profileIdToView);
        if(ConfigHelper.isNewPersonProfileEnabled()){
            profilePage = navigator.navigateToPage(userViewingProfile, path, NewHelpDeskUserProfilePage.class);
        } else {
            profilePage = navigator.navigateToPage(userViewingProfile, legacyPath, HelpDeskUserProfilePage.class);
        }
    }

    public HelpDeskProfilePage page() {
        return profilePage;
    }
}
