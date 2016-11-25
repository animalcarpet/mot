package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;

public class TesterTestLogPage extends TestLogPage {

    private static final String PAGE_TITLE = "Test logs of Tester";
    public static final String PATH = "/mot-test-log";

    @FindBy(id = "today") private WebElement todayLink;
    @FindBy(xpath = "(//*[@class='result-table__meta'])[3]") private WebElement testType;

    public TesterTestLogPage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public TesterTestLogPage clickTodayLink() {
        todayLink.click();
        return this;
    }

    public String getTestType() {
        return testType.getText();
    }
}
