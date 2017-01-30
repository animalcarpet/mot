package uk.gov.dvsa.helper;

public class ContactDetailsHelper {

    public static String getAddressLine1() {
        return RandomDataGenerator.generateRandomString(8, 9);
    }

    public static String getAddressLine2() {
        return RandomDataGenerator.generateRandomString(8, 9);
    }

    public static String getAddressLine3() {
        return RandomDataGenerator.generateRandomString(8, 9);
    }

    public static String getCity() {
        return RandomDataGenerator.generateRandomString(8, 9);
    }

    public static String getPostCode() {
        return RandomDataGenerator.generateRandomPostcode(false);
    }

    public static String getPhoneNumber() {
        return RandomDataGenerator.generateRandomNumber(10, 9);
    }

    public static String getEmail() {
        return  RandomDataGenerator.generateEmail(35, System.nanoTime());
    }

    public static String generateUniqueName() {return RandomDataGenerator.generateRandomString(8, 9);}

    public static int getDateOfBirthDay() { return RandomDataGenerator.generateRandomInteger(1,28); }

    public static int getDateOfBirthMonth() { return RandomDataGenerator.generateRandomInteger(1, 12); }

    public static int getDateOfBirthYear() {return RandomDataGenerator.generateRandomInteger(1950, 2010); }

}
