package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.exception.ExpectedElementNotFoundOnPageException;

import java.util.List;

public class InboxNotificationPage extends Page {

    public static final String PATH = "/notification/list";
    private static final String PAGE_TITLE = "Notifications";

    @FindBy(className = "c-tab-list__item-link") private List<WebElement> notificationList;
    @FindBy(id = "archive-tab") private WebElement archiveTabLink;

    public InboxNotificationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public int countNotifications() {
        return notificationList.size();
    }

    public void clickNotificationLink(String title) {
        getNotification(title).click();
    }

    private WebElement getNotification(String title) {
        for (WebElement element: notificationList) {
            if (element.getText().contains(title)) {
                return element;
            }
        }

        throw new ExpectedElementNotFoundOnPageException("Notification not found");
    }

    public ArchiveNotificationPage clickArchiveTab() {
        archiveTabLink.click();

        return new ArchiveNotificationPage(driver);
    }
}
