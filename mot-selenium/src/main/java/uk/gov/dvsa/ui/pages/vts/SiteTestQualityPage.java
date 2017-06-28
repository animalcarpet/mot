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
import java.util.Calendar;
import java.util.List;

public class SiteTestQualityPage extends Page {
    public static final String PATH = "/vehicle-testing-station/%s/test-quality";
    private static final String PAGE_TITLE = "Test Quality information";
    private String pageTertiaryTitle = "Tests done in %s";

    @FindBy(id="return-link")private WebElement returnLink;
    @FindBy(id="tqi-table-A")private WebElement tqiTableA;
    @FindBy(id="tqi-table-B")private WebElement tqiTableB;
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
        return tqiTableA.findElements(By.cssSelector("tbody tr")).size() - 1; // we subtract 1 as it's the header row
    }

    public int getTableForGroupBRowCount()
    {
        return tqiTableB.findElements(By.cssSelector("tbody tr")).size() - 1;
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
        List<WebElement> tableRows = driver.findElements(By.cssSelector("table#tqi-table-" + group + "  td  a"));

        for (WebElement row: tableRows){
            if(row.getText().contains(userName)) {
                row.click();
                return MotPageFactory.newPage(driver, UserTestQualityPage.class);
            }
        }
        return null;
    }

    public SiteTestQualityPage chooseMonth(DateTime date) {
        DateTimeFormatter dateFormat = DateTimeFormat.forPattern("MM/yyyy");
        driver.findElement(By.id(dateFormat.print(date))).click();
        return new SiteTestQualityPage(driver);
    }

    public boolean isThirteenMonthsAgoLinkPresent() {
        Calendar calendar = Calendar.getInstance();
        calendar.add(Calendar.MONTH, (-13));
        calendar.set(Calendar.DATE, 1);

        DateTime thirteenMonthsAgo = new DateTime(calendar.getTime());

        return PageInteractionHelper.isElementDisplayed(By.id(getDateAsString(thirteenMonthsAgo, "MM/yyyy")));
    }

    public SiteTestQualityPage waitUntilPageTertiaryTitleWillShowDate(DateTime dateTime)
    {
        pageTertiaryTitle = String.format(pageTertiaryTitle, getDateAsString(dateTime, "MMMM yyyy"));
        PageInteractionHelper.waitForTextToBePresentInElement(driver.findElement(By.tagName("h2")), pageTertiaryTitle, 15);
        return this;
    }

    private String getDateAsString(DateTime dateTime, String format) {
        DateTimeFormatter dateFormat = DateTimeFormat.forPattern(format);
        return dateFormat.print(dateTime);
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
