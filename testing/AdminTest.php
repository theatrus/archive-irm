<?php

class AdminTest extends IRM_WebTestCase
{
	function setUp()
	{
		$DB = Config::Database();
		$DB->_EmptyDatabase();
	}
	
	function testInitialiseDatabase()
	{
		$this->get(TESTING_BASEURI.'/admin.php');

		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/<select name="active_db">/i');
		$this->assertTrue($this->setField('active_db', 'testing'));
		$this->assertTrue($this->setField('sample_data', '1'));
		$this->assertTrue($this->setField('adminpassword', 'admin'));
		$this->clickSubmit('Install');

		$this->assertWantedPattern('/Configuration File Check/');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$DB = Config::Database();
		
		$this->assertTrue('1.5.8' == $DB->getOne("SELECT value FROM config WHERE variable='dbver'"));
		
		$this->assertEqual($DB->getOne("SELECT MAX(ID) FROM computers"),
					$DB->getOne("SELECT MAX(sequence) FROM computers__ID"));

		$this->clean_install = tempnam('/xyzzy', 'tID');
		$this->DumpDB($this->clean_install);
	}

	function testUpgradeFromOldVersion()
	{
		system("mysql -u test test < testing/irm_1.3.0.sql");
		
		$this->get(TESTING_BASEURI.'/admin.php');

		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/<select name="active_db">/i');
		$this->assertTrue($this->setField('active_db', 'testing'));
		$this->clickSubmit('Upgrade');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertNoUnwantedPattern('/There were query errors/');
		$this->assertWantedPattern('/Running upgrade for 1.3.0/');
		$this->assertWantedPattern('/Running upgrade for 1.3.1/');
		$this->assertWantedPattern('/Running upgrade for 1.3.2/');
		$this->assertWantedPattern('/Running upgrade for 1.3.3/');
		$this->assertWantedPattern('/Running upgrade for 1.3.4/');
		$this->assertWantedPattern('/Running upgrade for 1.4.1/');
		$this->assertWantedPattern('/Running upgrade for 1.4.2/');
		$this->assertWantedPattern('/Running upgrade for 1.4.3/');
		$this->assertWantedPattern('/Running upgrade for 1.5.0/');
		$this->assertWantedPattern('/Running upgrade for 1.5.1/');
		$this->assertWantedPattern('/Running upgrade for 1.5.2/');
		$this->assertWantedPattern('/Running upgrade for 1.5.5/');
		$this->assertWantedPattern('/Running upgrade for 1.5.8/');

		$DB = Config::Database();
		
		$this->assertEqual('1.5.8', $DB->getOne("SELECT value FROM config WHERE variable='dbver'"));

		$this->assertEqual('2', $DB->getOne("SELECT sequence FROM computers__ID"));

		// The real tricky shit -- working out whether the upgrade
		// gave us the same DB as the "official" schema
		$upgrade = tempnam('/xyzzy', 'tUFOV');
		$this->DumpDB($upgrade);

		$differences = `diff -u $upgrade $this->clean_install`;
		
		$this->assertFalse($differences, "Schemas differ: (- is upgrade, + is clean install)\n$differences");
		$this->resetDatabase();
	}

	function DumpDB($f)
	{
		system("mysqldump -u test test | grep -v '^INSERT INTO' > $f");
	}

	function resetDatabase()
	{
		$DB = Config::Database();
		$DB->_EmptyDatabase();
		$DB->InitDatabase();
	}


}
