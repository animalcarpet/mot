package uk.gov.dvsa.journey.userprofile;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;
import uk.gov.dvsa.ui.pages.profile.qualificationdetails.QualificationDetailsConfirmationPage;
import uk.gov.dvsa.ui.pages.profile.qualificationdetails.QualificationDetailsPage;

import java.io.IOException;

public class ChangeQualificationDetails {
    private ProfilePage profilePage;
    private QualificationDetailsPage qualificationDetailsPage;
    private PageNavigator pageNavigator;

    public ChangeQualificationDetails(ProfilePage profilePage, PageNavigator pageNavigator) {
        this.profilePage = profilePage;
        this.pageNavigator = pageNavigator;
    }

    public QualificationDetailsConfirmationPage addQualificationDetailsForGroupA(String certificateNumber, String vtsId, String day, String month, String year) {
        goToQualificationDetailsPage();
        return qualificationDetailsPage.clickAddGroupADetails()
                .fillCertificateNumber(certificateNumber)
                .fillVtsId(vtsId)
                .fillDate(day, month, year)
                .submitAndGoToReviewPage()
                .submitNewQualificationDetails();
    }

    public QualificationDetailsPage changeQualificationDetailsForGroupA(String certificateNumber, String vtsId, String day, String month, String year) {
        goToQualificationDetailsPage();
        qualificationDetailsPage.clickChangeGroupADetails()
            .fillCertificateNumber(certificateNumber)
            .fillVtsId(vtsId)
            .fillDate(day, month, year)
            .submitAndGoToReviewPage()
            .submitEditQualificationDetailsChanges();

        return qualificationDetailsPage;
    }

    public QualificationDetailsPage removeQualificationDetailsForGroupB(String certificateNumber) {
        qualificationDetailsPage = profilePage.clickQualificationDetailsLink();

        qualificationDetailsPage
                .clickRemoveGroupBDetails()
                .submitConfirmChanges();

        return qualificationDetailsPage;
    }

    public boolean verifyCertificateAddedForGroupA(String certificateNumber, String newDate) {
        Boolean certificateNumberIsCorrect = qualificationDetailsPage.getCertificateGroupANumber().equals(certificateNumber);
        Boolean dateIsCorrect = qualificationDetailsPage.getCertificateGroupADate().contains(newDate);

        return certificateNumberIsCorrect && dateIsCorrect;
    }

    public boolean verifyDetailsChangedForGroupA(String certificateNumber, String newDate) {
        Boolean certificateNumberIsCorrect = qualificationDetailsPage.getCertificateGroupANumber().equals(certificateNumber);
        Boolean dateIsCorrect = qualificationDetailsPage.getCertificateGroupADate().contains(newDate);
        Boolean successMessageIsDisplayed = qualificationDetailsPage.validationMessageSuccessIsDisplayed();

        return certificateNumberIsCorrect && dateIsCorrect && successMessageIsDisplayed;
    }

    public QualificationDetailsPage goToQualificationDetailsPage() {
        qualificationDetailsPage = profilePage.clickQualificationDetailsLink();
        return qualificationDetailsPage;
    }

    public int countUserCertificates() {
        int groupA = qualificationDetailsPage.getCertificateGroupANumber().isEmpty() ? 0 : 1;
        int groupB = qualificationDetailsPage.getCertificateGroupBNumber().isEmpty() ? 0 : 1;
        return groupA + groupB;
    }

    public boolean verifyDetailsAfterRemovedGroupBCertificate() {
        return qualificationDetailsPage.getQualificationStatusForGroupB().equals("Not Applied");
    }

    public QualificationDetailsConfirmationPage confirmationPage(User user) throws IOException {
        return pageNavigator.navigateToPage(user, QualificationDetailsConfirmationPage.PATH,
            QualificationDetailsConfirmationPage.class);
    }

    public boolean isOrderCardLinkDisplayed(){
        return new QualificationDetailsConfirmationPage(pageNavigator.getDriver()).isOrderCardLinkDisplayed();
    }
}
