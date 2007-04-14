<?php

class ComputerGroupsFuncTest extends IRM_WebTestCase
{
	function testGroupDisplay()
	{
		$rows[] = "DELETE FROM computers WHERE ID>1";
		$rows[] = "INSERT INTO computers (ID, name) VALUES (2, 'Compu2')";
		$rows[] = "INSERT INTO computers (ID, name) VALUES (3, 'Compu3')";
		$rows[] = "INSERT INTO computers (ID, name) VALUES (4, 'Compu4')";

		$rows[] = "INSERT INTO comp_group (comp_id, group_id) VALUES (2, 2)";
		$rows[] = "INSERT INTO comp_group (comp_id, group_id) VALUES (3, 1)";
		$rows[] = "INSERT INTO comp_group VALUES (1,1)";

		BulkQueries($rows);

		$this->get(TESTING_BASEURI.'/users/setup-groups-members.php?id=1');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Computers - Group Members');

		$this->assertWantedPattern('/Ants \(1\)/');
		$this->assertWantedPattern('/Compu3 \(3\)/');
		$this->assertNoUnwantedPattern('/[^\']Compu2[^\']/');
		$this->assertNoUnwantedPattern('/[^\']Compu4[^\']/');
	}

	function testGroupAddViaSelectBox()
	{
		$rows[] = "INSERT INTO comp_group (comp_id, group_id) VALUES (1, 1)";

		$rows[] = "DELETE FROM computers WHERE ID>1";
		$rows[] = "INSERT INTO computers (ID, name) VALUES (2, 'Compu2')";
		$rows[] = "INSERT INTO computers (ID, name) VALUES (3, 'Compu3')";
		$rows[] = "INSERT INTO computers (ID, name) VALUES (4, 'Compu4')";

		$rows[] = "DELETE FROM comp_group WHERE comp_id>1";
		$rows[] = "INSERT INTO comp_group (comp_id, group_id) VALUES (2, 2)";
		$rows[] = "INSERT INTO comp_group (comp_id, group_id) VALUES (3, 1)";
		BulkQueries($rows);

		$this->get(TESTING_BASEURI.'/users/setup-groups-members.php?id=1');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Computers - Group Members');

		$this->assertWantedPattern('/Ants \(1\)/');
		$this->assertWantedPattern('/Compu3 \(3\)/');
		$this->assertNoUnwantedPattern('/[^\']Compu2[^\']/');
		$this->assertNoUnwantedPattern('/[^\']Compu4[^\']/');

		// We need a comp_id text field at the moment
		$this->assertField('comp_id');
		
		// No comp_id select box, though
		$this->assertNoUnwantedPattern('/select.*comp_id/i');
		
		// Now, switch to the select box view...
		$this->assertTrue($this->clickLink('OR Choose from a dropdown list of computers'));
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		// and we should have a dropdown list
		$this->assertWantedPattern('/select.*comp_id/i');
		// And no text box
		$this->assertNoUnwantedPattern('/input.*comp_id/i');
		
		// And *only* the currently unselected computers
		$this->assertWantedPattern('/<option value="2">Compu2/i');
		$this->assertWantedPattern('/<option value="4">Compu4/i');
		$this->assertNoUnwantedPattern('/<option value="1">Ants/i');
		$this->assertNoUnwantedPattern('/<option value="3">Compu3/i');

		// A link back to how things were in the old days
		$this->assertWantedPattern('/OR Enter the computer ID/');
		
		// But can we add a new computer with that select box?
		$this->assertTrue($this->setField('comp_id', 'Compu4'));
		$this->assertTrue($this->clickSubmit('Add'));
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		// Is it in the 'winners' list?
		$DB = Config::Database();
		$this->assertEqual(1, $DB->getOne("SELECT COUNT(*) FROM comp_group WHERE comp_id=4 AND group_id=1"));
	}

	function testDuplicateAddByHand()
	{
		$rows[] = "DELETE FROM comp_group";
		$rows[] = "INSERT INTO comp_group (comp_id, group_id)
			VALUES (1, 1)";
		BulkQueries($rows);
		
		$this->get(TESTING_BASEURI.'/users/setup-groups-members.php?id=1');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Computers - Group Members');

		$this->assertTrue($this->setField('comp_id', '1'));
		$this->assertTrue($this->clickSubmit('Add'));
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Computers - Group Members');

		$this->assertWantedPattern('/Ants \(1\)/');
		
		$DB = Config::Database();
		// No extra entry should have been recorded here
		$this->assertEqual(1, $DB->getOne("SELECT COUNT(*) FROM comp_group"));
	}
}
