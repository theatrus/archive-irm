<?php

class FrontpageTest extends IRM_WebTestCase
{
	function testEventLogDisplay()
	{
		$rows[] = "INSERT INTO event_log (message) VALUES ('Weird Message For SimpleTest')";
		BulkQueries($rows);
		
		$this->get(TESTING_BASEURI.'/users/index.php');
		
		$this->assertWantedPattern('/Weird Message For SimpleTest/');
	}
}
