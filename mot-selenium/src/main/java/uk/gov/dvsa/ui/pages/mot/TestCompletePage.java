package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class TestCompletePage extends Page {

    @FindBy (id = "back-to-home-link") private WebElement backToHomeLink;
    @FindBy (id = "refusal-certificate-item") private WebElement refusalMessage;
    @FindBy (id = "compareTestResults") private WebElement compareResultsButton;
    @FindBy (id = "reprintDialog") private WebElement reinspectionMsg;
    @FindBy (id = "reprint-certificate") private WebElement printDocumentButton;
    @FindBy (className = "container") private WebElement testSummaryMessage;
    @FindBy (id = "logout") private WebElement signOutLink;

    private static final String PAGE_TITLE = "MOT test complete";
    private static final String PAGE_TITLE_REINSPECTION = "MOT reinspection complete";
    private static final String SUCCESSFULL_REINSPECTION_MSG = "Certificate generated successfully";

    public TestCompletePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE, PAGE_TITLE_REINSPECTION);
    }

    public DifferenceBetweenTestPage clickCompareResultsButton() {
        compareResultsButton.click();
        return new DifferenceBetweenTestPage(driver);
    }

    public boolean verifyBackToHomeLinkDisplayed(){
        return backToHomeLink.isDisplayed();
    }

    public GiveFeedbackPage clickBackHomeLink() {
        backToHomeLink.click();
        return MotPageFactory.newPage(driver, GiveFeedbackPage.class);
    }

    public GiveFeedbackPage clickSignOutLink() {
        signOutLink.click();
        return MotPageFactory.newPage(driver, GiveFeedbackPage.class);
    }

    public boolean isReinspectionSuccessfull() {
        return reinspectionMsg.getText().contains(SUCCESSFULL_REINSPECTION_MSG);
    }

    public String getTestSummaryMessageText() {
        return testSummaryMessage.getText();
    }


    public boolean isPrintDocumentButtonDisplayed(){
        return PageInteractionHelper.isElementDisplayed(printDocumentButton);
    }
}
