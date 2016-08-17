package uk.gov.dvsa.ui.pages.mot;

import org.hamcrest.core.Is;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

import static org.hamcrest.MatcherAssert.assertThat;

public class RemoveDefectPage extends Page {

    private static final String PAGE_TITLE = "Remove ";
    private static final String BREADCRUMB_TEXT = "Remove a ";

    private String defectType;

    @FindBy(className = "button-warning") private WebElement removeDefectButton;
    @FindBy(className = "back-to-open-list") private WebElement cancelAndReturnLink;
    @FindBy(id = "global-breadcrumb") private WebElement globalBreadcrumb;

    public RemoveDefectPage(MotAppDriver driver, String defectType) {
        super(driver);
        this.defectType = defectType;
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        //Check breadcrumb
        assertThat(globalBreadcrumb.getText().contains(BREADCRUMB_TEXT + defectType), Is.is(true));

        //Check remove button
        assertThat(removeDefectButton.getText().contains(PAGE_TITLE + defectType), Is.is(true));

        //Check page title
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE + defectType);
    }

    public <T extends Page> T cancelAndReturnToPage(Class<T> returnPage) {
        cancelAndReturnLink.click();
        return MotPageFactory.newPage(driver, returnPage);
    }

    public <T extends Page> T removeDefectAndReturnToPage(Class<T> returnPage) {
        removeDefectButton.click();
        return MotPageFactory.newPage(driver, returnPage);
    }
}