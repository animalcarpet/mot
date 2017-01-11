package uk.gov.dvsa.ui.feature.journey;

import java.io.IOException;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.BaseTest;
import uk.gov.dvsa.ui.pages.cpms.DownloadReportPage;
import uk.gov.dvsa.ui.pages.cpms.GenerateReportPage;
import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class CpmsFinancialReportsTest extends BaseTest {
    
    private User financeUser;
    
    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        
        financeUser = userData.createAFinanceUser("Finance", false);
    }
    
    @Test (groups = {"BVT", "Regression"}, description = "SPMS-272 User requests Slot Balance report")
    public void userGeneratesReportSuccessfully() throws IOException {
        
        //Given I am on Generate report page
        GenerateReportPage generateReportPage = pageNavigator.goToGenerateReportPage(financeUser);
        
        //When I select report type and Submit
        DownloadReportPage downloadReportPage = generateReportPage.selectReportType("CPMS82FA1F0C").clickGenerateReportButton();
        
        //Then The report should be created successfully
        assertThat(downloadReportPage.isBackToGenerateReportLinkDisplayed(), is(true));
    }  
    
}
