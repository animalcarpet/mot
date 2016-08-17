package uk.gov.dvsa.domain.navigation;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.ConfigHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;
import uk.gov.dvsa.ui.pages.exception.PageInstanceNotFoundException;

import java.lang.reflect.Constructor;

public class MotPageFactory {

    public static <T extends Page> T newPage(MotAppDriver driver, Class<T> clazz) {
        return getNewPageInstance(driver, clazz);
    }

    private static <T> T getNewPageInstance(MotAppDriver driver, Class<T> clazz) {
        try {
            Constructor<T> constructor = clazz.getConstructor(MotAppDriver.class);
            constructor.setAccessible(true);
            return constructor.newInstance(driver);
        } catch (Exception e) {
            throw new PageInstanceNotFoundException(e.getCause().toString(), e);
        }
    }

    public static ProfilePage getProfilePageInstance(final ProfilePage newProfilePage, final ProfilePage oldProfilePage) {
        if(ConfigHelper.isNewPersonProfileEnabled()) {
            return newProfilePage;
        } else {
            return oldProfilePage;
        }
    }
}
