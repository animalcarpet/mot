package uk.gov.dvsa.domain.api.response;

public class Model {
    private Long id;
    private String name;

    public Long getId() {
        return id;
    }

    public Model setId(Long id) {
        this.id = id;
        return this;
    }

    public String getName() {
        return name;
    }

    public Model setName(String name) {
        this.name = name;
        return this;
    }
}
