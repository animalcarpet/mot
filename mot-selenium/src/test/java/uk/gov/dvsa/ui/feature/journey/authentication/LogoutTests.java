package uk.gov.dvsa.ui.feature.journey.authentication;

import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.HomePage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.is;

public class LogoutTests extends DslTest {

    @Test(groups = {"BVT"})
    void authenticatedUserCanLogOut() throws IOException {
        //Given I am an Authenticated User
        User validUser = motApi.user.createAreaOfficeOne("dvsaUser");
        motUI.login(validUser);

        //When I logout using the link in the toolbar
        motUI.logout(validUser);

        //Then I am successfully logged out
        assertThat("Logout was successful", motUI.verifyLoginPageIsDisplayed(), is(true));
    }
}
