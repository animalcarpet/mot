package uk.gov.dvsa.ui.feature.journey.authentication;

import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.helper.RandomDataGenerator;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.accountclaim.AccountClaimConfirmationPage;
import uk.gov.dvsa.ui.pages.accountclaim.AccountClaimPage;
import uk.gov.dvsa.ui.pages.accountclaim.AccountClaimReviewPage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class ClaimUserAccountTests extends DslTest {

    @DataProvider(name = "createTester")
    public Object[][] createTesterAndVehicle() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        Site testSite = siteData.createNewSite(aeDetails.getId(), "My_Site");

        return new Object[][]{
                {userData.createTester(testSite.getId(), true)},
                {userData.createCustomerServiceOfficer(true)},
                {userData.createAedm(true)},
        };
    }

    @Test(groups = {"BVT"}, description = "VM-10319 - Tester, CSCO, AEDM can Claim Account and Set Password",
    dataProvider = "createTester")
    public void claimAsUser(User user) throws Exception {

        //Given I am on the AccountClaim page to my claim my account
        AccountClaimPage accountClaimPage = pageNavigator.navigateToPage(user, AccountClaimPage.PATH, AccountClaimPage.class);

        //When I Enter a valid Email Address and a compliant Password
        accountClaimPage.enterEmailAndPassword(
                RandomDataGenerator.generateEmail(7), RandomDataGenerator.generatePassword(8));

        accountClaimPage.clickContinueButton();

        //And I set my security answers
        accountClaimPage.setSecurityQuestionsAndAnswers(
                RandomDataGenerator.generateRandomString(), RandomDataGenerator.generateRandomString());

        AccountClaimReviewPage claimReviewPage = accountClaimPage.clickContinueToAccountReview();

        //And I verify my details entered
        AccountClaimConfirmationPage claimConfirmationPage = claimReviewPage.clickClaimYourAccountButton();

        //Then verify a pin number is displayed
        assertThat(claimConfirmationPage.isPinNumberDisplayed(), is(true));

        //And user is directed to the HomePage
        HomePage homePage = claimConfirmationPage.clickContinueToTheMotTestingService();
        assertThat(homePage.compareUserNameWithSessionUsername(), is(true));
    }
}

