package uk.gov.dvsa.module.userprofile;

import uk.gov.dvsa.ui.pages.profile.ProfilePage;
import uk.gov.dvsa.ui.pages.profile.annualassessmentcertificates.AnnualAssessmentCertificatesIndexPage;

public class AnnualAssessmentCertificates {
    private ProfilePage profilePage;
    private AnnualAssessmentCertificatesIndexPage annualAssessmentCertificatesIndexPage;

    public AnnualAssessmentCertificates(ProfilePage profilePage) {
        this.profilePage = profilePage;
    }

    public AnnualAssessmentCertificatesIndexPage gotoAnnualAssessmentCertificatesIndexPage() {
        annualAssessmentCertificatesIndexPage = profilePage.clickAnnualAssessmentCertificatesLink();
        return annualAssessmentCertificatesIndexPage;
    }

    public AnnualAssessmentCertificatesIndexPage addAnnualAssessmentCertificate(String certificateNumber, int score, String day, String month, String year) {
        gotoAnnualAssessmentCertificatesIndexPage();
        annualAssessmentCertificatesIndexPage.clickAddGroupA()
                .fillDate(day, month, year)
                .fillCertificateNumber(certificateNumber)
                .fillScore(score)
                .submitAndGoToReviewPage()
                .confirmAndGoToIndexPage();

        return annualAssessmentCertificatesIndexPage;
    }

    public AnnualAssessmentCertificatesIndexPage editAnnualAssessmentCertificate(String oldCertificateNumber, String newCertificateNumber, int score, String day, String month, String year) {
        gotoAnnualAssessmentCertificatesIndexPage();
        annualAssessmentCertificatesIndexPage.clickEditGroupB(oldCertificateNumber)
                .fillDate(day, month, year)
                .fillCertificateNumber(newCertificateNumber)
                .fillScore(score)
                .submitAndGoToReviewPage()
                .confirmAndGoToIndexPage();

        return annualAssessmentCertificatesIndexPage;
    }

    public AnnualAssessmentCertificatesIndexPage removeAnnualAssessmentCertificateGroupB(String certificateNumber) {
        gotoAnnualAssessmentCertificatesIndexPage();
        annualAssessmentCertificatesIndexPage.clickRemoveButtonForGroupB(certificateNumber)
                .submitAndGoToIndexPage();

        return annualAssessmentCertificatesIndexPage;
    }

    public Boolean verifySavedAssessmentForGroupA(String certificateNumber, String date, String score) {
        Boolean certificateNumberCorrect = annualAssessmentCertificatesIndexPage.getFirstCertificateGroupANumber().equals(certificateNumber);
        Boolean dateCorrect = annualAssessmentCertificatesIndexPage.getFirstCertificateGroupADate().equals(date);
        Boolean scoreCorrect = annualAssessmentCertificatesIndexPage.getFirstCertificateGroupAScore().equals(score);

        return certificateNumberCorrect && dateCorrect && scoreCorrect;
    }

    public Boolean verifySavedAssessmentForGroupB(String certificateNumber, String date, String score) {
        Boolean certificateNumberCorrect = annualAssessmentCertificatesIndexPage.getFirstCertificateGroupBNumber().equals(certificateNumber);
        Boolean dateCorrect = annualAssessmentCertificatesIndexPage.getFirstCertificateGroupBDate().equals(date);
        Boolean scoreCorrect = annualAssessmentCertificatesIndexPage.getFirstCertificateGroupBScore().equals(score);

        return certificateNumberCorrect && dateCorrect && scoreCorrect;
    }

    public boolean verifyChangedAssessment(String successMessage) {
        return annualAssessmentCertificatesIndexPage.verifySuccessfulMessageForChangeGroupBCertificate(successMessage);
    }

    public boolean verifyRemovedAssessment() {
        return annualAssessmentCertificatesIndexPage.thereIsNoAnyCertificateTable();
    }
}
