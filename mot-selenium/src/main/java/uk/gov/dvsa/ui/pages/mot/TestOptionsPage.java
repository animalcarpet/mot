package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;

public class TestOptionsPage extends OptionsPage {
    private static final String PAGE_TITLE = "test started";

    @FindBy(id = "enter-test-results") private WebElement enterTestResultsButton;

    public TestOptionsPage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public TestResultsEntryNewPage clickEnterTestResultsButton() {
        enterTestResultsButton.click();
        return new TestResultsEntryNewPage(driver);
    }
}
