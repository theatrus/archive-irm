<?php

class ScrapTest extends UnitTestCase
{
	function testLogEvent()
	{
		$DB = Config::Database();
		
		$DB->query("DELETE FROM event_log");
		
		Config::Set('minloglevel', 5);
		
		logevent(3, 'computers', 4, 'database', 'Joe added record');
		$row = $DB->getRow("SELECT * FROM event_log");
		$exp = array('ID' => $row['ID'], 
				'item' => 3,
				'itemtype' => 'computers',
				'date' => date('Y-m-d H:i:s'),
				'service' => 'database',
				'level' => 4,
				'message' => 'Joe added record');
		$this->assertEqual($exp, $row);
		
		logevent(7, 'networking', 6, 'database', 'Phil removed something');
		
		$this->assertEqual(1, $DB->getOne("SELECT COUNT(ID) FROM event_log"));
	}

	function testCount_licenses()
	{
		$rows[] = "INSERT INTO software_licenses (sID, entitlement)
				VALUES (1, 1)";
		$rows[] = "INSERT INTO software_licenses (sID, entitlement)
				VALUES (2, 2)";
		$rows[] = "INSERT INTO software_licenses (sID, entitlement)
				VALUES (1, 3)";
		$rows[] = "INSERT INTO software_licenses (sID, entitlement)
				VALUES (1, 4)";
		$DB = Config::Database();
		$DB->BulkQueries($rows);

		$this->assertEqual(8, Count_licenses(1));
	}

	function testSelect_options()
	{
		$this->assertEqual("<option value=\"1\">One</option>\n<option value=\"2\">Two</option>\n",
				select_options(array(1 => 'One', 2 => 'Two')));
		$this->assertEqual("<option value=\"3\">Three</option>\n<option value=\"4\" selected>Four</option>\n",
				select_options(array(3 => 'Three', 4 => 'Four'), 4));
	}

	function testappendURLArguments()
	{
		$this->assertEqual('http://www.foo.com/index.php?a=b', appendURLArguments('http://www.foo.com/index.php', array('a' => 'b')));
		$this->assertEqual('http://www.foo.com/index.php?a=b&c=d&e=f', appendURLArguments('http://www.foo.com/index.php?a=b', array('c' => 'd', 'e' => 'f')));
	}

	function testPageHistory()
	{
		// Yes, this is weird...
		unset($_SESSION['_sess_pagehistory']);

		$_SERVER['REQUEST_URI'] = '/nowhere1';
		require 'include/irm.inc';
		$this->assertTrue(is_a($_SESSION['_sess_pagehistory'], 'PageHistory'));
		$this->assertEqual(array('/nowhere1'), $_SESSION['_sess_pagehistory']->history);

		$_SERVER['REQUEST_URI'] = '/nowhere2';
		require 'include/irm.inc';
		$this->assertEqual(array('/nowhere2', '/nowhere1'), $_SESSION['_sess_pagehistory']->history);
		
		$_SERVER['REQUEST_URI'] = '/nowhere3';
		require 'include/irm.inc';
		$this->assertEqual(array('/nowhere3', '/nowhere2', '/nowhere1'), $_SESSION['_sess_pagehistory']->history);
		$this->assertEqual('/nowhere2', $_SESSION['_sess_pagehistory']->Previous());
	}
}
