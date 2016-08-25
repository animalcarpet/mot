package uk.gov.dvsa.framework.config.webdriver;

import org.apache.commons.io.FileUtils;
import org.openqa.selenium.OutputType;
import org.openqa.selenium.remote.RemoteWebDriver;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.helper.Utilities.Logger;
import java.io.File;
import uk.gov.dvsa.helper.Utilities.Logger;
import java.util.HashMap;
import java.util.Map;

public abstract class MotAppDriver implements MotWebDriver {
    protected RemoteWebDriver remoteWebDriver;
    private User user = null;
    private Map<String, Boolean> userSessionMap = new HashMap<>();
    private String baseUrl = "";

    public MotAppDriver(RemoteWebDriver remoteWebDriver) {
        this.remoteWebDriver = remoteWebDriver;
    }

    public void setBaseUrl(String baseUrl) {
        this.baseUrl = baseUrl;
    }

    public void loadBaseUrl() {
        remoteWebDriver.get(baseUrl);
    }

    public void navigateToPath(String path) {
        remoteWebDriver.get(baseUrl + path);
    }

    public void setUser(User user) {
        this.user = user;
        userSessionMap.put(user.getUsername(), true);
    }

    public void removeUser(User user) {
        if(user != null) {
            userSessionMap.remove(user.getUsername());
        }
    }

    public User getCurrentUser() {
        return user;
    }

    public String getPageSource() {
        return this.remoteWebDriver.getPageSource();
    }

    public void takeScreenShot(String filename, String destinationPath) {
        try {
            File scrFile = remoteWebDriver.getScreenshotAs(OutputType.FILE);
            File screenshotFile = new File(destinationPath + "/" + filename);

            if (!screenshotFile.exists()) {
                FileUtils.copyFile(scrFile, screenshotFile);
                Logger.LogInfo("PageUrl: " + remoteWebDriver.getCurrentUrl());
                Logger.LogInfo("Screenshot saved to: " + screenshotFile.getAbsolutePath());
            }
        } catch (Exception e) {
           Logger.LogError("Error trying to take screen shot: " + e.getMessage(), e);
        }
    }

    public boolean userHasSession(User user) {
        return userSessionMap.get(user.getUsername()) != null;
    }
}
