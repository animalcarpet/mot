package uk.gov.dvsa.ui.pages.mot.retest;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ReTestCompletePage extends Page {

    @FindBy(id = "back-to-home-link") private WebElement backToHomeLink;
    @FindBy (id = "refusal-certificate-item") private WebElement refusalMessage;
    @FindBy (id = "compareTestResults") private WebElement compareResultsButton;
    @FindBy (id = "reprintDialog") private WebElement reinspectionMsg;

    private static final String PAGE_TITLE = "MOT re-test complete";
    private static final String PAGE_TITLE_REINSPECTION = "MOT reinspection complete";
    private static final String SUCCESSFUL_REINFECTION_MSG = "Certificate generated successfully";

    public ReTestCompletePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE, PAGE_TITLE_REINSPECTION);
    }

    public void clickCompareResultsButton() {
        compareResultsButton.click();
    }

    public boolean verifyBackToHomeDisplayed(){
        return backToHomeLink.isDisplayed();
    }

    public boolean isRefusalMessageDisplayed() {
        return refusalMessage.getText().contains("Refusal certificate for ");
    }

    public boolean isReinspectionSuccessfull() {
        return reinspectionMsg.getText().contains(SUCCESSFUL_REINFECTION_MSG);
    }
}
