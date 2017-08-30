package uk.gov.dvsa.ui.pages.profile.testqualityinformation;

import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.openqa.selenium.By;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.vts.SiteTestQualityPage;
import uk.gov.dvsa.ui.pages.vts.UserTestQualityPage;

import java.util.List;

public class AggregatedTestQualityPage extends Page {

    @FindBy(id = "return-link")
    private WebElement returnLink;
    @FindBy(id = "tqi-table-A")
    private WebElement tqiTableA;
    @FindBy(id = "tqi-table-B")
    private WebElement tqiTableB;
    @FindBy(id = "view-components-A")
    private WebElement viewGroupAFailures;
    @FindBy(id = "view-components-B")
    private WebElement viewGroupBFailures;
    @FindBy(css = "#tqi-table-A a")
    private WebElement viewFirstSiteInGroupAFailures;
    @FindBy(css = ".lede")
    private WebElement secondaryTitle;
    @FindBy(id="last1Month")private WebElement last1MonthRadio;
    @FindBy(id="last3Months")private WebElement last3MonthsRadio;
    @FindBy(css="input[value='Update results']")private WebElement updateMonthRangeButton;

    private static final String PAGE_TITLE = "Test quality information";

    public AggregatedTestQualityPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
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
            return tqiTable.findElements(By.cssSelector("tbody tr")).size();
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

    public AggregatedTestQualityPage choose1MonthRange() {
        last1MonthRadio.click();
        updateMonthRangeButton.click();
        return new AggregatedTestQualityPage(driver);
    }

    public AggregatedTestQualityPage choose3MonthRange() {
        last3MonthsRadio.click();
        updateMonthRangeButton.click();
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

    public TesterAtSiteComponentBreakdownPage clickFirstSiteInGroupAFailures()
    {
        viewFirstSiteInGroupAFailures.click();
        return new TesterAtSiteComponentBreakdownPage(driver);
    }

    public AggregatedComponentBreakdownPage goToSiteComponentBreakdownPageForGroupA(String siteName){
        return goToSiteComponentBreakdownPage(siteName, "A");
    }

    public AggregatedComponentBreakdownPage goToSiteComponentBreakdownPageForGroupB(String siteName){
        return goToSiteComponentBreakdownPage(siteName, "B");
    }

    public AggregatedComponentBreakdownPage goToSiteComponentBreakdownPage(String siteName, String group){
        List<WebElement> tableRows = driver.findElements(By.cssSelector("table#tqi-table-" + group + " tbody tr"));

        for (WebElement row: tableRows){
            if(row.getText().contains(siteName)) {
                row.findElement(By.linkText("View")).click();
                return new AggregatedComponentBreakdownPage(driver);
            }
        }
        return null;
    }
}
