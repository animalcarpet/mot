package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;


public class AddSiteAssessmentPage extends Page {

    public static final String path = "/vehicle-testing-station/%s/add-risk-assessment";
    private static final String PAGE_TITLE = "Vehicle Testing Station\nEnter site assessment";

    @FindBy(id = "site-assessment-score") private WebElement siteAssessmentScore;

    @FindBy(id = "user-is-not-assessortrue") private WebElement yesRadioButton;

    @FindBy(id = "user-is-not-assessorfalse") private WebElement noRadioButton;

    @FindBy(id = "dvsa-examiners-user-id") private WebElement dvsaId;

    @FindBy(id = "ae-representatives-full-name") private WebElement aeFullName;

    @FindBy(id = "ae-representatives-role") private WebElement aeRole;

    @FindBy(id = "ae-representatives-user-id") private WebElement aeId;

    @FindBy(id = "testers-user-id") private WebElement testerId;

    @FindBy(id = "date1-day") private WebElement dateDay;

    @FindBy(id = "date1-month") private WebElement dateMonth;

    @FindBy(id = "date1-year") private WebElement dateYear;

    @FindBy(id = "submit") private WebElement continueButton;

    @FindBy(id = "returnDashboard") private WebElement cancelButton;

    public AddSiteAssessmentPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public AddSiteAssessmentPage addSiteAssessmentScore(String score) {
        FormDataHelper.enterText(siteAssessmentScore, score);
        return this;
    }

    public String getSiteAssessmentScore(){
        return siteAssessmentScore.getText();
    }

    public AddSiteAssessmentPage enterAeFullName(String name) {
        FormDataHelper.enterText(aeFullName, name);
        return this;
    }

    public AddSiteAssessmentPage enterDvsaId(String id) {
        FormDataHelper.enterText(dvsaId, id);
        return this;
    }

    public AddSiteAssessmentPage enterAeRole(String role) {
        FormDataHelper.enterText(aeRole, role);
        return this;
    }

    public AddSiteAssessmentPage enterAeId(String id) {
        FormDataHelper.enterText(aeId, id);
        return this;
    }

    public AddSiteAssessmentPage enterTesterId(String id) {
        FormDataHelper.enterText(testerId, id);
        return this;
    }

    public AddSiteAssessmentPage enterDate(String day, String month, String year) {
        FormDataHelper.enterText(dateDay, day);
        FormDataHelper.enterText(dateMonth, month);
        FormDataHelper.enterText(dateYear, year);
        return this;
    }

    public SiteAssessmentSummaryPage clickContinueButton() {
        continueButton.click();
        return new SiteAssessmentSummaryPage(driver);
    }

    public VehicleTestingStationPage clickCancelButton() {
        cancelButton.click();
        return new VehicleTestingStationPage(driver);
    }

    public AddSiteAssessmentPage clickYesRadioButton(){
        yesRadioButton.click();
        return this;
    }

    public AddSiteAssessmentPage clickNoRadioButton(){
        noRadioButton.click();
        return this;
    }

}
