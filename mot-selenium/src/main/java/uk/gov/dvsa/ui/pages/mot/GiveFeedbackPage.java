package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.Page;

public class GiveFeedbackPage extends Page {

    private static final String PAGE_TITLE = "Give feedback";
    @FindBy(id = "cancel-and-return") private WebElement cancelAndReturnHomeLink;
    @FindBy(id = "submitSurvey") private WebElement sendFeedback;
    @FindBy(id = "satisfactionRating5") private WebElement verySatisfiedOption;

    public GiveFeedbackPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public HomePage clickCancelAndReturnHome() {
        cancelAndReturnHomeLink.click();
        return MotPageFactory.newPage(driver, HomePage.class);
    }

    public <T extends Page> T submitFeedback(boolean isEmpty, Class<T> clazz) {
        if (isEmpty) {
            return clickSendFeedback(clazz);
        }
        verySatisfiedOption.click();
        return clickSendFeedback(clazz);
    }

    public <T extends Page> T clickSendFeedback(Class<T> clazz) {
        sendFeedback.click();
        return MotPageFactory.newPage(driver, clazz);
    }
}
