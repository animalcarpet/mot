package uk.gov.dvsa.ui.pages.authorisedexaminer;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.vts.SiteTestQualityPage;

public class ServiceReportsPage extends Page {
    public static final String PATH = "/authorised-examiner/%s/test-quality-information";
    private static final String PAGE_TITLE = "Vehicle testing stations";

    @FindBy(id="View") private WebElement viewLink;
    @FindBy(id="return-link") private WebElement returnLink;


    public ServiceReportsPage(MotAppDriver driver) {
        super(driver);
    }

    @Override protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public SiteTestQualityPage clickViewButton()
    {
        viewLink.click();
        return MotPageFactory.newPage(driver, SiteTestQualityPage.class);
    }

    public AedmAuthorisedExaminerViewPage clickReturnButton()
    {
        returnLink.click();
        return MotPageFactory.newPage(driver, AedmAuthorisedExaminerViewPage.class);
    }
}
