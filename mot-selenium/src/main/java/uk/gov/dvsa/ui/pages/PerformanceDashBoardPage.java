package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;

public class PerformanceDashBoardPage extends Page {

    public static final String PATH = "/stats";
    private static final String PAGE_TITLE = "Your performance dashboard";

    @FindBy (id = "today-total-vehicles-tested") private WebElement testConductedLabel;
    @FindBy (id = "today-number-passed") private WebElement passedTestLabel;

    public PerformanceDashBoardPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public String getTestConductedText() {
        return testConductedLabel.getText();
    }

    public String getPassedTestText() {
        return passedTestLabel.getText();
    }
}
