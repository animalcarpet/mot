package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class GenerateReportPage extends Page {
    public static final String PATH = "/finance-reports";
    private static final String PAGE_TITLE = "Generate a report";
    
    @FindBy(id="input_report_type") private WebElement reportType;
    @FindBy(id="generateReport") private WebElement generateReportButton;

    public GenerateReportPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }
    
    public GenerateReportPage selectReportType(String reportNumber) {
        FormDataHelper.selectFromDropDownByValue(reportType, reportNumber);
        return this;
    }
    
    public DownloadReportPage clickGenerateReportButton() {
        generateReportButton.click();
        return new DownloadReportPage(driver);
    }
}
