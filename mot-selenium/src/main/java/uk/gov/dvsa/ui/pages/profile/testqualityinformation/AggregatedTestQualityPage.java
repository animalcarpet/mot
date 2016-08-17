package uk.gov.dvsa.ui.pages.profile.testqualityinformation;

import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.openqa.selenium.By;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AggregatedTestQualityPage extends Page {

    @FindBy(id = "return-link")
    private WebElement returnLink;
    @FindBy(id = "tqi-table-A")
    private WebElement tqiTableA;
    @FindBy(id = "tqi-table-B")
    private WebElement tqiTableB;
    @FindBy(css = "#tqi-table-A a")
    private WebElement viewGroupAFailures;
    @FindBy(css = "#tqi-table-B a")
    private WebElement viewGroupBFailures;
    @FindBy(css = ".lede")
    private WebElement secondaryTitle;

    private static final String SECONDARY_PAGE_TITLE = "Tests done at all associated sites";

    public AggregatedTestQualityPage(MotAppDriver driver) {
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

    public boolean isTableForGroupADisplayed() {
        return tqiTableA.isDisplayed();
    }

    public boolean isTableForGroupBDisplayed() {
        return tqiTableB.isDisplayed();
    }

    public int getTableForGroupARowCount() {
        return getTableRowCount(tqiTableA);
    }

    private Integer getTableRowCount(WebElement tqiTable) {
        try {
            return tqiTable.findElements(By.cssSelector("tbody tr")).size() - 1; // we substract 1 as it's the header row
        } catch (StaleElementReferenceException e) {
            return getTableRowCount(tqiTable);
        }
    }

    public int getTableForGroupBRowCount() {
        return getTableRowCount(tqiTableB);
    }

    public boolean isReturnLinkDisplayed() {
        return returnLink.isDisplayed();
    }

    public AggregatedTestQualityPage chooseMonth(DateTime date) {
        clickElement(By.id(DateTimeFormat.forPattern("MM/yyyy").print(date)));
        return new AggregatedTestQualityPage(driver);
    }

    public AggregatedComponentBreakdownPage clickGroupBFailures() {
        viewGroupBFailures.click();
        return new AggregatedComponentBreakdownPage(driver);
    }

    public AggregatedComponentBreakdownPage clickGroupAFailures() {
        viewGroupAFailures.click();
        return new AggregatedComponentBreakdownPage(driver);
    }
}
