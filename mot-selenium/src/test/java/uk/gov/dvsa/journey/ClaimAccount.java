package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.accountclaim.AccountClaimPasswordPage;
import uk.gov.dvsa.ui.pages.accountclaim.AccountClaimReviewPage;
import uk.gov.dvsa.ui.pages.accountclaim.AccountClaimSecurityQuestionsPage;
import uk.gov.dvsa.ui.pages.accountclaim.TwoFaAccountClaimConfirmationPage;

import java.io.IOException;

import static uk.gov.dvsa.helper.RandomDataGenerator.generatePassword;
import static uk.gov.dvsa.helper.RandomDataGenerator.generateRandomString;

public class ClaimAccount {

    private PageNavigator pageNavigator;

    public ClaimAccount(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public TwoFaAccountClaimConfirmationPage claimAs2FaUser(User user) throws IOException {

        // Given I go to the Review Page
        AccountClaimReviewPage claimReviewPage = takeUserToReviewPage(user);

        // And I verify my details entered and end up on confirmation page
        return claimReviewPage.clickClaimYourAccountButton(TwoFaAccountClaimConfirmationPage.class);
    }

    private AccountClaimReviewPage takeUserToReviewPage(User user) throws IOException {

        // Given I am on the AccountClaim page to my claim my account
        AccountClaimPasswordPage accountClaimPage = pageNavigator.navigateToPage(user, AccountClaimPasswordPage.PATH, AccountClaimPasswordPage.class);

        // When I Enter a valid Email Address and a compliant Password
        accountClaimPage.enterPassword(generatePassword(8));
        AccountClaimSecurityQuestionsPage securityQuestionsPage = accountClaimPage.clickContinueButton();

        // And I set my security answers
        securityQuestionsPage.setSecurityQuestionsAndAnswers(generateRandomString(), generateRandomString());

        return securityQuestionsPage.clickContinueToAccountReview();
    }
}
