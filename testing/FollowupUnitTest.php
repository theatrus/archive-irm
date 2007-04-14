<?php

class FollowupClassTest extends IRM_UnitTestCase
{
	function testnewFollowup()
	{
		$o = new Followup(0);
		
		$this->assertEqual(0, $o->Public);
	}
	function testgetByTrackingId()
	{
		$rows[] = "INSERT INTO followups (ID, tracking, public, date)
				VALUES (8, 1, 1, '2004-01-01')";
		$rows[] = "INSERT INTO followups (ID, tracking, public, date)
				VALUES (2, 1, 0, '2004-02-02')";
		$rows[] = "INSERT INTO followups (ID, tracking, public, date)
				VALUES (3, 2, 1, '2004-01-01')";
		$rows[] = "INSERT INTO followups (ID, tracking, public, date)
				VALUES (4, 1, 0, '2003-12-12')";
		$rows[] = "INSERT INTO followups (ID, tracking, public, date)
				VALUES (5, 1, 1, '2002-12-12')";

		BulkQueries($rows);
		global $IRMName;
		
		$IRMName = 'Admin';
		$this->assertEqual(array('5', '4', '8', '2'), Followup::getByTrackingID(1));

		$IRMName = 'Tech';
		$this->assertEqual(array('5', '4', '8', '2'), Followup::getByTrackingID(1));

		$IRMName = 'Guest';
		$this->assertEqual(array('5', '8'), Followup::getByTrackingID(1));
	}
}
