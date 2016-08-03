package uk.gov.dvsa.domain.service;

import uk.gov.dvsa.framework.config.Configurator;

public class StatisticsService extends Service {

    private static final String CLEAR_CACHE_PATH = "/testsupport/clear-statistics-amazon-cache";

    public StatisticsService() {
        super(Configurator.testSupportUrl());
    }

    public void clearCache() {
        motClient.postWithoutToken("{}", CLEAR_CACHE_PATH);
    }
}
