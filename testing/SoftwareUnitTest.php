<?php

require_once 'TestHelpers.php';

class SoftwareScrapsUnitTest extends IRM_UnitTestCase
{
	function testshowLicenses()
	{
		$rows[] = "INSERT INTO software_licenses (ID, sID, licensekey,
						entitlement, oem_sticker)
				VALUES (12, 12, 'XYZ', 2, 'yes')";
		$rows[] = "INSERT INTO software_licenses (ID, sID, licensekey,
						entitlement, oem_sticker)
				VALUES (21, 21, 'notinterested', 2, 'yes')";
		$rows[] = "INSERT INTO software_licenses (ID, sID, licensekey,
						entitlement, oem_sticker)
				VALUES (13, 12, 'ABC', 1, 'no')";
		$rows[] = "INSERT INTO software_licenses (ID, sID, licensekey,
						entitlement, oem_sticker)
				VALUES (14, 12, 'PQM', 101, 'yes')";
		BulkQueries($rows);

		ob_start();
		
		showLicenses(12);

		$output = ob_get_clean();
		
//		$this->assertByDiff('testing/data/SoftwareScrapsUnitTest.testshowLicenses1.html', $output);
	}

	function testcompsoftShow()
	{
		$rows[] = "DELETE FROM software";
		$rows[] = "DELETE FROM software_licenses";
		
		$rows[] = "INSERT INTO software (ID, name)
				VALUES (1, 'Whee!')";
		$rows[] = "INSERT INTO software (ID, name)
				VALUES (2, 'Hello, I\\'m number two')";
		$rows[] = "INSERT INTO software (ID, name)
				VALUES (3, 'Not For Me 1.2')";

		$rows[] = "INSERT INTO software_licenses (ID, licensekey)
				VALUES (1, 'Won')";
		$rows[] = "INSERT INTO software_licenses (ID, licensekey)
				VALUES (2, 'Too')";
		$rows[] = "INSERT INTO software_licenses (ID, licensekey)
				VALUES (3, 'Free')";
		$rows[] = "INSERT INTO software_licenses (ID, licensekey)
				VALUES (4, 'Fore')";
				
		$rows[] = "INSERT INTO inst_software (ID, sID, cID, lID)
				VALUES (111, 1, 1, 1)";
		$rows[] = "INSERT INTO inst_software (ID, sID, cID, lID)
				VALUES (122, 1, 2, 2)";
		$rows[] = "INSERT INTO inst_software (ID, sID, cID, lID)
				VALUES (243, 2, 4, 3)";
		$rows[] = "INSERT INTO inst_software (ID, sID, cID, lID)
				VALUES (215, 2, 1, 5)";

		BulkQueries($rows);
		
		$_SERVER['SCRIPT_NAME'] = '/_irmtest/users';
		$_SERVER['SCRIPT_FILENAME'] = dirname(dirname(__FILE__)).'/users';
		ob_start();
		compsoftShow(1);
		$output = ob_get_clean();
		
		//$this->assertByDiff('testing/data/SoftwareScrapsUnitTest.testcompsoftShow1.html', $output);
	}

	function testfind_license()
	{
		$rows[] = "INSERT INTO software (ID, name) VALUES (10, 'Ten')";
		$rows[] = "INSERT INTO software (ID, name) VALUES (20, 'Twnety')";
		$rows[] = "INSERT INTO software_licenses (ID, sID, entitlement)
					VALUES (11, 10, 11)";
		$rows[] = "INSERT INTO software_licenses (ID, sID, entitlement)
					VALUES (12, 10, 12)";
		$rows[] = "INSERT INTO software_licenses (ID, sID, entitlement)
					VALUES (13, 10, 13)";
		$rows[] = "INSERT INTO software_licenses (ID, sID, entitlement)
					VALUES (21, 20, 21)";
		$rows[] = "INSERT INTO inst_software (ID, cID, sID, lID, lCnt)
					VALUES (101, 1, 10, 12, 8)";
		BulkQueries($rows);
		
		$lID = find_license(10, 12);

		$this->assertEqual(13, $lID);
	}
}
