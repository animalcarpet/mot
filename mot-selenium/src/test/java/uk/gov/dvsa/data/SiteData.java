package uk.gov.dvsa.data;

import com.google.common.base.Optional;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.service.SiteService;
import org.joda.time.DateTime;
import uk.gov.dvsa.domain.service.StatisticsService;

import java.io.IOException;

public class SiteData extends SiteService{
    private StatisticsService statisticsService = new StatisticsService();
    private AeData aeData = new AeData();

    public SiteData() {}

    public Site createNewSite(int aeId, String name) throws IOException {
        return createSite(Optional.of(aeId), name);
    }

    public Site createSite() throws IOException {
        return createSite(Optional.of(aeData.createAeWithDefaultValues().getId()), "default");
    }

    public Site createSite(String name) throws IOException {
        return createSite(Optional.of(aeData.createAeWithDefaultValues().getId()), name);
    }

    public Site createSiteWithoutAe(String name) throws IOException {
        return createSite(Optional.<Integer>absent(), name);
    }

    public Site createSiteWithStartSiteOrgLinkDate(int aeId, String name, DateTime startDate) throws IOException {
        return createSiteWithStartSiteOrgLinkDate(Optional.of(aeId), name, startDate);
    }

    public void clearAllCachedStatistics(){
        statisticsService.clearCache();
    }
}
