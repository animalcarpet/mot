package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;

public class DefectsPage extends AbstractDefectsBasketPage {

    private static final String PAGE_TITLE = "Defects";

    @FindBy(css = "#defects-list .defect") private WebElement defects;
    @FindBy(id = "submit-defect") private WebElement addDefect;
    @FindBy(css = "nav.content-navigation a.button") private WebElement finishAndReturnToMOTTestButton;
    @FindBy(css = "nav.content-navigation ul li a") private WebElement returnToDefectCategoriesLink;

    public DefectsPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public boolean defectsAreDisplayed() {
        return defects.isDisplayed();
    }

    public DefectsPage navigateToAddDefectPage(Defect defect) {
        WebElement reasonForRejection = defects.findElement(By.xpath(String.format(
                "//*[contains(text(), '%s')]/ancestor::li[contains(@class, 'defect')]", defect.getDefectName())));
        reasonForRejection.findElement(By.linkText(defect.getDefectType())).click();
        return this;
    }

    public DefectsPage clickAddDefectButton() {
        addDefect.click();
        return this;
    }

    public TestResultsEntryNewPage clickFinishAndReturnButton() {
        finishAndReturnToMOTTestButton.click();
        return new TestResultsEntryNewPage(driver);
    }

    public DefectCategoriesPage clickReturnToDefectCategoriesLink() {
        returnToDefectCategoriesLink.click();
        return new DefectCategoriesPage(driver);
    }
}