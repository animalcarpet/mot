package uk.gov.dvsa.domain.api.response;

public class Make {
    private Long id;
    private String name;

    public Long getId() {
        return id;
    }

    public Make setId(Long id) {
        this.id = id;
        return this;
    }

    public String getName() {
        return name;
    }

    public Make setName(String name) {
        this.name = name;
        return this;
    }
}
