package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.exception.ExpectedElementNotFoundOnPageException;

import java.util.List;

public class ArchiveNotificationPage extends Page {

    private static final String PAGE_TITLE = "Notifications";

    @FindBy(className = "c-tab-list__item-link") private List<WebElement> notificationList;
    @FindBy(id = "inbox-tab") private WebElement inboxTabLink;

    public ArchiveNotificationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public boolean hasNotification(String title) {
        try {
            getNotification(title);
            return true;
        } catch (ExpectedElementNotFoundOnPageException exception) {
            return false;
        }
    }

    private WebElement getNotification(String title) {
        for (WebElement element: notificationList) {
            if (element.getText().contains(title)) {
                return element;
            }
        }

        throw new ExpectedElementNotFoundOnPageException("Notification not found");
    }
}
