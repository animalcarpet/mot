package uk.gov.dvsa.domain.model;

import com.fasterxml.jackson.annotation.JsonAutoDetect;
import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonAutoDetect(fieldVisibility = JsonAutoDetect.Visibility.ANY)
@JsonIgnoreProperties(ignoreUnknown = true)
public class Site {
    private int id;
    private String siteNumber;
    private String name;

    public Site() {
    }

    public Site(int id, String siteNumber, String name) {
        this.id = id;
        this.siteNumber = siteNumber;
        this.name = name;
    }

    public String getIdAsString() {
        return String.valueOf(id);
    }

    public int getId() {
        return id;
    }

    public String getSiteNumber() {
        return siteNumber;
    }

    public String getName() {
        return name;
    }

    public String getSiteNameAndNumberInHomePageFormat(){
        return "(" + getSiteNumber() + ") " + getName();
    }

    @Override
    public String toString() {
        return "Site{" +
                "id=" + id +
                ", siteNumber='" + siteNumber + '\'' +
                ", name='" + name + '\'' +
                '}';
    }
}
