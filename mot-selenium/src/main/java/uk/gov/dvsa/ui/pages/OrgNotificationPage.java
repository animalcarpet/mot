package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.nominations.AlreadyOrderedCardPage;

public class OrgNotificationPage extends Page implements Notification {

    public static final String path = "/notification/%s";
    private static final String NOTIFICATION_STATUS_ACCEPTED = "Nomination accepted";

    @FindBy(id = "action-organisation-nomination-accepted") private WebElement acceptButton;
    @FindBy(id = "action-organisation-nomination-rejected") private WebElement rejectButton;
    @FindBy(id = "action-archive") private WebElement archiveButton;
    @FindBy(id = "orderCard") private WebElement orderCardButton;
    @FindBy(id = "activateCard") private WebElement activateCardButton;
    @FindBy(id = "notification-decision") private WebElement confirmationText;
    @FindBy(id = "notification-content") private WebElement notificationInformation;


    private static final By NOMINATION_STATUS = By.xpath(".//h1/span[2]");

    private WebElement getNotificationStatusElement() {
        return driver.findElement(NOMINATION_STATUS);
    }

    public OrgNotificationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return true;
    }

    public void clickOrderCard() {
        if(PageInteractionHelper.isElementDisplayed(orderCardButton)) {
            orderCardButton.click();
        }
    }

    public String clickOrderCardExpectingAlreadyOrderedText() {
        if(PageInteractionHelper.isElementDisplayed(orderCardButton)) {
            orderCardButton.click();
        }

        return new AlreadyOrderedCardPage(driver).getBannerTitleText();
    }

    public void clickActivateCard() {
        if(PageInteractionHelper.isElementDisplayed(activateCardButton)) {
            activateCardButton.click();
        }
    }

    public String getConfirmationText() {
        if(PageInteractionHelper.isElementDisplayed(confirmationText)) {
            return confirmationText.getText();
        }

        return "";
    }

    @Override
    public String getNotificationText() {
        if(PageInteractionHelper.isElementDisplayed(notificationInformation)) {
            return notificationInformation.getText();
        }

        return "";
    }

    @Override
    public Notification rejectNomination() {
        rejectButton.click();

        return this;
    }

    @Override
    public Notification acceptNomination() {
        acceptButton.click();

        return this;
    }

    @Override
    public InboxNotificationPage archiveNomination() {
        archiveButton.click();

        return new InboxNotificationPage(driver);
    }

    public boolean isAcceptButtonDisplayed() {
        return PageInteractionHelper.isElementDisplayed(acceptButton);
    }

    public boolean isRejectButtonDisplayed() {
        return PageInteractionHelper.isElementDisplayed(rejectButton);
    }

    private String getNotificationStatusText() {
        return getNotificationStatusElement().getText();
    }

    public boolean isNotificationStatusAccepted() {
        return getNotificationStatusText().contains(NOTIFICATION_STATUS_ACCEPTED);
    }
}
