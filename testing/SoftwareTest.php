<?php

class SoftwarePackageTest extends IRM_WebTestCase
{
	function testAddSoftwarePackage()
	{
		$this->clickLink('Inventory');
		$this->clickLink('Software');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->clickLink('Add software');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		$this->assertWantedPattern('/Location:/');
		$this->assertField('package');
		$this->assertTrue($this->setField('package', '\\\\server\\foo'));
		$this->assertTrue($this->setField('name', 'Foo'));
		$this->clickSubmit('Add');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$DB = Config::Database();
		
		$pkg = $DB->getOne("SELECT install_package FROM software WHERE name='Foo'");
		$this->assertEqual('\\\\server\\foo', $pkg);
		$name = $DB->getOne("SELECT name FROM software WHERE name='Foo'");
		$this->assertEqual($name, 'Foo');
	}

	function testModifySoftwarePackage()
	{
		$rows[] = "DELETE FROM software WHERE name='Foo2'";
		$rows[] = "INSERT INTO software (ID, name, install_package)
				VALUES (12, 'Foo2', '\\\\\\\\server\\\\foo2')";
		BulkQueries($rows);
		
		$this->clickLink('Inventory');
		$this->clickLink('Software');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertField('ID');
		$this->assertTrue($this->setField('ID', 'Foo2'));
		$this->clickSubmit('Show');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		$this->assertWantedPattern('/Location:/');
		$this->assertField('name', 'Foo2');
		$this->assertField('package', '\\\\server\\foo2');
		$this->assertTrue($this->setField('package', '\\\\newsrv\\foo2'));
		$this->assertTrue($this->setField('comments', 'This is all so nice'));
		$this->clickSubmit('Update');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		$DB = Config::Database();
		
		$pkg = $DB->getOne("SELECT install_package FROM software WHERE name='Foo2'");
		$this->assertEqual('\\\\newsrv\\foo2', $pkg);
		$comments = $DB->getOne("SELECT comments FROM software WHERE name='Foo2'");
		$this->assertEqual($comments, 'This is all so nice');
	}

	function testAddSoftwareLicense()
	{
		$rows[] = "INSERT INTO software (ID, name, class)
				VALUES (17, 'PatrickSoft', 'Application')";
		BulkQueries($rows);
		
		$this->clickLink('Inventory');
		$this->clickLink('Software');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
	
		$this->assertField('ID');
		$this->assertTrue($this->setField('ID', 'PatrickSoft'));
		$this->clickSubmit('Show');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Application: PatrickSoft \(17\)/');
		
		$this->assertTrue($this->setField('licensekey', 'A1B2C3'));
		$this->assertTrue($this->setField('entitlement', '3'));
		$this->clickSubmit('Add');
		
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Application: PatrickSoft \(17\)/');
		$this->assertWantedPattern('/A1B2C3/');
		
		$DB = Config::Database();
		$data = $DB->getRow("SELECT * FROM software_licenses WHERE licensekey='A1B2C3'");
		$this->assertEqual('A1B2C3', $data['licensekey']);
		$this->assertEqual(17, $data['sID']);
		$this->assertEqual(3, $data['entitlement']);
	}
}
