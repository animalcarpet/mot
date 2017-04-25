package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.model.PersonDetails;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.userregistration.*;

import java.io.IOException;

public class Register {
    private PageNavigator pageNavigator;
    private boolean accountCreated = false;

    public Register(PageNavigator pageNavigator)
    {
        this.pageNavigator = pageNavigator;
    }

    public CreateAnAccountPage createAccountPage() throws IOException
    {
        return pageNavigator.goToCreateAnAccountPage();
    }

    public void completeDetailsWithDefaultValues(String email, String telephone) throws IOException {
        PersonDetails personDetails = new PersonDetails();
        completeDetailsWithCustomValues(personDetails.getFirstName(), personDetails.getLastName(),
                    email, telephone, personDetails.getDateOfBirthDay(), personDetails.getDateOfBirthMonth(), personDetails.getDateOfBirthYear());
    }

    private void completeDetailsWithCustomValues(String name, String surname, String emailAddress, String telephone,
                                                int dateOfBirthDay, int dateOfBirthMonth, int dateOfYear) throws IOException {

        SummaryPage summaryPage = enterDetails(name, surname, emailAddress, telephone,
                dateOfBirthDay, dateOfBirthMonth, dateOfYear);

        accountCreated = summaryPage.clickCreateYourAccount().isAccountCreatedTextDisplayed();
    }

    private SummaryPage enterDetails(String name, String surname, String emailAddress, String telephone,
                                        int dateOfBirthDay, int dateOfBirthMonth, int dateOfYear) throws IOException {

        EmailPage emailPage = createAccountPage().email();
        emailPage.enterYourDetails(emailAddress, emailAddress);

        DetailsPage detailsPage = emailPage.clickContinue();
        detailsPage.enterYourDetails(name, surname, dateOfBirthDay, dateOfBirthMonth, dateOfYear);

        AddressPage addressPage = detailsPage.clickContinue();
        addressPage.enterAddressandTelephone();

        SecurityQuestionsPage securityQuestionsPage = addressPage.clickContinue();
        securityQuestionsPage.chooseQuestionsAndAnswers();

        PasswordPage passwordPage = securityQuestionsPage.clickContinue();
        passwordPage.enterPassword();

        return passwordPage.clickContinue();
    }

    public String completeDetailsWithCustomValuesExpectingMessage(String emailAddress) throws IOException {
        EmailPage emailPage = createAccountPage().email();
        emailPage.enterYourDetails(emailAddress, emailAddress);

        DuplicateEmailPage duplicateEmailPage = emailPage.clickContinueWithEmailAlreadyInUse();
        return duplicateEmailPage.getMessageText();
    }

    public boolean isAccountCreated() {
        return accountCreated;
    }

}
