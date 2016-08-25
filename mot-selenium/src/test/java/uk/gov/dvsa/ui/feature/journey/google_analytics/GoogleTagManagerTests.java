package uk.gov.dvsa.ui.feature.journey.google_analytics;

import org.testng.annotations.Test;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.login.LoginPage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.is;

public class GoogleTagManagerTests extends DslTest {

    @Test(groups = {"Regression", "BL-2706"},
            description = "Checks that Google TagManager's dataLayer structure is displayed in the login page")
    void googleTagManagerDataLayerIsRendered() throws IOException {
        // Given I am on the login page
        LoginPage loginPage = pageNavigator.goToLoginPage();

        // Then the Google TagManager's dataLayer structure should be displayed
        assertThat(loginPage.isGoogleTagManagerDataLayerRendered(), is(true));
    }

    @Test(groups = {"Regression", "BL-2706"},
            description = "Checks that Google TagManager's dataLayer structure with userId property is displayed for logged in users")
    void googleTagManagerDataLayerIsRenderedWhileLoggedIn() throws IOException {
        // Given I am valid user
        User validUser = userData.createTester(siteData.createSite().getId());

        // When I login through the login page
        HomePage homePage = pageNavigator.gotoHomePage(validUser);

        // Then the Google TagManager's dataLayer structure with the "userId" property should be displayed
        assertThat(homePage.refresh().isGoogleTagManagerDataLayerRendered(), is(true));
    }
}
