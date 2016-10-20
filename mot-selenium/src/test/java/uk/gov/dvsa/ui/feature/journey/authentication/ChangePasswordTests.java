package uk.gov.dvsa.ui.feature.journey.authentication;


import org.testng.Assert;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.ChangePasswordFromProfilePage;
import uk.gov.dvsa.ui.pages.profile.PersonProfilePage;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

import java.io.IOException;

public class ChangePasswordTests extends DslTest {

    private User tester;
    private String messageSuccess = "Your password has been changed.";
    private String errorMessageBase = "There was a problem with the information you entered:\n";

    @BeforeMethod(alwaysRun = true) public void setUp() throws IOException {
        tester = motApi.user.createTester(1);
    }

    @Test(groups = {"BVT"}, description = "VM-7668, Tester is changing password")
    public void testerChangesPassword() throws Exception {

        //Given I am logged as a tester and I am on my profile page
        PersonProfilePage personProfilePage = pageNavigator.navigateToPage(tester, PersonProfilePage.PATH, PersonProfilePage.class);

        //And I click change password link
        ChangePasswordFromProfilePage changePasswordFromProfilePage =
                personProfilePage.clickChangePasswordLink();

        //When I change my password
        changePasswordFromProfilePage.enterOldPassword(tester.getPassword());
        String password = "Password2";
        changePasswordFromProfilePage.enterNewPassword(password);
        changePasswordFromProfilePage.confirmNewPassword(password);
        personProfilePage = changePasswordFromProfilePage.clickSubmitButton(PersonProfilePage.class);

        //Then the password is changed and the success message is displayed
        assertThat(personProfilePage.isSuccessMessageDisplayed(), is(true));
        Assert.assertTrue(personProfilePage.getMessageSuccess().toString().equals(messageSuccess));
    }

    @Test(groups = {"BVT"}, description = "VM-7668, Tester cancels password change")
    public void testerCancelsPasswordChange() throws Exception {

        //Given I am logged as a tester and I am on my profile page
        PersonProfilePage personProfilePage = pageNavigator.navigateToPage(tester, PersonProfilePage.PATH, PersonProfilePage.class);

        //And I click change password link
        ChangePasswordFromProfilePage changePasswordFromProfilePage =
                personProfilePage.clickChangePasswordLink();

        //Then I click cancel link and I am back to the profile page
        changePasswordFromProfilePage.clickCancelLink();
    }

    @Test(groups = {"BVT"}, description = "VM-7668, Tester changes password for the same one")
    public void testerChangesPasswordForSameOne() throws Exception {

        //Given I am logged in as a tester and I am on the password change page
        ChangePasswordFromProfilePage changePasswordFromProfilePage =
                pageNavigator.navigateToPage(tester, ChangePasswordFromProfilePage.PATH, ChangePasswordFromProfilePage.class);

        //When I try to change password same as the old password
        changePasswordFromProfilePage.enterOldPassword(tester.getPassword());
        changePasswordFromProfilePage.enterNewPassword(tester.getPassword());
        changePasswordFromProfilePage.confirmNewPassword(tester.getPassword());
        changePasswordFromProfilePage.clickSubmitButton(ChangePasswordFromProfilePage.class);

        //Then The error message is displayed
        String error = "New password - password was found in the password history";
        assertThat(changePasswordFromProfilePage.isErrorMessageWindowDisplayed(), is(true));
        Assert.assertTrue(changePasswordFromProfilePage.getErrorMessage()
                .equals(String.format(errorMessageBase + error)));
    }

    @Test(groups = {"BVT"},
            description = "VM-7668, Tester tries to put new password but does not match with confirm password")
    public void newPasswordAndOldPasswordDoesNotMatch() throws Exception {

        //Given I am logged in as a tester and I am on the password change page
        ChangePasswordFromProfilePage changePasswordFromProfilePage =
                pageNavigator.navigateToPage(tester, ChangePasswordFromProfilePage.PATH, ChangePasswordFromProfilePage.class);

        //When I try to type new password that does not match with confirmed password
        changePasswordFromProfilePage.enterOldPassword(tester.getPassword());
        changePasswordFromProfilePage.enterNewPassword("Password2");
        changePasswordFromProfilePage.confirmNewPassword("Password3");
        changePasswordFromProfilePage.clickSubmitButton(ChangePasswordFromProfilePage.class);

        //Then The error message is displayed
        String error = "Re-type your new password - the passwords you have entered don't match";
        assertThat(changePasswordFromProfilePage.isErrorMessageWindowDisplayed(), is(true));
        Assert.assertTrue(changePasswordFromProfilePage.getErrorMessage()
                .equals(String.format(errorMessageBase + error)));
    }

    @Test(groups = {"BVT"},
            description = "VM-7668, Tester tries change password that is not according to password policy")
    public void testerTriesToChangePasswordThatValidatesPolicy() throws Exception {

        //Given I am logged in as a tester and I am on the password change page
        ChangePasswordFromProfilePage changePasswordFromProfilePage =
                pageNavigator.navigateToPage(tester, ChangePasswordFromProfilePage.PATH, ChangePasswordFromProfilePage.class);

        //When I try to change password for the password that does not match password policy
        changePasswordFromProfilePage.enterOldPassword(tester.getPassword());
        changePasswordFromProfilePage.enterNewPassword("pass");
        changePasswordFromProfilePage.confirmNewPassword("pass");
        changePasswordFromProfilePage.clickSubmitButton(ChangePasswordFromProfilePage.class);

        //Then The error message is displayed
        String error1 = "New password - must be 8 or more characters long";
        assertThat(changePasswordFromProfilePage.isErrorMessageWindowDisplayed(), is(true));
        Assert.assertTrue(changePasswordFromProfilePage.getErrorMessage().contains(error1));
    }

    @Test(groups = {"BVT"}, description = "VM-7668, Tester types invalid old password")
    public void testerPutsInvalidOldPassword() throws Exception {

        //Given I am logged in as a tester and I am on the password change page
        ChangePasswordFromProfilePage changePasswordFromProfilePage =
                pageNavigator.navigateToPage(tester, ChangePasswordFromProfilePage.PATH, ChangePasswordFromProfilePage.class);

        //When I try put empty old password
        changePasswordFromProfilePage.enterNewPassword("Password1");
        changePasswordFromProfilePage.confirmNewPassword("Password1");
        changePasswordFromProfilePage.clickSubmitButton(ChangePasswordFromProfilePage.class);

        //Then The error message is displayed
        String error = "Current password - enter your current password";
        assertThat(changePasswordFromProfilePage.isErrorMessageWindowDisplayed(), is(true));
        Assert.assertTrue(changePasswordFromProfilePage.getErrorMessage()
                .equals(String.format(errorMessageBase + error)));
    }

    @Test(groups = {"BVT"}, description = "VM-7668, Tester leaves empty fields and click submit")
    public void testerLeavesEmptyFields() throws Exception {

        //Given I am logged in as a tester and I am on the password change page
        ChangePasswordFromProfilePage changePasswordFromProfilePage =
                pageNavigator.navigateToPage(tester, ChangePasswordFromProfilePage.PATH, ChangePasswordFromProfilePage.class);

        //When I leave empty fields and click submit
        changePasswordFromProfilePage.clickSubmitButton(ChangePasswordFromProfilePage.class);

        //Then The error message is displayed
        assertThat(changePasswordFromProfilePage.isErrorMessageWindowDisplayed(), is(true));
        Assert.assertTrue(changePasswordFromProfilePage.getErrorMessage()
                .contains("New password - enter a password"));
        Assert.assertTrue(changePasswordFromProfilePage.getErrorMessage()
                .contains("Re-type your new password - re-type your password"));
        Assert.assertTrue(changePasswordFromProfilePage.getErrorMessage()
                .contains("Current password - enter your current password"));
    }
}
