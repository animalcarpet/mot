package uk.gov.dvsa.ui.pages.profile.annualassessmentcertificates;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AnnualAssessmentCertificatesAddPage extends Page {

    @FindBy(id = "cert-number") private WebElement certificateNumber;
    @FindBy(id = "date-day") private WebElement dateDay;
    @FindBy(id = "date-month") private WebElement dateMonth;
    @FindBy(id = "date-year") private WebElement dateYear;
    @FindBy(id = "score") private WebElement score;
    @FindBy (id = "submit-button") private WebElement submit;


    private static final String PAGE_TITLE = "Add your group A assessment certificate";

    public AnnualAssessmentCertificatesAddPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }


    public AnnualAssessmentCertificatesAddPage fillDate(String day, String month, String year) {
        FormDataHelper.enterText(dateDay, day);
        FormDataHelper.enterText(dateMonth, month);
        FormDataHelper.enterText(dateYear, year);
        return this;
    }

    public AnnualAssessmentCertificatesAddPage fillCertificateNumber(String number) {
        FormDataHelper.enterText(certificateNumber, number);
        return this;
    }

    public AnnualAssessmentCertificatesReviewPage submitAndGoToReviewPage(){
        submit.click();
        return new AnnualAssessmentCertificatesReviewPage(driver);
    }

    public AnnualAssessmentCertificatesAddPage fillScore(int score) {
        FormDataHelper.enterText(this.score, String.valueOf(score));
        return this;
    }
}
