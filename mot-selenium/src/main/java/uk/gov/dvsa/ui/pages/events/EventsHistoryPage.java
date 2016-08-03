package uk.gov.dvsa.ui.pages.events;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class EventsHistoryPage extends Page {
    public static final String SITE_PATH = "/event/list/site/%s";
    public static final String AE_PATH = "/event/list/ae/%s";
    public static final String PATH = "/event/list/%s/%s";
    private static final String PAGE_TITLE = "Events history";

    @FindBy(id = "search") private WebElement searchInput;
    @FindBy(id = "listLogs_wrapper") private WebElement listOfEventsTable;
    @FindBy(partialLinkText = "Record an event") private WebElement recordNewEventLink;
    @FindBy(name = "submit") private WebElement applyButton;

    private String eventNames = "#listLogs a[href*='%s']";

    public EventsHistoryPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public final String getEventName(String aeId) {
        By selector = By.cssSelector(String.format(eventNames, aeId));
        PageInteractionHelper.refreshPage();
        return driver.findElement(selector).getText();
    }

    public EventsHistoryPage fillSearchInput(String searchString) {
        searchInput.sendKeys(searchString);
        return this;
    }

    public final boolean isListOfEventsDisplayed() {
        return listOfEventsTable.isDisplayed();
    }

    public final int getEventHistoryTableSize() {
        return driver.findElements(By.cssSelector("#listLogs tbody>tr")).size();
    }

    public CreateNewEventPage clickRecordNewEvent() {
        recordNewEventLink.click();
        return new CreateNewEventPage(driver);
    }

    public EventsHistoryPage clickApplyButton() {
        applyButton.click();
        return this;
    }
}
