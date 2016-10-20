package uk.gov.dvsa.ui.feature.journey.authentication;

import org.testng.annotations.Test;
import uk.gov.dvsa.helper.ContactDetailsHelper;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.login.LoginPage;
import uk.gov.dvsa.ui.pages.userregistration.CreateAnAccountPage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class UserRegistrationTests extends DslTest {

    private final String name = ContactDetailsHelper.generateUniqueName();
    private final String surname = ContactDetailsHelper.generateUniqueName();
    private final String email = ContactDetailsHelper.getEmail();
    private final String telephone = ContactDetailsHelper.getPhoneNumber();

    @Test(groups = {"BVT"}, description = "VM-11472")
    public void createUserAccountSuccessfully() throws IOException {

        //Given I am on the Create Account Page
        motUI.register.createAccountPage();

        //When I continue to enter my details
        motUI.register.completeDetailsWithDefaultValues(email, telephone);

        //Then my account is created successfully
        assertThat(motUI.register.isAccountCreated(), is(true));
    }

    @Test(groups = {"BVT"}, description = "VM-11472")
    public void checkForDuplicateEmail() throws IOException {

        //Given I am on the Create Account Page
        motUI.register.createAccountPage();

        //When I continue to enter my details
        motUI.register.completeDetailsWithDefaultValues(email, telephone);

        //When I re-enter my details and use the same email as before
        motUI.register.completeDetailsWithCustomValues(name, surname, email, telephone);

        //Then I am prompted that this email is already in use
        assertThat(motUI.register.isEmailDuplicated(), is(true));
    }

    @Test(groups = {"BVT"}, description = "VM-11472")
    public void checkCreateAccountLinkExists() throws IOException {

        //Given I am on the Home Page
        LoginPage loginPage = pageNavigator.goToLoginPage();

        //When I expand the do not have account section
        loginPage.expandDoNotHaveAccountSection();

        //And click the create Account Link
        CreateAnAccountPage createAnAccountPage = loginPage.clickCreateAnAccountLink();

        //Then I should be on the create account page
        assertThat(createAnAccountPage.isContinueButtonDisplayed(), is(true));
    }
}
