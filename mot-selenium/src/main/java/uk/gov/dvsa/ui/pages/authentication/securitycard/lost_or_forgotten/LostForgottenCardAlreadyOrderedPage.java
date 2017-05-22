package uk.gov.dvsa.ui.pages.authentication.securitycard.lost_or_forgotten;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;

public class LostForgottenCardAlreadyOrderedPage extends AbstractLostForgottenPage {
   private static final String PAGE_TITLE = "Sign in without your security card";
   public static final String PATH = "/lost-or-forgotten-card/already-ordered";

    @FindBy(id = "continueToQuestionOne") private WebElement continueButton;

    public LostForgottenCardAlreadyOrderedPage(final MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public LostForgottenCardQuestionsPage continueToSecurityQuestionOnePage() {
        continueButton.click();
        return new LostForgottenCardQuestionsPage(driver);
    }
}
