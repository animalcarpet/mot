package uk.gov.dvsa.ui.feature.journey.authentication;

import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.is;

public class LoginTests extends DslTest {

    @Test(groups = {"BVT"})
    void userCanLogInSuccessfullyViaFrontend() throws IOException {
        //Given I am valid user
        User validUser = userData.createTester(siteData.createSite().getId());

        //When I login through the login page
        motUI.login(validUser);

        //Then my login is successful
        assertThat(motUI.isLoginSuccessful(), is(true));
    }
}
