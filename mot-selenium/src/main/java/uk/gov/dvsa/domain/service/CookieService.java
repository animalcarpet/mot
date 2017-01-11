package uk.gov.dvsa.domain.service;

import com.jayway.restassured.RestAssured;
import com.jayway.restassured.response.Response;
import org.openqa.selenium.Cookie;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.framework.config.Configurator;

import java.io.IOException;
import java.util.*;

public class CookieService {

    public static Cookie generateOpenAmLoginCookie(User user) throws IOException {
        String token = new AuthService().createSessionTokenForUser(user);
        return new Cookie("iPlanetDirectoryPro", token, Configurator.domain(), "/", null);
    }
}
