package uk.gov.dvsa.ui.pages.profile.testqualityinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AggregatedComponentBreakdownPage extends Page {

    @FindBy(css = ".content-header__tertiary")
    private WebElement secondaryTitle;
    @FindBy(id = "return-link")
    private WebElement returnLink;
    @FindBy(id = "tester-test-count")
    private WebElement testCount;
    @FindBy(id = "tqi-table-B")
    private WebElement tqiTableB;

    private static final String SECONDARY_PAGE_TITLE = "Failures by category at all associated sites";

    public AggregatedComponentBreakdownPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getSecondaryTitle(), SECONDARY_PAGE_TITLE);
    }

    private String getSecondaryTitle() {
        return secondaryTitle.getText();
    }

    public Integer getTestCount() {
        return Integer.parseInt(testCount.getText());
    }

    public boolean isReturnLinkDisplayed() {
        return returnLink.isDisplayed();
    }

}
