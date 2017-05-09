<?php

namespace DashboardTest\Model;

use Dashboard\Model\PersonalDetails;

/**
 * Class PersonalDetailsTest.
 */
class PersonalDetailsTest extends \PHPUnit_Framework_TestCase
{
    const ID = 1;
    const FIRST_NAME = 'John';
    const MIDDLE_NAME = 'Steven';
    const SURNAME = 'Smith';
    const DOB = '1980-10-10';
    const TITLE = 'Mr';
    const GENDER = 'Male';
    const ADDR_1 = 'London';
    const ADDR_2 = 'Abc';
    const ADDR_3 = '213';
    const TOWN = 'Dublin';
    const POSTCODE = 'LON 123';
    const EMAIL = 'personaldetailstest@dvsa.test';
    const PHONE = '123456765432';
    const DRIVING_LICENCE_BUMBER = '2343213';
    const REGION = 'Other';
    const ROLE_TESTER = 'TESTER';
    const ROLE_USER = 'USER';
    const ROLE_AEDM = 'aedm';
    const POSITIONS = 'test';
    const USERNAME = 'tester1';
    const SITE_ID = 1;
    const SITE_NAME = 'Garage';
    const SITE_NUMBER = 'V1234';
    const SITE_ADDRESS = 'Elm Street';
    const ORGANISATION_ID = 13;
    const ORGANISATION_NAME = 'Venture Industries AE';
    const ORGANISATION_NUMBER = 'AEVNTR';
    const ORGANISATION_ADDRESS = '1 Providence, Nashville, 72-123';

    public function test_gettersSetters()
    {
        $personalDetails = new PersonalDetails(self::getData());

        $this->assertEquals(self::ID, $personalDetails->getId());
        $this->assertEquals(self::FIRST_NAME, $personalDetails->getFirstName());
        $this->assertEquals(self::USERNAME, $personalDetails->getUsername());
        $this->assertEquals(self::MIDDLE_NAME, $personalDetails->getMiddleName());
        $this->assertEquals(self::SURNAME, $personalDetails->getSurname());
        $this->assertEquals(self::TITLE.' '.self::FIRST_NAME.' '.self::MIDDLE_NAME.' '.self::SURNAME, $personalDetails->getFullName());
        $this->assertEquals(self::DOB, $personalDetails->getDateOfBirth());
        $this->assertEquals(self::TITLE, $personalDetails->getTitle());
        $this->assertEquals(self::GENDER, $personalDetails->getGender());
        $this->assertEquals(self::ADDR_1, $personalDetails->getAddressLine1());
        $this->assertEquals(self::ADDR_2, $personalDetails->getAddressLine2());
        $this->assertEquals(self::ADDR_3, $personalDetails->getAddressLine3());
        $this->assertEquals(self::TOWN, $personalDetails->getTown());
        $this->assertEquals(self::POSTCODE, $personalDetails->getPostcode());
        $this->assertEquals(self::EMAIL, $personalDetails->getEmail());
        $this->assertEquals(self::PHONE, $personalDetails->getPhoneNumber());
        $this->assertEquals(self::DRIVING_LICENCE_BUMBER, $personalDetails->getDrivingLicenceNumber());
        $this->assertEquals(self::REGION, $personalDetails->getDrivingLicenceRegion());
        $this->assertEquals([self::POSITIONS], $personalDetails->getPositions());
        $this->assertCount(2, $personalDetails->getDisplayableRoles());
        $this->assertEquals(self::ROLE_AEDM, $personalDetails->getDisplayableRoles()[0]);
        $this->assertEquals(self::ROLE_TESTER, $personalDetails->getDisplayableRoles()[1]);

        $systemRoles = $personalDetails->getDisplayableSystemRoles();
        $this->assertCount(0, $systemRoles);

        $siteAndOrganisationRoles = $personalDetails->getSiteAndOrganisationRoles();
        $this->assertCount(2, $siteAndOrganisationRoles);

        $found = false;
        $organisation = null;

        foreach ($siteAndOrganisationRoles['organisations'] as $id => $siteAndOrganisation) {
            if ($id === self::ORGANISATION_ID) {
                $found = true;
                $organisation = $siteAndOrganisation;
                break;
            }
        }
        $this->assertTrue($found);

        $found = false;
        $site = null;
        foreach ($siteAndOrganisationRoles['sites'] as $id => $siteAndOrganisation) {
            if ($id === self::SITE_ID) {
                $found = true;
                $site = $siteAndOrganisation;
                break;
            }
        }
        $this->assertTrue($found);

        $this->assertEquals(self::ORGANISATION_NAME, $organisation['name']);
        $this->assertEquals(self::ORGANISATION_NUMBER, $organisation['number']);
        $this->assertEquals(self::ORGANISATION_ADDRESS, $organisation['address']);
        $this->assertCount(1, $organisation['roles']);
        $this->assertEquals(self::ROLE_AEDM, $organisation['roles'][0]);
        $this->assertEquals(self::SITE_NAME, $site['name']);
        $this->assertEquals(self::SITE_NUMBER, $site['number']);
        $this->assertEquals(self::SITE_ADDRESS, $site['address']);
        $this->assertCount(1, $site['roles']);
        $this->assertEquals(self::ROLE_TESTER, $site['roles'][0]);
    }

    public static function getData()
    {
        return [
            'id' => self::ID,
            'firstName' => self::FIRST_NAME,
            'middleName' => self::MIDDLE_NAME,
            'surname' => self::SURNAME,
            'dateOfBirth' => self::DOB,
            'username' => self::USERNAME,
            'title' => self::TITLE,
            'gender' => self::GENDER,
            'addressLine1' => self::ADDR_1,
            'addressLine2' => self::ADDR_2,
            'addressLine3' => self::ADDR_3,
            'town' => self::TOWN,
            'postcode' => self::POSTCODE,
            'email' => self::EMAIL,
            'phone' => self::PHONE,
            'drivingLicenceNumber' => self::DRIVING_LICENCE_BUMBER,
            'drivingLicenceRegion' => self::REGION,
            'roles' => [
                'system' => [
                    'roles' => [self::ROLE_USER],
                ],
                'organisations' => [
                    self::ORGANISATION_ID => [
                        'name' => self::ORGANISATION_NAME,
                        'number' => self::ORGANISATION_NUMBER,
                        'address' => self::ORGANISATION_ADDRESS,
                        'roles' => [self::ROLE_AEDM],
                    ],
                ],
                'sites' => [
                    self::SITE_ID => [
                        'name' => self::SITE_NAME,
                        'number' => self::SITE_NUMBER,
                        'address' => self::SITE_ADDRESS,
                        'roles' => [self::ROLE_TESTER],
                    ],
                ],
            ],
            'positions' => [
                'test',
            ],
        ];
    }
}
