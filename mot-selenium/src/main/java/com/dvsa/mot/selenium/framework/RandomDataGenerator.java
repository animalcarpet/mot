package com.dvsa.mot.selenium.framework;

import org.apache.commons.lang3.RandomStringUtils;

import java.util.Random;

public class RandomDataGenerator {

    private static final String alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private static final String numbers = "1234567890";

    private static String generateRandomString(int length, String characterSet, long seed) {
        Random random = new Random(seed);

        return RandomStringUtils.random(length, 0, characterSet.length() - 1, true, true,
                characterSet.toCharArray(), random);
    }

    public static String generateRandomString(int length, long seed) {
        return generateRandomString(length, alphabet, seed);
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
}
