package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.helper.ContactDetailsHelper;
import uk.gov.dvsa.ui.pages.userregistration.*;

import java.io.IOException;

public class Register {
    private PageNavigator pageNavigator;
    private boolean accountCreated = false;
    private boolean duplicateEmailAddress = false;

    public Register(PageNavigator pageNavigator)
    {
        this.pageNavigator = pageNavigator;
    }

    public CreateAnAccountPage createAnAccount() throws IOException
    {
        return pageNavigator.goToCreateAnAccountPage();
    }

    public void completeDetailsWithDefaultValues(String email, String telephone) throws IOException {
        SummaryPage summaryPage = enterDetails(ContactDetailsHelper.generateUniqueName(),
                ContactDetailsHelper.generateUniqueName(),
                email, telephone);

        AccountCreatedPage createdPage = summaryPage.clickCreateYourAccount();
        accountCreated = createdPage.isAccountCreatedTextDisplayed();
    }

    public void completeDetailsWithCustomValues(String name, String surname, String emailAddress, String telephone) throws IOException {

        SummaryPage summaryPage = enterDetails(name, surname, emailAddress, telephone);

        duplicateEmailAddress = summaryPage.emailAlreadyUsedMessage();

        summaryPage.clickCreateYourAccount();
    }

    private SummaryPage enterDetails(String name, String surname, String emailAddress, String telephone) throws IOException {
        DetailsPage detailsPage = createAnAccount().details();
        detailsPage.enterYourDetails(emailAddress, name, surname, telephone);

        AddressPage addressPage = detailsPage.clickContinue();
        addressPage.enterAddress();

        SecurityQuestionOnePage questionOnePage = addressPage.clickContinue();
        questionOnePage.chooseQuestionAndAnswer();

        SecurityQuestionTwoPage questionTwoPage = questionOnePage.clickContinue();
        questionTwoPage.chooseQuestionAndAnswer();

        PasswordPage passwordPage = questionTwoPage.clickContinue();
        passwordPage.enterPassword();

        return passwordPage.clickContinue();
    }

    public boolean isAccountCreated()
    {
        return accountCreated;
    }

    public boolean isEmailDuplicated() {
        return duplicateEmailAddress;
    }
}
