package uk.gov.dvsa.helper;

import org.apache.commons.lang3.RandomStringUtils;

import java.util.Arrays;
import java.util.List;
import java.util.Random;

public class RandomDataGenerator {

    private static final String alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private static final String numbers = "1234567890";
    private static final String postcodeIncode = "ABDEFGHJLNPQRSTUWXYZ";

    private static String generateRandomString(int length, String characterSet, long seed) {
        Random random = new Random(seed);

        return RandomStringUtils.random(length, 0, characterSet.length() - 1, true, true,
                characterSet.toCharArray(), random);
    }

    public static String generateRandomString(int length, long seed) {
        return generateRandomString(length, alphabet, seed);
    }

    public static String generateRandomPostcode(boolean withWhitespace) {
        StringBuilder postcodeBuilder = new StringBuilder();
        postcodeBuilder.append(getRandomPostcodeAreaCode())
                .append(RandomStringUtils.randomNumeric(1))
                .append((withWhitespace) ? ' ' : "")
                .append(RandomStringUtils.randomNumeric(1))
                .append(generateRandomString(2, postcodeIncode, 0));

        return postcodeBuilder.toString();
    }

    private static String getRandomPostcodeAreaCode() {
        String[] areaCodes = {"AB", "AL", "B", "BA", "BB", "BD", "BH", "BL", "BN",
                "BR", "BS", "BT", "CA", "CB", "CF", "CH", "CM", "CO", "CR", "CT", "CV", "CW", "DA",
                "DD", "DE", "DG", "DH", "DL", "DN", "DT", "DY", "E", "EC", "EH", "EN", "EX", "FK",
                "FY", "G", "GL", "GY", "GU", "HA", "HD", "HG", "HP", "HR", "HS", "HU", "HX", "IG",
                "IM", "IP", "IV", "JE", "KA", "KT", "KW", "KY", "L", "LA", "LD", "LE", "LL", "LN",
                "LS", "LU", "M", "ME", "MK", "ML", "N", "NE", "NG", "NN", "NP", "NR", "NW", "OL",
                "OX", "PA", "PE", "PH", "PL", "PO", "PR", "RG", "RH", "RM", "S", "SA", "SE", "SG",
                "SK", "SL", "SM", "SN", "SO", "SP", "SR", "SS", "ST", "SW", "SY", "TA", "TD", "TF",
                "TN", "TQ", "TR", "TS", "TW", "UB", "W", "WA", "WC", "WD", "WF", "WN", "WR", "WS",
                "WV", "YO", "ZE"};
        return areaCodes[new Random().nextInt(areaCodes.length)];
    }

    public static String generateRandomString() {
        return RandomStringUtils.randomAlphanumeric(6);
    }

    public static String generateRandomNumber(int length, long seed) {
        return generateRandomString(length, numbers, seed);
    }

    public static String generateRandomAlphaNumeric(int length, long seed) {
        return generateRandomString(length, alphabet + numbers, seed);
    }

    public static String generateStringWithAllowedSpecialChars(int length,
            String allowedSpecialChars, long seed) {
        return generateRandomString(length, alphabet + numbers + allowedSpecialChars, seed);
    }

    public static String generateEmail(int length, long seed) {
        String emailDomain = "@example.com";
        String temp = generateStringWithAllowedSpecialChars(length, "-_", seed);

        return temp.substring(0, temp.length() - emailDomain.length()) + emailDomain;
    }

    public static String generateEmail(int length) {
        String emailDomain = "@example.com";

        return generateRandomString() + emailDomain;
    }

    private static String generatePasswordPrefix(){
        return  RandomStringUtils.randomAlphabetic(1).toUpperCase() +
                RandomStringUtils.randomAlphabetic(1).toLowerCase() +
                RandomStringUtils.randomNumeric(1);
    }

    public static String generatePassword(int length) {
        String passwordPrefix = generatePasswordPrefix();
        String newPassword = RandomStringUtils.randomAlphanumeric(length-passwordPrefix.length());
        return newPassword.concat(passwordPrefix);
    }

    public static int generateRandomInteger(int min, int max) {
        return min + (int)(Math.random() * ((max - min) + 1));
    }
}
