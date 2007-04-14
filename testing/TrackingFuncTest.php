<?php


class TrackingFuncTest extends IRM_WebTestCase
{
	function testEditableTracking()
	{
		$rows[] = "DELETE FROM tracking";
		$rows[] = "INSERT INTO tracking (ID, date, status, author,
					computer, contents, priority, uemail,
					emailupdates)
				VALUES (4, '2004-12-29', 'new', 'Joe',
					1, 'Do Work', 2, 'joe@localhost',
					'no')";
		BulkQueries($rows);
		
		$this->Get(TESTING_BASEURI.'/users/tracking-index.php?action=detail&ID=4');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertField('priority', '2');
		$this->assertField('status', 'new');
		
		$this->assertTrue($this->setField('priority', 'High'));
		$this->assertTrue($this->setField('status', 'Active'));
		$this->assertTrue($this->setField('workrequest', 'Do Useful Work'));

		$this->clickSubmit('Update Tracking');

		$this->assertDBField(4, 'tracking', 'priority');
		$this->assertDBField('active', 'tracking', 'status');
		$this->assertDBField('Do Useful Work', 'tracking', 'contents');
	}

	function testNewFollowup()
	{
		$rows[] = "DELETE FROM followups";
		$rows[] = "DELETE FROM tracking";
		$rows[] = "INSERT INTO tracking (ID, contents, date, computer, author, uemail, emailupdates, status)
				VALUES (1, 'tNF', '2004-01-01', 1, 'x', 'y', 'no', 'new')";
		BulkQueries($rows);
		
		$this->Get(TESTING_BASEURI.'/users/tracking-index.php?action=detail&ID=1');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		$this->assertTrue($this->setField('newfollowup', 'oh what a world'));
		$this->assertTrue($this->setField('public', '1'));
		$this->clickSubmit('Update Tracking');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		$this->assertDBField(1, 'followups', 'public');
		$this->assertDBField('oh what a world', 'followups', 'contents');
	}

	function testNewPrivateFollowup()
	{
		$rows[] = "DELETE FROM followups";
		$rows[] = "DELETE FROM tracking";
		$rows[] = "INSERT INTO tracking (ID, contents, date, computer, author, uemail, emailupdates, status)
				VALUES (1, 'tNF', '2004-01-01', 1, 'x', 'y', 'no', 'new')";
		BulkQueries($rows);
		
		$this->Get(TESTING_BASEURI.'/users/tracking-index.php?action=detail&ID=1');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertTrue($this->setField('newfollowup', 'users suck'));
		$this->assertTrue($this->setField('public', false));
		$this->clickSubmit('Update Tracking');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		$this->assertDBField(0, 'followups', 'public');
		$this->assertDBField('users suck', 'followups', 'contents');
	}

	function testNewTrackingDisplayCurrentlyOpen()
	{
		$rows[] = "DELETE FROM tracking";
		$rows[] = "INSERT INTO tracking (ID, status, computer, contents)
				VALUES (1, 'new', 1, 'Another problem')";
		$rows[] = "INSERT INTO tracking (ID, status, computer, contents)
				VALUES (2, 'new', 2, 'A problem on a different computer')";
		$rows[] = "INSERT INTO tracking (ID, status, computer, contents)
				VALUES (3, 'complete', 1, 'A solved problem')";
		
		BulkQueries($rows);

		$this->clickLink('Request Help');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertTrue($this->setField('ID', '1'));
		$this->clickSubmit('Continue with IRM ID');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Tracking - Add Job - Is this the computer?');
	
	//	$this->assertWantedPattern('/Another problem/');
		$this->assertNoUnwantedPattern('/A problem on a different computer/');
		$this->assertNoUnwantedPattern('/A solved problem/');
	}

	function testNewTrackingDisplayGroupCurrentlyOpen()
	{
		$rows[] = "INSERT INTO comp_group VALUES (1,1)";

		$rows[] = "DELETE FROM groups";
		$rows[] = "INSERT INTO groups (ID, name) VALUES (1, 'mygroup')";
		
		$rows[] = "DELETE FROM tracking";
		$rows[] = "INSERT INTO tracking (ID, status, computer, contents, is_group)
				VALUES (1, 'new', 1, 'Another problem', 'Yes')";
		$rows[] = "INSERT INTO tracking (ID, status, computer, contents, is_group)
				VALUES (2, 'new', 2, 'A problem on a different computer', 'Yes')";
		$rows[] = "INSERT INTO tracking (ID, status, computer, contents, is_group)
				VALUES (3, 'complete', 1, 'A solved problem', 'Yes')";
		$rows[] = "INSERT INTO tracking (ID, status, computer, contents, is_group)
				VALUES (4, 'new', 1, 'A single-computer problem', 'No')";
		BulkQueries($rows);

		$this->clickLink('Request Help');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertTrue($this->setField('groupname', 'mygroup'));
		$this->clickSubmit('Continue with group selection');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Tracking - Add Job');
		
		$this->assertWantedPattern('/Another problem/');
		$this->assertNoUnwantedPattern('/A problem on a different computer/');
		$this->assertNoUnwantedPattern('/A solved problem/');
		$this->assertNoUnwantedPattern('/A single-computer problem/');
	}
}
