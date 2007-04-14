<?php

class ComputersAddTest extends IRM_WebTestCase
{
	function testAddDisplay()
	{	
		$rows[] = "INSERT INTO lookup_data VALUES ('type', 'Homebrew')";
		BulkQueries($rows);		

		$this->clearTable('computers');
		$this->get(TESTING_BASEURI.'/users/computers-index.php?action=add&withtemplate=1&ID=1');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Computers - Add Form');
		// A few random fields...
		$this->assertField('name');
		$this->assertField('processor');
		$this->assertField('contact');

		$this->setField('name', 'testcomp');
		$this->setField('type', 'Homebrew');
		$this->setField('osver', '1.1');
		$this->clickSubmit('Add');

		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Computers - Add Form');
		$this->assertWantedPattern('/Computer Added Successfully/');
		
		$DB = Config::Database();

		$this->assertDBField('testcomp', 'computers', 'name');
		$this->assertDBField('Homebrew', 'computers', 'type');
		$this->assertDBField('1.1', 'computers', 'osver');
	}

	function testExplicitID()
	{
		$rows[] = "INSERT INTO lookup_data VALUES ('type', 'Homebrew')";
		BulkQueries($rows);		

		$this->clearTable('computers');
		$this->get(TESTING_BASEURI.'/users/computers-index.php?action=add&withtemplate=1&ID=1');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Computers - Add Form');
		// A few random fields...
		$this->assertField('name');
		$this->assertField('processor');
		$this->assertField('contact');

		$this->setField('name', 'testcomp');
		$this->setField('type', 'Homebrew');
		$this->setField('osver', '1.1');
		$this->assertTrue($this->setField('reqID', '65000'));
		
		$this->clickSubmit('Add');
		
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Computer Added Successfully/');
		
		$DB = Config::Database();

		$this->assertDBField('testcomp', 'computers', 'name');
		$this->assertDBField('Homebrew', 'computers', 'type');
		$this->assertDBField('1.1', 'computers', 'osver');
		$this->assertDBField(65000, 'computers', 'ID');
		// Ensure that the sequence has updated properly
		$this->assertDBField(65000, 'computers__ID', 'sequence');
	}

	function testInvalidIDRequest()
	{
		$this->get(TESTING_BASEURI.'/users/computers-index.php?action=add&withtemplate=1&ID=1');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertTrue($this->setField('name', 'testcomp2'));
		$this->assertTrue($this->setField('reqID', 'invalid01'));
		$this->clickSubmit('Add');
		
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Error');
		$this->assertWantedPattern('/Requested IDs can only contain digits/');
		$this->assertNoUnwantedPattern('/Computer Added Successfully/');
		
		$DB = Config::Database();

		$this->assertfalse($DB->getOne("SELECT ID FROM computers WHERE name='testcomp2'"));
	}

	function testAddSoftwareItem()
	{
		$rows[] = "INSERT INTO computers 
		(ID, name, type, os, osver, processor, processor_speed,
		location, ramtype, ram,
		network, ip, mac, hdspace)
		VALUES (1,'Ants','iMac DV','Mac OS','9.0.4','PowerPC G3',
		'400','Library Back Room','SDRAM DIMMs (<10ns)','192',
		'Generic 100Mbps Card','DHCP','00 50 E4','13')";


		$rows[] = "INSERT INTO software (ID, name) VALUES (8, 'Install Me')";
		$rows[] = "INSERT INTO software_licenses (sID, licensekey, entitlement)
				VALUES (8, 'XyZ', 10)";
		BulkQueries($rows);
		
		$this->clickLink('Inventory');
		$this->clickLink('Computers');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTrue($this->setField('ID', 'Ants'));
		$this->clickSubmit('Show');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		$this->assertTrue($this->setField('sID', 'Install Me'));
		$this->clickSubmit('Add');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertWantedPattern('/Ants \(1\)/');
	}

	function testAllSomeNoneTracking()
	{
		$rows[] = "DELETE FROM tracking";
		$rows[] = "INSERT INTO comp_group VALUES (1,1)";
		$rows[] = "INSERT INTO tracking (ID, computer, status, contents, is_group, device)
				VALUES (1, 1, 'new', 'A current tracking item', 'no', 'Computers')";
		$rows[] = "INSERT INTO tracking (ID, computer, status, contents, is_group, device)
				VALUES (2, 2, 'new', 'Not a tracking item', 'no', 'Computers')";
		$rows[] = "INSERT INTO tracking (ID, computer, status, contents, is_group, device)
				VALUES (3, 1, 'complete', 'Not a current tracking item', 'no', 'Computers')";
		$rows[] = "INSERT INTO followups (tracking, contents)
				VALUES (1, 'A current followup')";
		BulkQueries($rows);
		
		$this->clickLink('Inventory');
		$this->clickLink("Computers");
		$this->assertTitle("IRM: Computers");
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->setField('ID', 1);
		$this->clickSubmit("Show");
		$this->assertTitle("IRM: Computers - Info");
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		// By default, only show current tracking
		$this->assertWantedPattern('/Found 2 tracking items, 1 currently open/');
		$this->assertNoUnwantedPattern('/No Tracking Found/');
		$this->assertWantedPattern('/A current tracking item/');
		$this->assertNoUnwantedPattern('/Not a tracking item/');
		$this->assertNoUnwantedPattern('/Not a current tracking item/');
		
		// Now, switch to 'all tracking' and see what we get
		$this->assertTrue($this->setField('showtracking', 'Show All Tracking'));
		$this->clickSubmit('Show Tracking');
		$this->assertTitle("IRM: Computers - Info");
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		$this->assertWantedPattern('/Found 2 tracking items, 1 currently open/');
		$this->assertWantedPattern('/A current tracking item/');
		$this->assertNoUnwantedPattern('/Not a tracking item/');
		$this->assertWantedPattern('/Not a current tracking item/');

		// And finally, no tracking
		$this->assertTrue($this->setField('showtracking', 'Hide Tracking'));
		$this->clickSubmit('Show Tracking');
		$this->assertTitle("IRM: Computers - Info");
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		$this->assertWantedPattern('/Found 2 tracking items, 1 currently open/');
		$this->assertNoUnwantedPattern('/A current tracking item/');
		$this->assertNoUnwantedPattern('/Not a tracking item/');
		$this->assertNoUnwantedPattern('/Not a current tracking item/');
	}
}
class ComputerSearchTest extends IRM_WebTestCase
{
	function testSearchFormLayout()
	{
		$this->get(TESTING_BASEURI.'/users/computers-index.php');

		$this->assertTitle('IRM: Computers');
		$this->assertNoUnwantedPattern('/Select system by name:/');
		$this->assertWantedPattern('/Select computer/');
		$this->assertWantedPattern('/Select computer by name/');
		$this->assertWantedPattern('/View by Location/');
		$this->assertWantedPattern('/computer Management/');
		$this->assertWantedPattern('%<option value="1">Ants</option>%');
	}
	
	function testServerFlagSearch()
	{
		$rows[] = "INSERT INTO computers (ID, name, flags_server) VALUES (2, 'srv1', 1)";
		$rows[] = "INSERT INTO computers (ID, name, flags_server) VALUES (3, 'mypc', 0)";
		$rows[] = "INSERT INTO computers (ID, name, flags_server) VALUES (4, 'webserver', 1)";
		$rows[] = "INSERT INTO computers__ID VALUES (5)";
		BulkQueries($rows);

		$this->get(TESTING_BASEURI.'/users/computers-index.php');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertField('field');
		$this->assertTrue($this->setField('field', 'Server'),
				"Couldn't set field to flags_server");
		$this->setField('contains', '1');
		$this->clickSubmit('Search');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertWantedPattern('%srv1 \(\d+\)%');
		$this->assertWantedPattern('%webserver \(\d+\)%');
		$this->assertNoUnwantedPattern('/[^\']mypc[^\']/');
	}

	function testNewTrackingDisplayCurrentlyOpen()
	{
		$rows[] = "DELETE FROM computers WHERE ID=4";
		$rows[] = "INSERT INTO computers__ID VALUES (4)";
		$rows[] = "INSERT INTO computers (ID, name) VALUES (4, 'Furr')";
				
		$rows[] = "DELETE FROM tracking";
		$rows[] = "INSERT INTO tracking (ID, status, computer, contents)
				VALUES (1, 'new', 4, 'Another problem')";
		$rows[] = "INSERT INTO tracking (ID, status, computer, contents)
				VALUES (2, 'new', 1, 'A problem on a different computer')";
		$rows[] = "INSERT INTO tracking (ID, status, computer, contents)
				VALUES (3, 'complete', 4, 'A solved problem')";
		BulkQueries($rows);

		$this->clickLink('Inventory');
		$this->clickLink('Computers');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertTrue($this->setField('ID', 'Furr'));
		$this->clickSubmit('Show');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Computers - Info');
		
		$this->assertWantedPattern('/Another problem/');
		$this->assertNoUnwantedPattern('/A problem on a different computer/');
		$this->assertNoUnwantedPattern('/A solved problem/');
	}
}
