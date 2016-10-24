package uk.gov.dvsa.ui.feature.journey.authentication;

import org.testng.annotations.Test;
import uk.gov.dvsa.helper.ContactDetailsHelper;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.login.LoginPage;
import uk.gov.dvsa.ui.pages.userregistration.CreateAnAccountPage;

import java.io.IOException;

import static org.hamcrest.CoreMatchers.containsString;
import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class UserRegistrationTests extends DslTest {
    
    private final String telephone = ContactDetailsHelper.getPhoneNumber();

    @Test(groups = {"BVT"}, description = "VM-11472")
    public void createUserAccountSuccessfully() throws IOException {

        //Given I am on the Create Account Page
        motUI.register.createAccountPage();

        //When I continue to enter my details
        String email = ContactDetailsHelper.getEmail();
        motUI.register.completeDetailsWithDefaultValues(email, telephone);

        //Then my account is created successfully
        assertThat(motUI.register.isAccountCreated(), is(true));
    }

    @Test(groups = {"BVT"})
    public void cannotCreateANewAccountIfEmailIsAlreadyInUse() throws IOException {

        step("Given I create an account");
        String email = ContactDetailsHelper.getEmail();
        motUI.register.completeDetailsWithDefaultValues(email, telephone);

        step("When I create another account with the same email");
        String message = motUI.register.completeDetailsWithCustomValuesExpectingMessage(email);

        step("Then I am redirected to a Duplicate email page and advised that this email is already in use");
        assertThat("message is displayed", message, containsString("This email is already in use"));
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
