package uk.gov.dvsa.domain.model;

import uk.gov.dvsa.helper.RandomDataGenerator;

public class PersonDetails {
    private String firstName;
    private String lastName;
    private int dateOfBirthDay;
    private int dateOfBirthMonth;
    private int dateOfBirthYear;
    private Address address;

    public PersonDetails() {
        this.firstName = RandomDataGenerator.generateRandomString();
        this.lastName = RandomDataGenerator.generateRandomString();
        this.dateOfBirthDay = RandomDataGenerator.generateRandomInteger(1, 28);
        this.dateOfBirthMonth = RandomDataGenerator.generateRandomInteger(1, 12);
        this.dateOfBirthYear = RandomDataGenerator.generateRandomInteger(1950, 2000);
        this.address = new Address();
    }

    public String getFirstName() {
        return firstName;
    }

    public String getLastName() {
        return lastName;
    }

    public int getDateOfBirthDay() {
        return dateOfBirthDay;
    }

    public int getDateOfBirthMonth() {
        return dateOfBirthMonth;
    }

    public int getDateOfBirthYear() {
        return dateOfBirthYear;
    }

    public Address getAddress() {
        return address;
    }
}
