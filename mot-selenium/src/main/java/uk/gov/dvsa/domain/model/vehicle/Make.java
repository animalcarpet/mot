package uk.gov.dvsa.domain.model.vehicle;

public class Make {
    private Long id;
    private String name;

    public Make() {
    }

    public Make(String name) {
        this.name = name;
    }

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

    @Override
    public boolean equals(Object o) {
        if (this == o) {
            return true;
        }
        if (o == null || getClass() != o.getClass()) {
            return false;
        }

        Make make = (Make) o;

        if (id == null && make.id != null) {
            return false;
        }

        if (id != null && make.id == null) {
            return false;
        }

        if (id != null && make.id != null && !id.equals(make.id)) {
            return false;
        }
        return name != null ? name.equals(make.name) : make.name == null;

    }

    @Override
    public int hashCode() {
        int result = id.intValue();
        result = 31 * result + (name != null ? name.hashCode() : 0);
        return result;
    }

    @Override
    public String toString() {
        return "Make{" +
                "id=" + id +
                ", name='" + name + '\'' +
                '}';
    }
}
