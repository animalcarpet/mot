package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.Page;

public class TestCompletePage extends Page {

    private static final String PAGE_TITLE = "MOT test complete";
    private static final String PAGE_TITLE_REINSPECTION = "MOT reinspection complete";
    private static final String PAGE_TITLE_NON_MOT = "Non-MOT test finished successfully";

    @FindBy (id = "back-to-home-link") private WebElement returnToHomepageLink;
    @FindBy (linkText = "Continue to home") private WebElement continueToHomeLink;
    @FindBy (id = "refusal-certificate-item") private WebElement refusalMessage;
    @FindBy (id = "compareTestResults") private WebElement compareResultsButton;
    @FindBy (id = "reprintDialog") private WebElement reinspectionMsg;
    @FindBy (id = "reprint-certificate") private WebElement printDocumentButton;
    @FindBy (className = "container") private WebElement testSummaryMessage;
    @FindBy (id = "logout") private WebElement signOutLink;

    public TestCompletePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE, PAGE_TITLE_REINSPECTION, PAGE_TITLE_NON_MOT);
    }

    public DifferenceBetweenTestPage clickCompareResultsButton() {
        compareResultsButton.click();
        return new DifferenceBetweenTestPage(driver);
    }

    public boolean isReturnToHomepageLinkDisplayed(){
        return returnToHomepageLink.isDisplayed();
    }

    public HomePage clickReturnToHomepageLink() {
        continueToHomeLink.click();
        return new HomePage(driver);
    }

    public GiveFeedbackPage clickSignOutLink() {
        signOutLink.click();
        return MotPageFactory.newPage(driver, GiveFeedbackPage.class);
    }

    public boolean isPrintDocumentButtonDisplayed(){
        return PageInteractionHelper.isElementDisplayed(printDocumentButton);
    }
}
