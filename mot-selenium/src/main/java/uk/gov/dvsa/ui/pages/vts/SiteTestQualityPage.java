package uk.gov.dvsa.ui.pages.vts;


import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.authorisedexaminer.ServiceReportsPage;

import java.net.MalformedURLException;
import java.net.URL;
import java.util.List;

public class SiteTestQualityPage extends Page {
    public static final String PATH = "/vehicle-testing-station/%s/test-quality";
    private static final String PAGE_TITLE = "Test Quality information";
    private String pageTertiaryTitle = "Tests done in %s";

    @FindBy(id="return-link")private WebElement returnLink;
    @FindBy(id="tqi-table-A")private WebElement tqiTableA;
    @FindBy(id="tqi-table-B")private WebElement tqiTableB;
    @FindBy(id="last1Month")private WebElement last1MonthRadio;
    @FindBy(id="last3Months")private WebElement last3MonthsRadio;
    @FindBy(css="input[value='Update results']")private WebElement updateMonthRangeButton;
    @FindBy(id="site-tqi-csv-downaload-group-A")private WebElement tqiCsvDownloadGroupA;
    @FindBy(id="site-tqi-csv-downaload-group-B")private WebElement tqiCsvDownloadGroupB;

    public SiteTestQualityPage(MotAppDriver driver) {
        super(driver);
    }

    @Override protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public boolean isTableForGroupADisplayed()
    {
        return tqiTableA.isDisplayed();
    }

    public boolean isTableForGroupBDisplayed()
    {
        return tqiTableB.isDisplayed();
    }

    public int getTableForGroupARowCount()
    {
        return tqiTableA.findElements(By.cssSelector("tbody tr")).size(); // we subtract 1 as it's the header row
    }

    public int getTableForGroupBRowCount()
    {
        return tqiTableB.findElements(By.cssSelector("tbody tr")).size();
    }

    public boolean isReturnLinkDisplayed()
    {
        return returnLink.isDisplayed();
    }

    public UserTestQualityPage goToUserTestQualityPageForGroupA(String userName){
        return goToUserTestQualityPage(userName, "A");
    }

    public UserTestQualityPage goToUserTestQualityPageForGroupB(String userName){
        return goToUserTestQualityPage(userName, "B");
    }

    public UserTestQualityPage goToUserTestQualityPage(String userName, String group){
        List<WebElement> tableRows = driver.findElements(By.cssSelector("table#tqi-table-" + group + "  tr"));

        for (WebElement row: tableRows){
            if(row.getText().contains(userName)) {
                row.findElement(By.linkText("View")).click();
                return MotPageFactory.newPage(driver, UserTestQualityPage.class);
            }
        }
        return null;
    }

    public SiteTestQualityPage choose1MonthRange() {
        last1MonthRadio.click();
        updateMonthRangeButton.click();
        return new SiteTestQualityPage(driver);
    }

    public SiteTestQualityPage choose3MonthRange() {
        last3MonthsRadio.click();
        updateMonthRangeButton.click();
        return new SiteTestQualityPage(driver);
    }

    public SiteTestQualityPage waitUntilPageTertiaryTitleWillShowTitleForRange(int monthRange)
    {
        PageInteractionHelper.waitForTextToBePresentInElement(
                driver.findElement(By.tagName("h2")),
                getHeaderForMonthRange(monthRange),
                15
        );

        return this;
    }

    private String getDateAsString(DateTime dateTime, String format) {
        DateTimeFormatter dateFormat = DateTimeFormat.forPattern(format);
        return dateFormat.print(dateTime);
    }

    private String getHeaderForMonthRange(int monthRange) {
        DateTime startMonth = DateTime.now().dayOfMonth().withMinimumValue().minusMonths(monthRange);
        if(monthRange == 1) {
            return String.format(pageTertiaryTitle, startMonth.toString("MMMM yyyy"));
        }

        DateTime endMonth = DateTime.now().dayOfMonth().withMinimumValue().minusMonths(1);

        String startMonthWording = areDatesInTheSameYear(startMonth, endMonth)
                ? startMonth.toString("MMMM")
                : startMonth.toString("MMMM yyyy");

        return String.format(pageTertiaryTitle, startMonthWording + " to " + endMonth.toString("MMMM yyyy"));
    }

    private boolean areDatesInTheSameYear(DateTime startDate, DateTime endDate) {
        return startDate.year().equals(endDate.year());
    }

    public ServiceReportsPage clickReturnButtonToAEPage()
    {
        returnLink.click();
        return MotPageFactory.newPage(driver, ServiceReportsPage.class);
    }

    public String getCsvDownloadLinkForGroupA() throws MalformedURLException {
        return new URL(tqiCsvDownloadGroupA.getAttribute("href")).getPath();
    }

    public String getCsvDownloadLinkForGroupB() throws MalformedURLException {
        return new URL(tqiCsvDownloadGroupB.getAttribute("href")).getPath();
    }
}
