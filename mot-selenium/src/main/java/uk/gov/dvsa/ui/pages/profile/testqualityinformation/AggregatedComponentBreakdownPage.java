package uk.gov.dvsa.ui.pages.profile.testqualityinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AggregatedComponentBreakdownPage extends Page {

    @FindBy(id = "return-link")
    private WebElement returnLink;
    @FindBy(id = "test-count")
    private WebElement testCount;
    @FindBy(id = "tqi-table-B")
    private WebElement tqiTableB;

    private static final String PAGE_TITLE = "Test quality information";

    public AggregatedComponentBreakdownPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public Integer getTestCount() {
        return Integer.parseInt(testCount.getText());
    }

    public boolean isReturnLinkDisplayed() {
        return returnLink.isDisplayed();
    }

}
