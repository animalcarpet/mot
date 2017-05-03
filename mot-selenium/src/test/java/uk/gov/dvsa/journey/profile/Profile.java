package uk.gov.dvsa.journey.profile;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.journey.userprofile.*;
import uk.gov.dvsa.journey.userprofile.ChangeTelephone;
import uk.gov.dvsa.module.userprofile.AnnualAssessmentCertificates;
import uk.gov.dvsa.ui.pages.ChangePasswordFromProfilePage;
import uk.gov.dvsa.ui.pages.ChangeTelephoneDetailsPage;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.changedriverlicence.ChangeDrivingLicencePage;
import uk.gov.dvsa.ui.pages.changedriverlicence.RemoveDriverLicencePage;
import uk.gov.dvsa.ui.pages.changedriverlicence.ReviewDrivingLicencePage;
import uk.gov.dvsa.ui.pages.exception.PageInstanceNotFoundException;
import uk.gov.dvsa.ui.pages.profile.*;
import uk.gov.dvsa.ui.pages.profile.security.ChangeSecurityQuestionsPasswordPage;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

import java.io.IOException;

public class Profile {
    private PageNavigator pageNavigator;
    private ProfilePage profilePage;
    private ChangeName changeName;
    private ChangeAddress changeAddress;
    private ChangeQualificationDetails changeQualificationDetails;
    private ChangeTelephone changeTelephone;
    private AnnualAssessmentCertificates annualAssessmentCertificates;

    public Profile(final PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public ProfilePage viewYourProfile(final User user) throws IOException {
        profilePage = pageNavigator.navigateToPage(user, PersonProfilePage.PATH, PersonProfilePage.class);
        return profilePage;
    }

    public ProfilePage dvsaViewUserProfile(final User userViewingProfile, final User userProfileToView) throws IOException {
        String queryPath = String.format(UserProfilePage.PATH, userProfileToView.getId());
        profilePage = pageNavigator.navigateToPage(userViewingProfile, queryPath, UserProfilePage.class);

        return profilePage;
    }

    public ProfilePage tradeViewUserProfile(final User userViewingProfile, final User userProfileToView) throws IOException {
        profilePage = null;
        VehicleTestingStationPage vtsPage =
                pageNavigator.navigateToPage(userViewingProfile, HomePage.PATH, HomePage.class).selectRandomVts();

        profilePage = vtsPage.chooseAssignedToVtsUser(userProfileToView.getId());
        return profilePage;
    }

    public ProfilePage editAndSubmitLicenseDetails(String number, String countryId){
        return profilePage.clickChangeDrivingLicenceLink()
                .enterDriverLicenceNumber(number)
                .selectDlIssuingCountry(countryId)
                .clickSubmitDrivingLicenceButton(ReviewDrivingLicencePage.class)
                .clickChangeDrivingLicenceButton();
    }

    public ChangeDrivingLicencePage submitInvalidLicenseDetails(String number, String countryId){
        return profilePage.clickChangeDrivingLicenceLink()
                .enterDriverLicenceNumber(number)
                .selectDlIssuingCountry(countryId)
                .clickSubmitDrivingLicenceButton(ChangeDrivingLicencePage.class);
    }

    public ProfilePage removeLicense() {
        ChangeDrivingLicencePage changeDrivingLicencePage = profilePage.clickChangeDrivingLicenceLink();
        RemoveDriverLicencePage removeDriverLicencePage = changeDrivingLicencePage.clickRemoveDrivingLicenceLink();
        profilePage = removeDriverLicencePage.clickRemoveDrivingLicenceButton();

        return profilePage;
    }


    public ChangeTelephoneDetailsPage editTelephone(String number) {
        return profilePage.clickChangeTelephoneLink().fillTel(number);
    }

    public String editTelephoneWithInvalidInput(String number) {
        return profilePage.clickChangeTelephoneLink().fillTel(number)
                .submit()
                .getValidationMessage();
    }

    public void hackChangeLicenseUrl(User dvsaUserhacking, User dvsaUserBeingHacked) throws IOException {
        pageNavigator.navigateToPage(dvsaUserhacking,
                String.format(ChangeDrivingLicencePage.PATH, dvsaUserBeingHacked.getId()), ChangeDrivingLicencePage.class);
    }

    public ChangeSecurityQuestionsPasswordPage securityQuestionsPasswordPage(User user) {

        return new ChangeSecurityQuestionsPasswordPage(pageNavigator.getDriver());

    }

    public String changeDobWithInvalidValues(String day, String month, String year) {
        return new ChangeDOB(profilePage).changeDobWithInvalidValues(day, month, year);
    }

    public ProfilePage changeDateOfBirthTo(String dateOfBirth) {
        return new ChangeDOB(profilePage).changeDateOfBirthTo(dateOfBirth);
    }

    public ProfilePage changeYourEmailTo(String email) {
        return new ChangeEmail(profilePage).changeYourEmailTo(email);
    }

    public ProfilePage changeUserEmailAsDvsaTo(String email) {
        return new ChangeEmail(profilePage).changeUserEmailAsDvsaTo(email);
    }

    public String changeEmailWithInvalidInputs(String email, String confirmationEmail) {
        return new ChangeEmail(profilePage).changeEmailWithInvalidInputs(email, confirmationEmail);
    }

    public boolean isTesterQualificationStatusDisplayed() {
        return profilePage.isTesterQualificationStatusDisplayed();
    }

    public ProfilePage page(){
        if (profilePage == null) {
            throw new PageInstanceNotFoundException("Profile page wasn't instantiated");
        }
        return profilePage;
    }

    public ChangeName changeName() {
        if (changeName == null) {
            return this.changeName = new ChangeName(pageNavigator, page());
        }
        return changeName;
    }

    public ChangeAddress changeAddress() {
        if (changeAddress == null) {
            return this.changeAddress = new ChangeAddress(page());
        }
        return changeAddress;
    }

    public ChangeQualificationDetails qualificationDetails() {
        if (changeQualificationDetails == null) {
            return this.changeQualificationDetails = new ChangeQualificationDetails(page(), pageNavigator);
        }
        return changeQualificationDetails;
    }

    public ProfilePage changeYourTelephoneTo(String phoneNumber) {
        return new ChangeTelephone(pageNavigator, profilePage).changeYourTelephoneTo(phoneNumber);
    }

    public ProfilePage changeUserTelephoneAsDvsaTo(String phoneNumber) {
        return new ChangeTelephone(pageNavigator, profilePage).changeUserTelephoneAsDvsaTo(phoneNumber);
    }

    public ChangeTelephone changeTelephone() {
        if (changeTelephone == null) {
            return this.changeTelephone = new ChangeTelephone(pageNavigator, page());
        }
        return changeTelephone;
    }

    public AnnualAssessmentCertificates annualAssessmentCertificates() {
        if(annualAssessmentCertificates == null) {
            return this.annualAssessmentCertificates = new AnnualAssessmentCertificates(page());
        }

        return annualAssessmentCertificates;
    }

    public String changeSecurityQuestionsAndAnswer(User user) {
        return new ChangeSecurityQuestions(profilePage).change(user.getPassword());
    }

    public String changePasswordExpectingSuccessText(User user, String oldPassword, String newPassword) throws IOException {
        ChangePasswordFromProfilePage changePasswordPage = pageNavigator.gotoHomePage(user).clickYourProfile().clickChangePasswordLink();
        return changePasswordPage.changePasswordExpectingText(oldPassword,newPassword);
    }

    public String changePasswordExpectingErrorText(User user, String oldPassword,String newPassword) throws IOException {
        ChangePasswordFromProfilePage changePasswordPage =
            pageNavigator.navigateToPage(user, ChangePasswordFromProfilePage.PATH, ChangePasswordFromProfilePage.class);
        return changePasswordPage
            .enterOldPassword(oldPassword)
            .enterNewPassword(newPassword)
            .confirmNewPassword(newPassword = oldPassword.equals(newPassword) ? newPassword : newPassword + "wwe")
            .clickSubmitButton(ChangePasswordFromProfilePage.class).getErrorMessage();
    }
}
