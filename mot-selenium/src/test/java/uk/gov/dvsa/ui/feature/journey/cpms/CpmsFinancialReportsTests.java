package uk.gov.dvsa.ui.feature.journey.cpms;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.cpms.DownloadReportPage;
import uk.gov.dvsa.ui.pages.cpms.GenerateReportPage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class CpmsFinancialReportsTests extends DslTest {
    
    private User financeUser;
    
    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        financeUser = motApi.user.createAFinanceUser("Finance", false);
    }
    
    @Test (enabled = false, groups = {"Regression"}, description = "SPMS-272 User requests Slot Balance report")
    public void userGeneratesReportSuccessfully() throws Exception {
        
        //Given I am logged as a Finance user and I am on Generate report page
        GenerateReportPage generateReportPage = pageNavigator.navigateToPage(financeUser, GenerateReportPage.PATH, GenerateReportPage.class);
        
        //When I select report type and Submit
        DownloadReportPage downloadReportPage = generateReportPage.selectReportType("CPMS82FA1F0C").clickGenerateReportButton();
        
        //Then The report should be created successfully
        assertThat(downloadReportPage.isBackToGenerateReportLinkDisplayed(), is(true));
    }
}
