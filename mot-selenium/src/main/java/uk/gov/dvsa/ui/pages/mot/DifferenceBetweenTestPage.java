package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.helper.RandomDataGenerator;
import uk.gov.dvsa.helper.enums.Comparison;
import uk.gov.dvsa.ui.pages.AssessmentDetailsConfirmationPage;
import uk.gov.dvsa.ui.pages.Page;

import java.util.List;

public class DifferenceBetweenTestPage extends Page{
    private static final String PAGE_TITLE = "Differences found between tests";
    private By scoreDropdown = By.cssSelector("[id*='-NT-FAIL-score']");
    private By justificationBox = By.cssSelector("[id*='-NT-FAIL-justification']");
    private By totalScore = By.id("totalScore");
    private By caseOutcome = By.id("caseOutcome");
    private By recordAssessment = By.id("record_assessment_button");

    public DifferenceBetweenTestPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public void setScoreByFailureName(Comparison comparison) {
        if(PageInteractionHelper.isElementDisplayed(driver.findElement(scoreDropdown))) {
            FormDataHelper
                    .selectFromDropDownByVisibleText(driver.findElement(scoreDropdown), comparison.toString());
        }
    }

    public String indicativeCaseOutcome() {
       return FormDataHelper.getSelectedTextFromDropdown(driver.findElement(caseOutcome));
    }

    public String totalScore() {
        return driver.findElement(totalScore).getText();
    }

    public AssessmentDetailsConfirmationPage recordAssessment(){
        driver.findElement(recordAssessment).click();
        return new AssessmentDetailsConfirmationPage(driver);
    }

    public DifferenceBetweenTestPage completeJustificationWithRandomValues(){
        List<WebElement> justificationBoxes = driver.findElements(justificationBox);
        if(justificationBoxes.size() > 0) {
            for (WebElement e : justificationBoxes) {
                FormDataHelper.enterText(e, RandomDataGenerator.generateRandomString());
            }
        }

        return this;
    }
}
