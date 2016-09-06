package uk.gov.dvsa.data;

import com.jayway.restassured.response.Response;
import org.openqa.selenium.Cookie;
import uk.gov.dvsa.domain.service.FrontendService;

public class FrontendData extends FrontendService{

    public FrontendData() {}

    public Response downloadFileFromFrontend(String path, Cookie sessionCookie, Cookie tokenCookie){
        return downloadFile(path, sessionCookie, tokenCookie);
    }
}
