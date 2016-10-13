package uk.gov.dvsa.domain.model.vehicle;

public class Model {
    private Long id;
    private String name;

    public Model() {
    }

    public Model(String name) {
        this.name = name;
    }

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

    @Override
    public boolean equals(Object o) {
        if (this == o) {
            return true;
        }
        if (o == null || getClass() != o.getClass()) {
            return false;
        }

        Model model = (Model) o;

        if (id == null && model.id != null) {
            return false;
        }

        if (id != null && model.id == null) {
            return false;
        }

        if (id != null && model.id != null && !id.equals(model.id)) {
            return false;
        }
        return name != null ? name.equals(model.name) : model.name == null;

    }

    @Override
    public int hashCode() {
        int result = id.intValue();
        result = 31 * result + (name != null ? name.hashCode() : 0);
        return result;
    }

    @Override
    public String toString() {
        return "Model{" +
                "id=" + id +
                ", name='" + name + '\'' +
                '}';
    }
}
