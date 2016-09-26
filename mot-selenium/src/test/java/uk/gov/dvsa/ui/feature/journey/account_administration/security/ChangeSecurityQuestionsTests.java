package uk.gov.dvsa.ui.feature.journey.account_administration.security;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;
import static org.hamcrest.text.IsEqualIgnoringCase.equalToIgnoringCase;

public class ChangeSecurityQuestionsTests extends DslTest {

    private User tradeUser;

    @BeforeMethod(alwaysRun = true)
    private void createTradeUser() throws IOException {
        tradeUser = userData.createTester(siteData.createSite().getId());
    }

    @Test(groups = "BVT")
    void changeSecurityQuestionsLinkIsDisplayedOnUserProfile() throws IOException {
        step("Given I am on my profile page as a trade user");
        step("Then change security questions link is displayed");
        assertThat("Change Security Question Link is Displayed" ,
            motUI.profile.viewYourProfile(tradeUser).isChangeSecurityQuestionsLinkDisplayed(), is(true));
    }

    @Test(groups = "BVT")
    void CscoCannotChangeSecurityQuestionForUser() throws IOException {
        step("Given I am viewing a trade user profile as CSCO");
        User csco = userData.createCSCO();

        step("Then I should not see the option to change security questions");
        assertThat("Change Security Question Link is Displayed" ,
            motUI.profile.dvsaViewUserProfile(csco, tradeUser).isChangeSecurityQuestionsLinkDisplayed(), is(false));
    }

    @Test(groups = "BVT")
    public void userCanChangeTheirSecurityQuestion() throws IOException {
        step("Given I on my profile Page as a trade user");
        motUI.profile.viewYourProfile(tradeUser);

        step("When I change my security questions");
        message = motUI.profile.changeSecurityQuestionsAndAnswer(tradeUser);

        step("Then my security questions should be changed Successfully");
        assertThat("Questions Changed Successfully",
            message, equalToIgnoringCase("Your security questions have been changed"));
    }
}
