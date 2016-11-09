package uk.gov.dvsa.ui.feature.journey.authentication;


import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;

public class ChangePasswordTests extends DslTest {

    private User tester;

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        tester = motApi.user.createTester(siteData.createSite().getId());
    }

    @Test(groups = {"BVT"}, description = "VM-7668, Tester is changing password")
    public void testerChangesPassword() throws Exception {
        step("Given I change my password");
        String message = motUI.profile.changePasswordExpectingSuccessText(tester, tester.getPassword(), "Password34");

        step("Then the password is changed and the success message is displayed");
        assertThat("Password Changed successfully", message, containsString("Your password has been changed."));
    }

    @Test(groups = {"BVT"}, description = "VM-7668, Tester cancels password change")
    public void testerCancelsPasswordChange() throws Exception {
        step("Given I am on the change password page");

        step("Then I can click cancel link to return to profile page");
        motUI.profile.viewYourProfile(tester).clickChangePasswordLink().clickCancelLink();
    }

    @Test(groups = {"BVT"}, description = "VM-7668, Tester changes password for the same one")
    public void testerChangesPasswordForSameOne() throws Exception {
        step("Given I attempt to change my password with current password");
        String message = motUI.profile.changePasswordExpectingErrorText(tester, tester.getPassword(), tester.getPassword());

        step("Then The error message is displayed");
        assertThat("Password found in history exception is shown", message,
            containsString("New password - password was found in the password history"));
    }

    @Test(groups = {"BVT"},
            description = "VM-7668, Tester tries to put new password but does not match with confirm password")
    public void newPasswordAndOldPasswordDoesNotMatch() throws Exception {
        step("Given I attempt to change my password with incorrect confirm password");
        String message = motUI.profile.changePasswordExpectingErrorText(tester, tester.getPassword(), "NewPa22word");

        step("Then Re-type your new password exception is displayed");
        assertThat("Password found in history exception is shown", message,
            containsString("Re-type your new password - the passwords you have entered don't match"));
    }

    @Test(groups = {"BVT"},
            description = "VM-7668, Tester tries change password that is not according to password policy")
    public void testerTriesToChangePasswordThatValidatesPolicy() throws IOException {
        step("Given I attempt to change my password with a value that fails password policy validation");
        String message = motUI.profile.changePasswordExpectingErrorText(tester, tester.getPassword(), "pass");

        step("Then Password exception is displayed");
        assertThat("Password exception is shown", message,
            containsString("New password - must be 8 or more characters long"));
    }

    @Test(groups = {"BVT"}, description = "VM-7668, Tester types invalid old password")
    public void testerPutsInvalidOldPassword() throws IOException {
        step("Given I attempt to change my password with incorrect old password");
        String message = motUI.profile.changePasswordExpectingErrorText(tester, "oh no", "pass");

        step("Then invalid old Password exception is displayed");
        assertThat("Password invalid old Password exception is shown", message,
            containsString("Current password - enter a valid password"));
    }

    @Test(groups = {"BVT"}, description = "VM-7668, Tester leaves empty fields and click submit")
    public void testerLeavesEmptyFields() throws Exception {
        step("Given I attempt to change my password with inputting any values");
        String message = motUI.profile.changePasswordExpectingErrorText(tester, " ", " ");

        step("Then Empty field exception is displayed");
        assertThat("Empty field exception is shown", message,
            containsString("Current password - enter your current password"));
    }
}
