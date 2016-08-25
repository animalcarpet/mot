package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.accountclaim.AccountClaimConfirmationPage;
import uk.gov.dvsa.ui.pages.accountclaim.AccountClaimPage;
import uk.gov.dvsa.ui.pages.accountclaim.AccountClaimReviewPage;
import uk.gov.dvsa.ui.pages.accountclaim.TwoFaAccountClaimConfirmationPage;
import uk.gov.dvsa.ui.pages.login.LoginPage;

import java.io.IOException;

import static uk.gov.dvsa.helper.RandomDataGenerator.generateEmail;
import static uk.gov.dvsa.helper.RandomDataGenerator.generatePassword;
import static uk.gov.dvsa.helper.RandomDataGenerator.generateRandomString;

public class ClaimAccount {

    private PageNavigator pageNavigator;

    private Boolean isPinDisplayed;

    public ClaimAccount(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public LoginPage claimAs2FaUser(User user) throws IOException {

        // Given I go to the Review Page
        AccountClaimReviewPage claimReviewPage = takeUserToReviewPage(user);

        //And I verify my details entered and end up on confirmation page
        TwoFaAccountClaimConfirmationPage claimConfirmationPage = claimReviewPage
                .clickClaimYourAccountButton(TwoFaAccountClaimConfirmationPage.class);

        isPinDisplayed = false;
        // Then I finish claiming account back on Login Page
        return claimConfirmationPage.goToSignIn();
    }

    public HomePage claimAsUser(User user) throws IOException {

        // Given I go to the Review Page
        AccountClaimReviewPage claimReviewPage = takeUserToReviewPage(user);

        //And I verify my details entered
        AccountClaimConfirmationPage claimConfirmationPage = claimReviewPage.clickClaimYourAccountButton(AccountClaimConfirmationPage.class);

        isPinDisplayed = claimConfirmationPage.isPinNumberDisplayed();

        //And user is directed to the HomePage
        return claimConfirmationPage.clickContinueToTheMotTestingService();
    }

    public Boolean isPinDisplayed() {
        return isPinDisplayed;
    }

    private AccountClaimReviewPage takeUserToReviewPage(User user) throws IOException {

        //Given I am on the AccountClaim page to my claim my account
        AccountClaimPage accountClaimPage = pageNavigator.navigateToPage(user, AccountClaimPage.PATH, AccountClaimPage.class);

        //When I Enter a valid Email Address and a compliant Password
        accountClaimPage.enterEmailAndPassword(generateEmail(7), generatePassword(8));

        accountClaimPage.clickContinueButton();

        //And I set my security answers
        accountClaimPage.setSecurityQuestionsAndAnswers(generateRandomString(), generateRandomString());

        return accountClaimPage.clickContinueToAccountReview();
    }
}
