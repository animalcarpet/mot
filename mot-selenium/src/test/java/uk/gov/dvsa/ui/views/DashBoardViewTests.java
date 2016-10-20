package uk.gov.dvsa.ui.views;

import org.openqa.selenium.By;
import org.testng.annotations.Test;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.HomePage;

import static org.testng.Assert.assertFalse;

public class DashBoardViewTests extends DslTest {

    @Test(groups = {"Regression", "VM_9444"})
    public void doesNotDisplayRetestLinkWhenEnteredHomePage() throws Exception {

        pageNavigator.navigateToPage(motApi.user.createTester(siteData.createSite().getId()), HomePage.PATH, HomePage.class);

        boolean startMotRetestPossible = PageInteractionHelper.isElementPresent(By.id("action-start-mot-retest"));
        assertFalse(startMotRetestPossible);
    }
}
