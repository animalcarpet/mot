package uk.gov.dvsa.domain.service;

import org.openqa.selenium.Cookie;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.framework.config.Configurator;
import java.io.IOException;

public class CookieService {

    public static final String TOKEN_COOKIE_NAME = "iPlanetDirectoryPro";
    public static final String SESSION_COOKIE_NAME = "PHPSESSID";

    public static Cookie generateOpenAmLoginCookie(User user) throws IOException {
        String token = new AuthService().createSessionTokenForUser(user);
        return new Cookie(TOKEN_COOKIE_NAME, token, Configurator.domain(), "/", null);
    }
}