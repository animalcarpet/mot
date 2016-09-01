package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.login.LoginPage;

public class GiveFeedbackPage extends Page {

    private static final String PAGE_TITLE = "You've successfully signed out";
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

    public LoginPage clickCancelAndReturn() {
        cancelAndReturnHomeLink.click();
        return MotPageFactory.newPage(driver, LoginPage.class);
    }

    public SurveyThankYouPage submitCompletedFeedback() {
        verySatisfiedOption.click();
        return submitSurvey();
    }

    public SurveyThankYouPage submitEmptyFeedback() {
        return submitSurvey();
    }

    private SurveyThankYouPage submitSurvey() {
        sendFeedback.click();
        return MotPageFactory.newPage(driver, SurveyThankYouPage.class);
    }

}
