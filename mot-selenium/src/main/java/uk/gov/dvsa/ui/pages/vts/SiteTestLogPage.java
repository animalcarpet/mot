package uk.gov.dvsa.ui.pages.vts;

import org.joda.time.DateTime;
import org.openqa.selenium.By;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.enums.DateRangeFilter;
import uk.gov.dvsa.ui.pages.mot.TestLogPage;

import java.util.List;

public class SiteTestLogPage extends TestLogPage {
    private static final String PAGE_TITLE = "Test logs of Vehicle Testing Station";
    public static final String PATH = "/vehicle-testing-station/%s/mot-test-log";

    private By dataTable = By.cssSelector("#dataTable>tbody>tr");
    @FindBy(id="btn_search")private WebElement updateResultsButton;

    @FindBy(id="navigation-link-")private WebElement returnToVtsLink;
    @FindBy(id="lastWeek(Mon-Sun)")private WebElement lastWeekLink;
    @FindBy(xpath="//*[contains(text(),'Last week (Mon-Sun)')]")private WebElement lastWeekSelectedOption;
    @FindBy(id="today")private WebElement todayLink;
    @FindBy(id="lastMonth")private WebElement lastMonthLink;

    @FindBy(id="dateFrom-Day")private WebElement dateFromDay;
    @FindBy(id="dateFrom-Month")private WebElement dateFromMonth;
    @FindBy(id="dateFrom-Year")private WebElement dateFromYear;

    @FindBy(id="dateTo-Day")private WebElement dateToDay;
    @FindBy(id="dateTo-Month")private WebElement dateToMonth;
    @FindBy(id="dateTo-Year")private WebElement dateToYear;

    public SiteTestLogPage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public boolean isDataTableDisplayed(){
        return driver.findElement(dataTable).isDisplayed();
    }

    public VehicleTestingStationPage returnToVts(){
        returnToVtsLink.click();
        return new VehicleTestingStationPage(driver);
    }

    public boolean isSelected(DateRangeFilter filter){
       return checkNoSuchElementErrorIsThrownAndReturnTrue(filter);
    }

    public void enterCustomDateRange(DateTime firstDate, DateTime secondDate){
        FormDataHelper.enterText(dateFromDay, secondDate.dayOfMonth().getAsString());
        FormDataHelper.enterText(dateFromMonth, secondDate.monthOfYear().getAsString());
        FormDataHelper.enterText(dateFromYear, secondDate.year().getAsString());

        FormDataHelper.enterText(dateToDay, firstDate.dayOfMonth().getAsString());
        FormDataHelper.enterText(dateToMonth, firstDate.monthOfYear().getAsString());
        FormDataHelper.enterText(dateToYear, firstDate.year().getAsString());

        updateResultsButton.click();
    }

    public int getNumberOfRows(){
        List<WebElement> rows = driver.findElements(dataTable);
        return rows.size();
    }

    private boolean checkNoSuchElementErrorIsThrownAndReturnTrue(DateRangeFilter filter) {
        try {
            switch (filter) {
                case LAST_WEEK:
                    return lastWeekSelectedOption.isEnabled();

                case LAST_MONTH:
                    return lastMonthLink.getAttribute("href").isEmpty();

                case TODAY:
                    return todayLink.getAttribute("href").isEmpty();

                default:
                    return todayLink.getAttribute("href").isEmpty();
            }
        } catch (NoSuchElementException ex) {
            return true;
        }
    }
}
