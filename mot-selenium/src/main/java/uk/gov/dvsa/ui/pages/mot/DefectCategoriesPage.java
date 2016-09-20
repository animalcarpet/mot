package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;

public class DefectCategoriesPage extends AbstractDefectsBasketPage {

    private static final String PAGE_TITLE = "Defect categories";

    @FindBy(id = "listContainer") private WebElement listContainer;
    @FindBy(id = "mot-test-defects__categories__add-manual-advisory") private WebElement addManualAdvisory;

    public DefectCategoriesPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public DefectsPage navigateToDefectCategory(String... defectCategories) {
        for (String value : defectCategories) {
            listContainer.findElement(By.linkText(value)).click();
        }
        return new DefectsPage(driver);
    }

    public boolean isCategoryDisplayed(String categoryToCheck, String... defectCategories) {
        for (String value : defectCategories) {
            listContainer.findElement(By.linkText(value)).click();
        }
        return PageInteractionHelper.isElementDisplayed(By.linkText(categoryToCheck));
    }

    public AddAManualAdvisoryPage navigateToAddManualAdvisory() {
        addManualAdvisory.click();
        return new AddAManualAdvisoryPage(driver);
    }
}